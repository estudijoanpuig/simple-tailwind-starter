import pandas as pd
import mysql.connector
from datetime import datetime
import logging
import os
import sys
import glob

# Configurar logging només per consola (sense fitxer)
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[logging.StreamHandler()]
)

# Configuració de la connexió a la base de dades
db_config = {
    'host': 'localhost',
    'user': 'joan',
    'password': 'queMm88/g62123',
    'database': 'autonomo_contabilidad',
    'port': 3306
}

# Directori d'Uploads a l'arrel del projecte
UPLOADS_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'Uploads')

# Obtenir els fitxers CSV (arguments o directori Uploads/descàrregues)
csv_files = []
if len(sys.argv) > 1:
    csv_files = sys.argv[1:]
else:
    # Buscar fitxers CSV al directori Uploads
    csv_files = glob.glob(os.path.join(UPLOADS_DIR, 'compartit*.csv'))
    if not csv_files:
        logging.info(f"No s'han trobat fitxers CSV al directori '{UPLOADS_DIR}'. Prova de buscar a descàrregues...")
        # Intentar buscar a descàrregues
        DOWNLOADS_DIR = os.path.expanduser("~/Downloads")
        csv_files = glob.glob(os.path.join(DOWNLOADS_DIR, 'compartit*.csv'))
        if not csv_files:
            logging.error(f"No s'han trobat fitxers CSV a '{DOWNLOADS_DIR}' ni a '{UPLOADS_DIR}'.")
            exit(1)
    logging.info(f"Fitxers CSV seleccionats automàticament: {csv_files}")

# Comprovar si els fitxers existeixen
for csv_file in csv_files:
    if not os.path.exists(csv_file):
        logging.error(f"El fitxer CSV '{csv_file}' no existeix o no és accessible.")
        exit(1)

# Connectar a la base de dades
try:
    conn = mysql.connector.connect(**db_config)
    cursor = conn.cursor(buffered=True)
    logging.info("Connexió a la base de dades establerta correctament.")
except mysql.connector.Error as e:
    logging.error(f"Error en connectar a la base de dades: {e}")
    exit(1)

# Comprovar si les taules existeixen
try:
    for table in ['wp_contabilidad_proveedores', 'wp_contabilidad_productos', 'wp_contabilidad_compras', 'wp_contabilidad_detalles_compra']:
        cursor.execute(f"SHOW TABLES LIKE '{table}'")
        if not cursor.fetchone():
            logging.error(f"La taula {table} no existeix. Assegura't que l'esquema està creat.")
            cursor.close()
            conn.close()
            exit(1)
        cursor.fetchall()
except mysql.connector.Error as e:
    logging.error(f"Error en comprovar taules: {e}")
    cursor.close()
    conn.close()
    exit(1)

# Eliminar la restricció unique_compra per diagnosticar
try:
    cursor.execute("ALTER TABLE wp_contabilidad_compras DROP CONSTRAINT unique_compra")
    logging.info("Restricció unique_compra eliminada temporalment per diagnosticar.")
except mysql.connector.Error as e:
    if e.errno == 1091:  # Error per restricció inexistent
        logging.info("La restricció unique_compra no existeix.")
    else:
        logging.error(f"Error en eliminar la restricció unique_compra: {e}")
        cursor.close()
        conn.close()
        exit(1)

# Inserir proveïdor per defecte per a files amb NOM buit
try:
    cursor.execute("SELECT id FROM wp_contabilidad_proveedores WHERE nombre = %s", ('Desconegut',))
    result = cursor.fetchone()
    cursor.fetchall()
    if not result:
        cursor.execute(
            "INSERT INTO wp_contabilidad_proveedores (nombre, created_at, updated_at) VALUES (%s, NOW(), NOW())",
            ('Desconegut',)
        )
        default_proveidor_id = cursor.lastrowid
        logging.info(f"Proveïdor per defecte 'Desconegut' inserit amb ID {default_proveidor_id}.")
    else:
        default_proveidor_id = result[0]
except mysql.connector.Error as e:
    logging.error(f"Error en inserir proveïdor per defecte: {e}")
    conn.rollback()
    cursor.close()
    conn.close()
    exit(1)

# Processar cada fitxer CSV
total_inserted_rows = 0
for csv_file in csv_files:
    logging.info(f"Processant fitxer CSV: {csv_file}")

    # Llegir el fitxer CSV
    try:
        df = pd.read_csv(csv_file, encoding='utf-8')
        logging.info(f"Fitxer CSV '{csv_file}' llegit correctament.")
        logging.info(f"Capçaleres del CSV: {list(df.columns)}")
    except Exception as e:
        logging.error(f"Error en llegir el fitxer CSV '{csv_file}': {e}")
        continue

    # Netejar i preprocessar les dades
    def clean_currency(value):
        try:
            if pd.isna(value) or value == '':
                logging.warning(f"Valor monetari buit detectat. Assignant 0.0.")
                return 0.0
            if isinstance(value, str):
                cleaned_value = value.replace(' €', '').replace('.', '').replace(',', '.')
                return float(cleaned_value)
            return float(value)
        except Exception as e:
            logging.error(f"Error en netejar valor monetari '{value}': {e}")
            return 0.0

    def clean_date(date_str):
        try:
            if pd.isna(date_str) or date_str == '':
                logging.warning(f"Data buida detectada. Retornant None.")
                return None
            return datetime.strptime(date_str, '%d/%m/%Y').strftime('%Y-%m-%d')
        except Exception as e:
            logging.error(f"Error en netejar data '{date_str}': {e}")
            return None

    # Detectar columnes dinàmicament
    columns = df.columns.str.upper()
    if 'TOTAL' in columns:
        df['IMPORT TOTAL'] = df['TOTAL'].apply(clean_currency)
    else:
        logging.error(f"La columna 'TOTAL' no existeix al CSV '{csv_file}'. Verifica les capçaleres.")
        continue

    if 'BASE IVA' in columns:
        df['BASE IVA'] = df['BASE IVA'].apply(clean_currency)
    else:
        df['BASE IVA'] = 0.0
        logging.warning(f"La columna 'BASE IVA' no existeix al CSV '{csv_file}', s'utilitza 0.0 com a valor per defecte.")

    if 'IVA SOPORTAT' in columns:
        df['iva monto'] = df['IVA SOPORTAT'].apply(clean_currency)
    elif 'IVA MONTO' in columns:
        df['iva monto'] = df['IVA MONTO'].apply(clean_currency)
    else:
        df['iva monto'] = 0.0
        logging.warning(f"La columna 'IVA SOPORTAT' o 'IVA MONTO' no existeix al CSV '{csv_file}', s'utilitza 0.0 com a valor per defecte.")

    if 'FECHA' in columns:
        df['DATA'] = df['FECHA'].apply(clean_date)
    elif 'DATA' in columns:
        df['DATA'] = df['DATA'].apply(clean_date)
    else:
        logging.error(f"La columna 'FECHA' o 'DATA' no existeix al CSV '{csv_file}'. Verifica les capçaleres.")
        continue

    if '%IVA' in columns:
        df['%IVA'] = df['%IVA'].fillna(0).astype(float)
    else:
        df['%IVA'] = 0.0
        logging.warning(f"La columna '%IVA' no existeix al CSV '{csv_file}', s'utilitza 0.0 com a valor per defecte.")

    if 'NOM' not in columns:
        df['NOM'] = 'Desconegut'
        logging.warning(f"La columna 'NOM' no existeix al CSV '{csv_file}', s'utilitza 'Desconegut' com a valor per defecte.")

    if 'CONCEPTE' not in columns:
        df['CONCEPTE'] = 'Desconegut'
        logging.warning(f"La columna 'CONCEPTE' no existeix al CSV '{csv_file}', s'utilitza 'Desconegut' com a valor per defecte.")

    # Filtrar files amb import total 0 o dates invàlides
    df = df[(df['IMPORT TOTAL'] > 0) & (df['DATA'].notna())]
    logging.info(f"Total de files després de netejar al CSV '{csv_file}': {len(df)}")

    # Inserir proveïdors únics
    proveedores = df['NOM'].dropna().unique()
    proveedor_ids = {'Desconegut': default_proveidor_id}
    try:
        for proveedor in proveedores:
            if proveedor == 'Desconegut':
                continue
            cursor.execute("SELECT id FROM wp_contabilidad_proveedores WHERE nombre = %s", (proveidor,))
            result = cursor.fetchone()
            cursor.fetchall()
            if not result:
                cursor.execute(
                    "INSERT INTO wp_contabilidad_proveedores (nombre, created_at, updated_at) VALUES (%s, NOW(), NOW())",
                    (proveidor,)
                )
                proveedor_ids[proveidor] = cursor.lastrowid
                logging.info(f"Proveïdor '{proveidor}' inserit amb ID {proveidor_ids[proveidor]}.")
            else:
                proveedor_ids[proveidor] = result[0]
    except mysql.connector.Error as e:
        logging.error(f"Error en inserir proveïdors: {e}")
        conn.rollback()
        cursor.close()
        conn.close()
        exit(1)

    # Inserir conceptes com a productes
    conceptes = df['CONCEPTE'].dropna().unique()
    product_ids = {}
    try:
        for concepte in conceptes:
            if concepte == 'Desconegut':
                continue
            cursor.execute("SELECT id FROM wp_contabilidad_productos WHERE nombre = %s", (concepte,))
            result = cursor.fetchone()
            cursor.fetchall()
            if not result:
                cursor.execute(
                    """
                    INSERT INTO wp_contabilidad_productos 
                    (nombre, id_categoria_producto, precio, stock, protocol, created_at, updated_at) 
                    VALUES (%s, %s, %s, %s, %s, NOW(), NOW())
                    """,
                    (concepte, default_category_id, 0.0, 0, '')
                )
                product_ids[concepte] = cursor.lastrowid
                logging.info(f"Concepte '{concepte}' inserit com a producte amb ID {product_ids[concepte]}.")
            else:
                product_ids[concepte] = result[0]
    except mysql.connector.Error as e:
        logging.error(f"Error en inserir productes: {e}")
        conn.rollback()
        cursor.close()
        conn.close()
        exit(1)

    # Inserir producte per defecte per a conceptes nuls
    try:
        cursor.execute("SELECT id FROM wp_contabilidad_productos WHERE nombre = %s", ('Desconegut',))
        result = cursor.fetchone()
        cursor.fetchall()
        if not result:
            cursor.execute(
                """
                INSERT INTO wp_contabilidad_productos 
                (nombre, id_categoria_producto, precio, stock, protocol, created_at, updated_at) 
                VALUES (%s, %s, %s, %s, %s, NOW(), NOW())
                """,
                ('Desconegut', default_category_id, 0.0, 0, '')
            )
            product_ids['Desconegut'] = cursor.lastrowid
            logging.info(f"Producte per defecte 'Desconegut' inserit amb ID {product_ids['Desconegut']}.")
        else:
            product_ids['Desconegut'] = result[0]
    except mysql.connector.Error as e:
        logging.error(f"Error en inserir producte per defecte: {e}")
        conn.rollback()
        cursor.close()
        conn.close()
        exit(1)

    # Inserir despeses i detalls de despeses
    try:
        inserted_rows = 0
        for index, row in df.iterrows():
            # Preparar dades
            total = row.get('IMPORT TOTAL', 0.0)
            base_iva = row.get('BASE IVA', 0.0)
            iva_porcentaje = row.get('%IVA', 0.0)
            iva_monto = row.get('iva monto', 0.0)
            proveedor = row.get('NOM', 'Desconegut')
            concepte = row.get('CONCEPTE', 'Desconegut')
            notas = None
            if pd.notna(row.get('NUMERO DOCUMENT')):
                notas = f"Número document: {row['NUMERO DOCUMENT']}"

            # Depuració: registrar els valors abans d'inserir
            logging.info(f"Fila {index} (CSV {csv_file}): data={row['DATA']}, proveïdor={proveidor}, total={total}, base_iva={base_iva}, iva_porcentaje={iva_porcentaje}, iva_monto={iva_monto}, notas={notas}")

            # Inserir a wp_contabilidad_compras
            cursor.execute(
                """
                INSERT INTO wp_contabilidad_compras 
                (fecha, proveedor_id, subtotal, iva_monto, total, notas, created_at)
                VALUES (%s, %s, %s, %s, %s, %s, NOW())
                """,
                (
                    row['DATA'],
                    proveedor_ids.get(proveidor, default_proveidor_id),
                    base_iva,
                    iva_monto,
                    total,
                    notas
                )
            )
            compra_id = cursor.lastrowid
            logging.info(f"Despesa inserida amb ID {compra_id} per al proveïdor {proveidor}.")

            # Inserir a wp_contabilidad_detalles_compra
            cursor.execute(
                """
                INSERT INTO wp_contabilidad_detalles_compra 
                (compra_id, producto_id, cantidad, precio_unitario, iva_porcentaje, iva_monto, subtotal, created_at)
                VALUES (%s, %s, %s, %s, %s, %s, %s, NOW())
                """,
                (
                    compra_id,
                    product_ids.get(concepte, product_ids['Desconegut']),
                    1,  # Assumim quantitat 1
                    base_iva,
                    iva_porcentaje,
                    iva_monto,
                    base_iva
                )
            )
            logging.info(f"Detall de despesa inserit per al concepte {concepte}.")
            inserted_rows += 1

        logging.info(f"Total de files inserides per al CSV '{csv_file}': {inserted_rows}")
        total_inserted_rows += inserted_rows
    except mysql.connector.Error as e:
        logging.error(f"Error en inserir despeses o detalls per al CSV '{csv_file}': {e}, fila: {row.to_dict()}")
        conn.rollback()
        continue

# Confirmar canvis i tancar connexió
try:
    conn.commit()
    logging.info(f"Importació completada. Total de files inserides: {total_inserted_rows}")
except mysql.connector.Error as e:
    logging.error(f"Error en confirmar canvis: {e}")
    conn.rollback()
finally:
    cursor.close()
    conn.close()

print("Importació de despeses completada amb èxit.")