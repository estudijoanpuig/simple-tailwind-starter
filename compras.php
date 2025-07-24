<?php include 'head.php';?>
<!-- Content -->
<main class="p-6">
    <h1 class="mb-2 text-4xl font-extrabold leading-none tracking-normal text-gray-900 md:text-6xl md:tracking-tight text-center">
        Gestio<span class="block w-full py-2 text-transparent bg-clip-text leading-12 bg-gradient-to-r from-green-400 to-purple-500 lg:inline">Compras</span>
    </h1>
    <script>
        // Copia el text del <h1> al <title>
        document.title = document.querySelector('h1').textContent;
    </script>
	
	<?php
// crud_compras.php


// Crear o actualizar compra
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_compra') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $proveedor_id = (int)$_POST['proveedor_id'];
    $fecha = $_POST['fecha'];

    // Convertir data de d/m/Y a Y-m-d si cal
    if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $fecha)) {
        $dateTime = DateTime::createFromFormat('d/m/Y', $fecha);
        if ($dateTime) {
            $fecha = $dateTime->format('Y-m-d');
        } else {
            echo "<div class='alert alert-danger'>Format de data no vàlid.</div>";
            exit;
        }
    }

    $notas = trim($_POST['notas']);
    $detalles = isset($_POST['detalles']) ? $_POST['detalles'] : [];

    // Validacions bàsiques
    if (empty($proveedor_id)) {
        echo "<div class='alert alert-danger'>El proveïdor és obligatori.</div>";
    } elseif (empty($fecha)) {
        echo "<div class='alert alert-danger'>La data és obligatòria.</div>";
    } elseif (empty($detalles)) {
        echo "<div class='alert alert-danger'>Cal afegir almenys un detall de compra.</div>";
    } else {
        try {
            // Calcular subtotal i iva_monto sumant els valors dels detalls
            $subtotal = 0;
            $iva_monto = 0;
            foreach ($detalles as $detalle) {
                $subtotal += floatval($detalle['subtotal']);
                $iva_monto += floatval($detalle['iva_monto']);
            }
            $total = $subtotal + $iva_monto;

            $pdo->beginTransaction();

            if ($id) {
                // Actualitzar compra
                $sql = "UPDATE wp_contabilidad_compras SET proveedor_id = ?, fecha = ?, subtotal = ?, iva_monto = ?, total = ?, notas = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$proveedor_id, $fecha, $subtotal, $iva_monto, $total, $notas, $id]);

                // Eliminar detalls existents
                $sql_delete = "DELETE FROM wp_contabilidad_detalles_compra WHERE compra_id = ?";
                $stmt_delete = $pdo->prepare($sql_delete);
                $stmt_delete->execute([$id]);
            } else {
                // Crear compra
                $sql = "INSERT INTO wp_contabilidad_compras (proveedor_id, fecha, subtotal, iva_monto, total, notas) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$proveedor_id, $fecha, $subtotal, $iva_monto, $total, $notas]);
                $id = $pdo->lastInsertId();
            }

            // Obtenir ID de la categoria A_GASTO
            $sql_categoria_gasto = "SELECT id FROM wp_contabilidad_categoria_productos WHERE nombre_categoria = 'A_GASTO'";
            $stmt_categoria_gasto = $pdo->query($sql_categoria_gasto);
            $categoria_gasto_id = $stmt_categoria_gasto->fetchColumn();

            // Insertar detalls i actualitzar stock si no és un gasto
            $sql_detalle = "INSERT INTO wp_contabilidad_detalles_compra (compra_id, producto_id, cantidad, precio_unitario, iva_porcentaje, iva_monto, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_detalle = $pdo->prepare($sql_detalle);

            $sql_update_stock = "UPDATE wp_contabilidad_productos SET stock = stock + ? WHERE id = ? AND id_categoria_producto != ?";
            $stmt_update_stock = $pdo->prepare($sql_update_stock);

            foreach ($detalles as $detalle) {
                $producto_id = (int)$detalle['producto_id'];
                $cantidad = (int)$detalle['cantidad'];
                $precio_unitario = floatval($detalle['precio_unitario']);
                $iva_porcentaje = floatval($detalle['iva_porcentaje']);
                $subtotal_detalle = floatval($detalle['subtotal']);
                $iva_monto_detalle = floatval($detalle['iva_monto']);

                // Insertar detall
                $stmt_detalle->execute([$id, $producto_id, $cantidad, $precio_unitario, $iva_porcentaje, $iva_monto_detalle, $subtotal_detalle]);

                // Actualitzar stock només si no és un gasto
                $stmt_update_stock->execute([$cantidad, $producto_id, $categoria_gasto_id]);
            }

            $pdo->commit();
            echo "<div class='alert alert-success'>Compra " . ($id ? 'actualitzada' : 'creada') . " correctament.</div>";
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    }
}

// Eliminar compra
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $pdo->beginTransaction();

        // Obtenir ID de la categoria A_GASTO
        $sql_categoria_gasto = "SELECT id FROM wp_contabilidad_categoria_productos WHERE nombre_categoria = 'A_GASTO'";
        $stmt_categoria_gasto = $pdo->query($sql_categoria_gasto);
        $categoria_gasto_id = $stmt_categoria_gasto->fetchColumn();

        // Obtenir detalls per restar stock (només per a productes no gastos)
        $sql_detalles = "SELECT d.producto_id, d.cantidad, p.id_categoria_producto 
                         FROM wp_contabilidad_detalles_compra d 
                         JOIN wp_contabilidad_productos p ON d.producto_id = p.id 
                         WHERE d.compra_id = ?";
        $stmt_detalles = $pdo->prepare($sql_detalles);
        $stmt_detalles->execute([$id]);
        $detalles = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);

        $sql_update_stock = "UPDATE wp_contabilidad_productos SET stock = stock - ? WHERE id = ? AND id_categoria_producto != ?";
        $stmt_update_stock = $pdo->prepare($sql_update_stock);

        foreach ($detalles as $detalle) {
            // Restar stock només si no és un gasto
            $stmt_update_stock->execute([$detalle['cantidad'], $detalle['producto_id'], $categoria_gasto_id]);
        }

        // Eliminar detalls
        $sql_delete_detalles = "DELETE FROM wp_contabilidad_detalles_compra WHERE compra_id = ?";
        $stmt_delete_detalles = $pdo->prepare($sql_delete_detalles);
        $stmt_delete_detalles->execute([$id]);

        // Eliminar compra
        $sql_delete = "DELETE FROM wp_contabilidad_compras WHERE id = ?";
        $stmt_delete = $pdo->prepare($sql_delete);
        $stmt_delete->execute([$id]);

        $pdo->commit();
        echo "<div class='alert alert-success'>Compra eliminada correctament.</div>";
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "<div class='alert alert-danger'>Error al eliminar: " . $e->getMessage() . "</div>";
    }
}

// Obtenir llista de compres
try {
    $sql = "SELECT c.*, p.nombre AS nombre_proveedor, MIN(d.producto_id) AS producto_id, 
            MIN(pr.nombre) AS nombre_producto, MIN(cat.nombre_categoria) AS nombre_categoria 
            FROM wp_contabilidad_compras c 
            LEFT JOIN wp_contabilidad_proveedores p ON c.proveedor_id = p.id 
            LEFT JOIN wp_contabilidad_detalles_compra d ON c.id = d.compra_id 
            LEFT JOIN wp_contabilidad_productos pr ON d.producto_id = pr.id 
            LEFT JOIN wp_contabilidad_categoria_productos cat ON pr.id_categoria_producto = cat.id 
            GROUP BY c.id, c.proveedor_id, c.fecha, c.subtotal, c.iva_monto, c.total, c.notas, c.created_at, c.updated_at, p.nombre";
    $stmt = $pdo->query($sql);
    $compras = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error al carregar compres: " . $e->getMessage() . "</div>";
    $compras = [];
}

// Obtenir dades per a edició
$compra_editar = null;
$detalles_editar = [];
if (isset($_GET['accion']) && $_GET['accion'] === 'editar' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        // Obtenir compra
        $sql = "SELECT * FROM wp_contabilidad_compras WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $compra_editar = $stmt->fetch(PDO::FETCH_ASSOC);

        // Obtenir detalls
        $sql_detalles = "SELECT d.*, p.nombre AS nombre_producto 
                         FROM wp_contabilidad_detalles_compra d 
                         LEFT JOIN wp_contabilidad_productos p ON d.producto_id = p.id 
                         WHERE d.compra_id = ?";
        $stmt_detalles = $pdo->prepare($sql_detalles);
        $stmt_detalles->execute([$id]);
        $detalles_editar = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error al carregar compra: " . $e->getMessage() . "</div>";
    }
}

// Obtenir llista de proveïdors i productes per a formularis
try {
    $sql_proveedores = "SELECT id, nombre FROM wp_contabilidad_proveedores";
    $stmt_proveedores = $pdo->query($sql_proveedores);
    $proveedores = $stmt_proveedores->fetchAll(PDO::FETCH_ASSOC);

    // Incluir categoria per identificar gastos, ordenar productes alfabèticament
    $sql_productos = "SELECT p.id, p.nombre, p.precio, p.id_categoria_producto, c.nombre_categoria 
                      FROM wp_contabilidad_productos p 
                      LEFT JOIN wp_contabilidad_categoria_productos c ON p.id_categoria_producto = c.id 
                      ORDER BY p.nombre ASC";
    $stmt_productos = $pdo->query($sql_productos);
    $productos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error al carregar dades: " . $e->getMessage() . "</div>";
    $proveedores = [];
    $productos = [];
}
?>

	

    <!-- Formulari per crear/actualitzar compra -->
    <div class="w-full p-[5px] m-[5px]">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Gestió de Compres</h2>
        <form method="POST" class="space-y-6" id="compraForm">
            <input type="hidden" name="action" value="save_compra">
            <input type="hidden" name="id" value="<?php echo $compra_editar ? $compra_editar['id'] : ''; ?>">
            <div>
                <label for="proveedor_id" class="block text-sm font-medium text-gray-700 mb-1">Proveïdor</label>
                <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" name="proveedor_id" required>
                    <option value="">Selecciona un proveïdor</option>
                    <?php foreach ($proveedores as $proveedor): ?>
                        <option value="<?php echo $proveedor['id']; ?>" <?php echo ($compra_editar && $compra_editar['proveedor_id'] == $proveedor['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($proveedor['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="fecha_display" class="block text-sm font-medium text-gray-700 mb-1">Data</label>
                <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 flatpickr" id="fecha_display" placeholder="dd/mm/aaaa">
                <input type="hidden" name="fecha" id="fecha_hidden" value="<?php echo $compra_editar ? $compra_editar['fecha'] : date('Y-m-d'); ?>">
            </div>
            <div>
                <label for="notas" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" name="notas"><?php echo $compra_editar ? htmlspecialchars($compra_editar['notas'] ?? '') : ''; ?></textarea>
            </div>

            <!-- Secció de detalls de compra -->
            <div>
                <h4 class="text-lg font-semibold text-gray-800 mb-4">Detalls de la Compra</h4>
                <div id="detallesContainer" class="space-y-4">
                    <?php if ($compra_editar && $detalles_editar): ?>
                        <?php foreach ($detalles_editar as $index => $detalle): ?>
                            <div class="detalle-row border border-gray-200 rounded-lg p-4">
                                <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Producte</label>
                                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 producto-select" name="detalles[<?php echo $index; ?>][producto_id]" required>
                                            <option value="">Selecciona un producte</option>
                                            <?php foreach ($productos as $producto): ?>
                                                <option value="<?php echo $producto['id']; ?>" 
                                                        data-precio="<?php echo $producto['precio']; ?>" 
                                                        data-categoria="<?php echo $producto['nombre_categoria']; ?>" 
                                                        <?php echo ($detalle['producto_id'] == $producto['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($producto['nombre']); ?> (<?php echo $producto['nombre_categoria']; ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantitat</label>
                                        <input type="number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 cantidad-input" name="detalles[<?php echo $index; ?>][cantidad]" value="<?php echo $detalle['cantidad']; ?>" min="1" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Preu Unitari (€)</label>
                                        <input type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 precio-unitario" name="detalles[<?php echo $index; ?>][precio_unitario]" value="<?php echo number_format($detalle['precio_unitario'], 2, '.', ''); ?>" placeholder="Preu suggerit: <?php echo number_format($detalle['precio_unitario'], 2, '.', ''); ?>">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">IVA (%)</label>
                                        <input type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 iva-porcentaje" name="detalles[<?php echo $index; ?>][iva_porcentaje]" value="<?php echo number_format($detalle['iva_porcentaje'], 2, '.', ''); ?>" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Subtotal (€)</label>
                                        <input type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed subtotal" name="detalles[<?php echo $index; ?>][subtotal]" value="<?php echo number_format($detalle['subtotal'], 2, '.', ''); ?>" readonly>
                                    </div>
                                    <div class="flex items-end">
                                        <button type="button" class="w-full px-3 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors remove-detalle" title="Eliminar">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                    <input type="hidden" class="iva-monto" name="detalles[<?php echo $index; ?>][iva_monto]" value="<?php echo number_format($detalle['iva_monto'], 2, '.', ''); ?>">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button type="button" class="mt-4 px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors" id="addDetalle">Afegir Detall</button>
                <button type="submit" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"><?php echo $compra_editar ? 'Actualitzar' : 'Crear'; ?> Compra</button>
            </div>
        </form>
    </div>

    <!-- Taula de compres amb DataTables -->
    <div class="w-full p-[5px] m-[5px]">
        <table id="tablaCompras" class="display nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Proveïdor</th>
                    <th>Data</th>
                    <th>Categoria</th>
                    <th>Subtotal (€)</th>
                    <th>Import IVA (€)</th>
                    <th>Total (€)</th>
                    <th>Notes</th>
                    <th>Creat</th>
                    <th>Actualitzat</th>
                    <th>Accions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($compras as $compra): ?>
                    <tr>
                        <td><?php echo $compra['id']; ?></td>
                        <td><?php echo htmlspecialchars($compra['nombre_proveedor'] ?? 'Sense proveïdor'); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($compra['fecha'])); ?></td>
                        <td><?php echo htmlspecialchars($compra['nombre_categoria'] ?? 'Sense categoria'); ?></td>
                        <td><?php echo number_format($compra['subtotal'], 2, ',', '.'); ?></td>
                        <td><?php echo number_format($compra['iva_monto'], 2, ',', '.'); ?></td>
                        <td><?php echo number_format($compra['total'], 2, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($compra['notas'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($compra['created_at'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($compra['updated_at'] ?? ''); ?></td>
                        <td class="text-center space-x-2">
                            <a href="?tabla=compras&accion=editar&id=<?php echo $compra['id']; ?>" class="text-blue-600 hover:text-blue-800 transition-colors" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?tabla=compras&accion=eliminar&id=<?php echo $compra['id']; ?>" class="text-red-600 hover:text-red-800 transition-colors" 
                               onclick="return confirm('¿Segur que vols eliminar?')" title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                            <a href="?tabla=compras&accion=detalles&id=<?php echo $compra['id']; ?>" class="text-green-600 hover:text-green-800 transition-colors" title="Veure Detalls">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Taula de detalls (mostrada al fer clic a "Veure Detalls") -->
    <?php if (isset($_GET['accion']) && $_GET['accion'] === 'detalles' && isset($_GET['id'])): ?>
    <?php
    $compra_id = (int)$_GET['id'];
    try {
        $sql_detalles = "SELECT d.*, p.nombre AS nombre_producto, c.nombre_categoria 
                         FROM wp_contabilidad_detalles_compra d 
                         LEFT JOIN wp_contabilidad_productos p ON d.producto_id = p.id 
                         LEFT JOIN wp_contabilidad_categoria_productos c ON p.id_categoria_producto = c.id 
                         WHERE d.compra_id = ?";
        $stmt_detalles = $pdo->prepare($sql_detalles);
        $stmt_detalles->execute([$compra_id]);
        $detalles = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error al carregar detalls: " . $e->getMessage() . "</div>";
        $detalles = [];
    }
    ?>
    <div class="max-w-7xl mx-auto bg-white rounded-lg shadow-md p-6 mt-8">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Detalls de la Compra #<?php echo $compra_id; ?></h3>
        <table id="tablaDetallesCompra" class="display nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Producte</th>
                    <th>Categoria</th>
                    <th>Quantitat</th>
                    <th>Preu Unitari (€)</th>
                    <th>IVA (%)</th>
                    <th>Import IVA (€)</th>
                    <th>Subtotal (€)</th>
                    <th>Creat</th>
                    <th>Actualitzat</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $detalle): ?>
                    <tr>
                        <td><?php echo $detalle['id']; ?></td>
                        <td><?php echo htmlspecialchars($detalle['nombre_producto'] ?? 'Sense producte'); ?></td>
                        <td><?php echo htmlspecialchars($detalle['nombre_categoria'] ?? 'Sense categoria'); ?></td>
                        <td><?php echo $detalle['cantidad']; ?></td>
                        <td><?php echo number_format($detalle['precio_unitario'], 2, ',', '.'); ?></td>
                        <td><?php echo number_format($detalle['iva_porcentaje'], 2, ',', '.'); ?></td>
                        <td><?php echo number_format($detalle['iva_monto'], 2, ',', '.'); ?></td>
                        <td><?php echo number_format($detalle['subtotal'], 2, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($detalle['created_at'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($detalle['updated_at'] ?? ''); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
</main>

<!-- Plantilla per a nous detalls -->
<script type="text/template" id="detalleTemplate">
    <div class="detalle-row border border-gray-200 rounded-lg p-4">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Producte</label>
                <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 producto-select" name="detalles[{index}][producto_id]" required>
                    <option value="">Selecciona un producte</option>
                    <?php foreach ($productos as $producto): ?>
                        <option value="<?php echo $producto['id']; ?>" 
                                data-precio="<?php echo $producto['precio']; ?>" 
                                data-categoria="<?php echo $producto['nombre_categoria']; ?>">
                            <?php echo htmlspecialchars($producto['nombre']); ?> (<?php echo $producto['nombre_categoria']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantitat</label>
                <input type="number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 cantidad-input" name="detalles[{index}][cantidad]" min="1" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Preu Unitari (€)</label>
                <input type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 precio-unitario" name="detalles[{index}][precio_unitario]" placeholder="Preu suggerit: ">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">IVA (%)</label>
                <input type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 iva-porcentaje" name="detalles[{index}][iva_porcentaje]" value="21.00" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Subtotal (€)</label>
                <input type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed subtotal" name="detalles[{index}][subtotal]" readonly>
            </div>
            <div class="flex items-end">
                <button type="button" class="w-full px-3 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors remove-detalle" title="Eliminar">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
            <input type="hidden" class="iva-monto" name="detalles[{index}][iva_monto]">
        </div>
    </div>
</script>

<!-- Configuració de Flatpickr i DataTables -->
<script>
$(document).ready(function() {
    // Inicialitzar Flatpickr
    flatpickr(".flatpickr", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d/m/Y",
        defaultDate: "<?php echo $compra_editar ? $compra_editar['fecha'] : date('Y-m-d'); ?>",
        locale: "ca",
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length > 0) {
                $('#fecha_hidden').val(selectedDates[0].toISOString().split('T')[0]);
            } else {
                $('#fecha_hidden').val('');
            }
        }
    });

    // Inicialitzar DataTables
    $('#tablaCompras').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ca.json'
        },
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: true, targets: '_all' },
            { orderable: false, targets: -1 }
        ],
        searching: true,
        paging: true
    });

    $('#tablaDetallesCompra').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ca.json'
        },
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: true, targets: '_all' }
        ],
        searching: true,
        paging: true
    });

    // Contador per als índexs dels detalls
    let detalleIndex = <?php echo $compra_editar ? count($detalles_editar) : 0; ?>;

    // Afegir nou detall
    $('#addDetalle').on('click', function() {
        let template = $('#detalleTemplate').html();
        template = template.replace(/{index}/g, detalleIndex);
        $('#detallesContainer').append(template);
        detalleIndex++;
        actualizarCalculos();
    });

    // Eliminar detall
    $(document).on('click', '.remove-detalle', function() {
        $(this).closest('.detalle-row').remove();
        actualizarCalculos();
    });

    // Actualitzar placeholder del preu unitari al canviar el producte
    $(document).on('change', '.producto-select', function() {
        let $row = $(this).closest('.detalle-row');
        let precioSugerido = parseFloat($(this).find('option:selected').data('precio')) || 0;
        $row.find('.precio-unitario').attr('placeholder', 'Preu suggerit: ' + precioSugerido.toFixed(2));
        actualizarCalculos();
    });

    // Actualitzar càlculs al canviar producte, quantitat, preu unitari o IVA
    $(document).on('change', '.producto-select, .cantidad-input, .precio-unitario, .iva-porcentaje', function() {
        actualizarCalculos();
    });

    // Funció per actualitzar càlculs
    function actualizarCalculos() {
        $('.detalle-row').each(function() {
            let $row = $(this);
            let $productoSelect = $row.find('.producto-select');
            let $cantidadInput = $row.find('.cantidad-input');
            let $precioUnitario = $row.find('.precio-unitario');
            let $ivaPorcentaje = $row.find('.iva-porcentaje');
            let $subtotal = $row.find('.subtotal');
            let $ivaMonto = $row.find('.iva-monto');

            // Obtenir valors
            let precio = parseFloat($precioUnitario.val()) || 0;
            let cantidad = parseInt($cantidadInput.val()) || 0;
            let ivaPorcentaje = parseFloat($ivaPorcentaje.val()) || 0;

            // Calcular subtotal i IVA
            let subtotal = precio * cantidad;
            let ivaMonto = (subtotal * ivaPorcentaje) / 100;

            // Actualitzar camps
            $subtotal.val(subtotal.toFixed(2));
            $ivaMonto.val(ivaMonto.toFixed(2));
        });
    }

    // Inicialitzar càlculs per a files existents
    actualizarCalculos();
});
</script>

<?php include 'footer.php';?>