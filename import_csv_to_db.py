import pandas as pd
import mysql.connector
from datetime import datetime
import uuid
import logging
import os
import sys

# Configurar logging per rastrejar errors i progrés
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

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

# Verificar si el fitxer CSV existeix
if not os.path.exists(csv_file):
    logging.error(f"El fitxer CSV '{csv_file}' no existeix. Verifica la ruta i el nom del fitxer.")
    exit(1)

# Llegir el fitxer CSV
try:
    df = pd.read_csv(csv_file, encoding='utf-8')
    logging.info(f"Fitxer CSV '{csv_file}' llegit correctament. Total de files: {len(df)}")
except Exception as e:
    logging.error(f"Error en llegir el fitxer CSV: {e}")
    exit(1)

# Netejar i preprocessar les dades
def clean_currency(value):
    try:
        if pd.isna(value) or value == '':
            logging.warning(f"Valor monetari buit detectat. Assignant 0.0.")
            return 0.0
        if isinstance(value, str):
            return float(value.replace(' €', '').replace(',', '.'))
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

# Eliminar files amb camps essencials buits o invàlids
df = df.dropna(subset=['data', 'client', 'producte', 'import total', 'empleat'])
df['import total'] = df['import total'].apply(clean_currency)
df['data'] = df['data'].apply(clean_date)
df = df.dropna(subset=['data'])
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
    cursor.execute("SHOW TABLES LIKE 'wp_contabilidad_productos'")
    if not cursor.fetchone():
        logging.error("La taula wp_contabilidad_productos no existeix. Assegura't que l'esquema està creat.")
        cursor.close()
        conn.close()
        exit(1)
    cursor.fetchall()
except mysql.connector.Error as e:
    logging.error(f"Error en comprovar taules: {e}")
    cursor.close()
    conn.close()
    exit(1)

# Crear índex únic a wp_contabilidad_ventas si no existeix
try:
    cursor.execute("""
        ALTER TABLE wp_contabilidad_ventas 
        ADD CONSTRAINT unique_venta UNIQUE (fecha, cliente_id, empleado_id)
    """)
    logging.info("Índex únic creat a wp_contabilidad_ventas.")
except mysql.connector.Error as e:
    if e.errno == 1061:  # Error per índex duplicat (ja existeix)
        logging.info("L'índex únic ja existeix a wp_contabilidad_ventas.")
    else:
        logging.error(f"Error en crear índex únic: {e}")
        cursor.close()
        conn.close()
        exit(1)

# Inserir clients únics
clients = df['client'].unique()
client_ids = {}
try:
    for client in clients:
        cursor.execute("SELECT id FROM wp_contabilidad_clientes WHERE nombre = %s", (client,))
        result = cursor.fetchone()
        cursor.fetchall()
        if not result:
            cursor.execute(
                "INSERT INTO wp_contabilidad_clientes (nombre, created_at) VALUES (%s, NOW())",
                (client,)
            )
            client_ids[client] = cursor.lastrowid
            logging.info(f"Client '{client}' inserit amb ID {client_ids[client]}.")
        else:
            client_ids[client] = result[0]
except mysql.connector.Error as e:
    logging.error(f"Error en inserir clients: {e}")
    conn.rollback()
    cursor.close()
    conn.close()
    exit(1)

# Inserir empleats únics
employees = df['empleat'].unique()
employee_ids = {}
try:
    for employee in employees:
        cursor.execute("SELECT id FROM wp_contabilidad_empleados WHERE nombre = %s", (employee,))
        result = cursor.fetchone()
        cursor.fetchall()
        if not result:
            cursor.execute(
                "INSERT INTO wp_contabilidad_empleados (nombre, nif, created_at) VALUES (%s, %s, NOW())",
                (employee, str(uuid.uuid4())[:20])
            )
            employee_ids[employee] = cursor.lastrowid
            logging.info(f"Empleat '{employee}' inserit amb ID {employee_ids[employee]}.")
        else:
            employee_ids[employee] = result[0]
except mysql.connector.Error as e:
    logging.error(f"Error en inserir empleats: {e}")
    conn.rollback()
    cursor.close()
    conn.close()
    exit(1)

# Inserir productes únics
products = df['producte'].unique()
product_ids = {}
default_category_id = 7
try:
    for product in products:
        cursor.execute("SELECT id FROM wp_contabilidad_productos WHERE nombre = %s", (product,))
        result = cursor.fetchone()
        cursor.fetchall()
        if not result:
            cursor.execute(
                """
                INSERT INTO wp_contabilidad_productos 
                (nombre, id_categoria_producto, precio, stock, protocol, created_at, updated_at) 
                VALUES (%s, %s, %s, %s, %s, NOW(), NOW())
                """,
                (product, default_category_id, 0.00, 0, '')
            )
            product_ids[product] = cursor.lastrowid
            logging.info(f"Producte '{product}' inserit amb ID {product_ids[product]}.")
        else:
            product_ids[product] = result[0]
except mysql.connector.Error as e:
    logging.error(f"Error en inserir productes: {e}")
    conn.rollback()
    cursor.close()
    conn.close()
    exit(1)

# Inserir vendes i detalls de vendes
try:
    for index, row in df.iterrows():
        # Verificar si la venda ja existeix
        cursor.execute(
            """
            SELECT id FROM wp_contabilidad_ventas 
            WHERE fecha = %s AND cliente_id = %s AND empleado_id = %s
            """,
            (row['data'], client_ids[row['client']], employee_ids[row['empleat']])
        )
        result = cursor.fetchone()
        cursor.fetchall()
        if result:
            logging.info(f"Venda ja existent per client {row['client']}, empleat {row['empleat']}, data {row['data']}. Saltant...")
            continue

        # Comprovar si l'import total és vàlid
        if row['import total'] == 0.0:
            logging.warning(f"Import total 0.0 detectat per {row['client']} a {row['data']}. Saltant...")
            continue

        # Calcular subtotal i IVA (assumim IVA del 21%)
        total = row['import total']
        iva_porcentaje = 21.00
        subtotal = round(total / 1.21, 2)
        iva_monto = round(total - subtotal, 2)

        # Inserir a wp_contabilidad_ventas
        cursor.execute(
            """
            INSERT INTO wp_contabilidad_ventas 
            (fecha, cliente_id, subtotal, iva_porcentaje, iva_monto, total, notas, fecha_creacion, empleado_id)
            VALUES (%s, %s, %s, %s, %s, %s, %s, NOW(), %s)
            """,
            (
                row['data'],
                client_ids[row['client']],
                subtotal,
                iva_porcentaje,
                iva_monto,
                total,
                f"Metode de pagament: {row['metode pagament']}",
                employee_ids[row['empleat']]
            )
        )
        venta_id = cursor.lastrowid
        logging.info(f"Venda inserida amb ID {venta_id} per al client {row['client']}.")

        # Inserir a wp_contabilidad_detalles_venta
        cursor.execute(
            """
            INSERT INTO wp_contabilidad_detalles_venta 
            (venta_id, producto_id, cantidad, precio_unitario, subtotal)
            VALUES (%s, %s, %s, %s, %s)
            """,
            (
                venta_id,
                product_ids[row['producte']],
                1,
                subtotal,
                subtotal
            )
        )
        logging.info(f"Detall de venda inserit per al producte {row['producte']}.")
except mysql.connector.Error as e:
    logging.error(f"Error en inserir vendes o detalls: {e}, fila: {row.to_dict()}")
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

print("Importació completada amb èxit.")