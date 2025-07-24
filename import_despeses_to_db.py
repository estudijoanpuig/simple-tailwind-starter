import pandas as pd
import mysql.connector
from datetime import datetime
import logging
import os
import sys

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

# Ruta del fitxer CSV passada com a argument
csv_file = sys.argv[1] if len(sys.argv) > 1 else None

# Comprovar si el fitxer existeix
if not os.path.exists(csv_file):
    logging.error(f"El fitxer CSV '{csv_file}' no existeix o no és accessible.")
    exit(1)

# Llegir el fitxer CSV
try:
    df = pd.read_csv(csv_file)
    logging.info(f"Fitxer CSV '{csv_file}' llegit correctament.")
    logging.info(f"Capçaleres del CSV: {list(df.columns)}")  # Depuració de les capçaleres
except Exception as e:
    logging.error(f"Error en llegir el fitxer CSV: {e}")
    exit(1)

# Netejar i preprocessar les dades
def clean_currency(value):
    try:
        if isinstance(value, str):
            # Eliminar ' €' i substituir '.' (milers) per res i ',' (decimals) per '.'
            cleaned_value = value.replace(' €', '').replace('.', '').replace(',', '.')
            return float(cleaned_value)
        return float(value) if pd.notna(value) else 0.0
    except Exception as e:
        logging.error(f"Error en netejar valor monetari '{value}': {e}")
        return 0.0

def clean_date(date_str):
    try:
        if isinstance(date_str, str):
            return datetime.strptime(date_str, '%d/%m/%Y').strftime('%Y-%m-%d')
        return None
    except Exception as e:
        logging.error(f"Error en netejar data '{date_str}': {e}")
        return None

# Detectar columnes dinàmicament
columns = df.columns.str.upper()  # Convertir a majúscules per fer coincidir
if 'TOTAL' in columns:
    df['IMPORT TOTAL'] = df['TOTAL'].apply(clean_currency)
else:
    logging.error("La columna 'TOTAL' no existeix al CSV. Verifica les capçaleres.")
    exit(1)

if 'BASE IVA' in columns:
    df['BASE IVA'] = df['BASE IVA'].apply(clean_currency)
else:
    df['BASE IVA'] = 0.0  # Valor per defecte si no existeix
    logging.warning("La columna 'BASE IVA' no existeix, s'utilitza 0.0 com a valor per defecte.")

if 'IVA SOPORTAT' in columns:
    df['iva suportat'] = df['IVA SOPORTAT'].apply(clean_currency)
else:
    df['iva suportat'] = 0.0  # Valor per defecte
    logging.warning("La columna 'IVA SOPORTAT' no existeix, s'utilitza 0.0 com a valor per defecte.")

if 'DATA' in columns:
    df['DATA'] = df['DATA'].apply(clean_date)
else:
    logging.error("La columna 'DATA' no existeix al CSV. Verifica les capçaleres.")
    exit(1)

if '%IVA' in columns:
    df['%IVA'] = df['%IVA'].fillna(0).astype(float)
else:
    df['%IVA'] = 0.0  # Valor per defecte
    logging.warning("La columna '%IVA' no existeix, s'utilitza 0.0 com a valor per defecte.")

# Filtrar files amb import total 0 o dates invàlides
df = df[(df['IMPORT TOTAL'] > 0) & (df['DATA'].notna())]
logging.info(f"Total de files després de netejar: {len(df)}")

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

# Crear índex únic a wp_contabilidad_compras si no existeix
try:
    cursor.execute("""
        ALTER TABLE wp_contabilidad_compras 
        ADD CONSTRAINT unique_compra UNIQUE (fecha, proveedor_id)
    """)
    logging.info("Índex únic creat a wp_contabilidad_compras.")
except mysql.connector.Error as e:
    if e.errno == 1061:  # Error per índex duplicat
        logging.info("L'índex únic ja existeix a wp_contabilidad_compras.")
    else:
        logging.error(f"Error en crear índex únic: {e}")
        cursor.close()
        conn.close()
        exit(1)

# Inserir proveïdors únics
proveedores = df['NOM'].dropna().unique() if 'NOM' in df.columns else []
proveedor_ids = {}
try:
    for proveedor in proveedores:
        cursor.execute("SELECT id FROM wp_contabilidad_proveedores WHERE nombre = %s", (proveedor,))
        result = cursor.fetchone()
        cursor.fetchall()
        if not result:
            cursor.execute(
                "INSERT INTO wp_contabilidad_proveedores (nombre, created_at, updated_at) VALUES (%s, NOW(), NOW())",
                (proveedor,)
            )
            proveedor_ids[proveedor] = cursor.lastrowid
            logging.info(f"Proveïdor '{proveedor}' inserit amb ID {proveedor_ids[proveedor]}.")
        else:
            proveedor_ids[proveedor] = result[0]
except mysql.connector.Error as e:
    logging.error(f"Error en inserir proveïdors: {e}")
    conn.rollback()
    cursor.close()
    conn.close()
    exit(1)

# Inserir conceptes com a productes
conceptes = df['CONCEPTE'].dropna().unique() if 'CONCEPTE' in df.columns else []
product_ids = {}
default_category_id = 7  # ID de categoria per defecte
try:
    for concepte in conceptes:
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

# Inserir despeses i detalls de despeses
try:
    for index, row in df.iterrows():
        # Comprovar si la despesa ja existeix
        cursor.execute(
            "SELECT id FROM wp_contabilidad_compras WHERE fecha = %s AND proveedor_id = %s",
            (row['DATA'], proveedor_ids.get(row['NOM'], None) if 'NOM' in df.columns else None)
        )
        if cursor.fetchone():
            logging.info(f"Despesa ja existent per proveïdor {row.get('NOM', 'Desconegut')}, data {row['DATA']}. Saltant...")
            continue

        # Preparar dades
        total = row.get('IMPORT TOTAL', 0.0)
        base_iva = row.get('BASE IVA', 0.0)
        iva_porcentaje = row.get('%IVA', 0.0)
        iva_monto = row.get('iva suportat', 0.0) if pd.notna(row.get('iva suportat')) else round(total - base_iva, 2)
        notas = f"Forma de pagament: {row.get('FORMA PAGAMENT', 'Desconegut')}"
        if pd.notna(row.get('NUMERO DOCUMENT')):
            notas += f", Número document: {row['NUMERO DOCUMENT']}"

        # Inserir a wp_contabilidad_compras
        cursor.execute(
            """
            INSERT INTO wp_contabilidad_compras 
            (fecha, proveedor_id, subtotal, iva_monto, total, notas, created_at)
            VALUES (%s, %s, %s, %s, %s, %s, NOW())
            """,
            (
                row['DATA'],
                proveedor_ids.get(row.get('NOM', None), None),
                base_iva,
                iva_monto,
                total,
                notas
            )
        )
        compra_id = cursor.lastrowid
        logging.info(f"Despesa inserida amb ID {compra_id} per al proveïdor {row.get('NOM', 'Desconegut')}.")

        # Inserir a wp_contabilidad_detalles_compra
        cursor.execute(
            """
            INSERT INTO wp_contabilidad_detalles_compra 
            (compra_id, producto_id, cantidad, precio_unitario, iva_porcentaje, iva_monto, subtotal, created_at)
            VALUES (%s, %s, %s, %s, %s, %s, %s, NOW())
            """,
            (
                compra_id,
                product_ids.get(row.get('CONCEPTE', 'Desconegut'), None),
                1,  # Assumim quantitat 1
                base_iva,
                iva_porcentaje,
                iva_monto,
                base_iva
            )
        )
        logging.info(f"Detall de despesa inserit per al concepte {row.get('CONCEPTE', 'Desconegut')}.")
except mysql.connector.Error as e:
    logging.error(f"Error en inserir despeses o detalls: {e}")
    conn.rollback()
    cursor.close()
    conn.close()
    exit(1)

# Confirmar canvis i tancar connexió
try:
    conn.commit()
    logging.info("Totes les dades s'han importat correctament a la base de dades.")
except mysql.connector.Error as e:
    logging.error(f"Error en confirmar canvis: {e}")
    conn.rollback()
finally:
    cursor.close()
    conn.close()

print("Importació de despeses completada amb èxit.")