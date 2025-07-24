<?php include 'head.php';?>

<!-- Content -->
<main class="p-6">
    <h1 class="mb-2 text-4xl font-extrabold leading-none tracking-normal text-gray-900 md:text-6xl md:tracking-tight text-center">
        Gestio<span class="block w-full py-2 text-transparent bg-clip-text leading-12 bg-gradient-to-r from-green-400 to-purple-500 lg:inline">Vendas</span>
    </h1>
    <script>
        // Copia el text del <h1> al <title>
        document.title = document.querySelector('h1').textContent;
    </script>

    <?php
    // crud/crud_ventas.php

    // Crear o actualitzar venda
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_venta') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
        $cliente_id = (int)$_POST['cliente_id'];
        $fecha = $_POST['fecha'];
        $iva_porcentaje = floatval($_POST['iva_porcentaje']);
        $notas = trim($_POST['notas']);
        $empleado_id = !empty($_POST['empleado_id']) ? (int)$_POST['empleado_id'] : null;
        $detalles = isset($_POST['detalles']) ? $_POST['detalles'] : [];

        // Validar fecha (esperem YYYY-MM-DD des de Flatpickr, però verifiquem per seguretat)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            echo "<div class='alert alert-danger'>Error: Format de data invàlid. Usa DD/MM/YYYY.</div>";
            $fecha = null;
        } else {
            // Validar que la data sigui vàlida
            $date_parts = explode('-', $fecha);
            if (!checkdate($date_parts[1], $date_parts[2], $date_parts[0])) {
                echo "<div class='alert alert-danger'>Error: Data invàlida.</div>";
                $fecha = null;
            }
        }

        // Validacions bàsiques
        if (empty($cliente_id)) {
            echo "<div class='alert alert-danger'>El client és obligatori.</div>";
        } elseif (empty($fecha)) {
            echo "<div class='alert alert-danger'>La data és obligatòria.</div>";
        } elseif ($iva_porcentaje < 0) {
            echo "<div class='alert alert-danger'>El percentatge d'IVA ha de ser major o igual a 0.</div>";
        } elseif (empty($detalles)) {
            echo "<div class='alert alert-danger'>Cal afegir almenys un detall de venda.</div>";
        } else {
            try {
                // Calcular subtotal, iva_monto i total
                $subtotal = 0;
                foreach ($detalles as $detalle) {
                    $subtotal += floatval($detalle['subtotal']);
                }
                $iva_monto = $subtotal * ($iva_porcentaje / 100);
                $total = $subtotal + $iva_monto;

                $pdo->beginTransaction();

                if ($id) {
                    // Actualitzar venda
                    $sql = "UPDATE wp_contabilidad_ventas SET cliente_id = ?, fecha = ?, subtotal = ?, iva_porcentaje = ?, iva_monto = ?, total = ?, notas = ?, empleado_id = ? WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$cliente_id, $fecha, $subtotal, $iva_porcentaje, $iva_monto, $total, $notas, $empleado_id, $id]);

                    // Eliminar detalls existents
                    $sql_delete = "DELETE FROM wp_contabilidad_detalles_venta WHERE venta_id = ?";
                    $stmt_delete = $pdo->prepare($sql_delete);
                    $stmt_delete->execute([$id]);
                } else {
                    // Crear venda
                    $sql = "INSERT INTO wp_contabilidad_ventas (cliente_id, fecha, subtotal, iva_porcentaje, iva_monto, total, notas, empleado_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$cliente_id, $fecha, $subtotal, $iva_porcentaje, $iva_monto, $total, $notas, $empleado_id]);
                    $id = $pdo->lastInsertId();
                }

                // Insertar detalls
                $sql_detalle = "INSERT INTO wp_contabilidad_detalles_venta (venta_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)";
                $stmt_detalle = $pdo->prepare($sql_detalle);
                foreach ($detalles as $detalle) {
                    $producto_id = (int)$detalle['producto_id'];
                    $cantidad = (int)$detalle['cantidad'];
                    $precio_unitario = floatval($detalle['precio_unitario']);
                    $subtotal_detalle = floatval($detalle['subtotal']);
                    $stmt_detalle->execute([$id, $producto_id, $cantidad, $precio_unitario, $subtotal_detalle]);
                }

                $pdo->commit();
                echo "<div class='alert alert-success'>Venda " . ($id ? 'actualitzada' : 'creada') . " correctament.</div>";
            } catch (PDOException $e) {
                $pdo->rollBack();
                echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
            }
        }
    }

    // Eliminar venda
    if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        try {
            $pdo->beginTransaction();
            // Eliminar detalls
            $sql_delete_detalles = "DELETE FROM wp_contabilidad_detalles_venta WHERE venta_id = ?";
            $stmt_delete_detalles = $pdo->prepare($sql_delete_detalles);
            $stmt_delete_detalles->execute([$id]);
            // Eliminar venda
            $sql_delete = "DELETE FROM wp_contabilidad_ventas WHERE id = ?";
            $stmt_delete = $pdo->prepare($sql_delete);
            $stmt_delete->execute([$id]);
            $pdo->commit();
            echo "<div class='alert alert-success'>Venda eliminada correctament.</div>";
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo "<div class='alert alert-danger'>Error al eliminar: " . $e->getMessage() . "</div>";
        }
    }

    // Obtenir llista de vendes
    try {
        $sql = "SELECT v.*, c.nombre AS nombre_cliente, e.nombre AS nombre_empleado 
                FROM wp_contabilidad_ventas v 
                LEFT JOIN wp_contabilidad_clientes c ON v.cliente_id = c.id 
                LEFT JOIN wp_contabilidad_empleados e ON v.empleado_id = e.id";
        $stmt = $pdo->query($sql);
        $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error al carregar vendes: " . $e->getMessage() . "</div>";
        $ventas = [];
    }

    // Obtenir dades per a edició
    $venta_editar = null;
    $detalles_editar = [];
    if (isset($_GET['accion']) && $_GET['accion'] === 'editar' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        try {
            // Obtenir venda
            $sql = "SELECT * FROM wp_contabilidad_ventas WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $venta_editar = $stmt->fetch(PDO::FETCH_ASSOC);

            // Obtenir detalls
            $sql_detalles = "SELECT d.*, p.nombre AS nombre_producto 
                             FROM wp_contabilidad_detalles_venta d 
                             LEFT JOIN wp_contabilidad_productos p ON d.producto_id = p.id 
                             WHERE d.venta_id = ?";
            $stmt_detalles = $pdo->prepare($sql_detalles);
            $stmt_detalles->execute([$id]);
            $detalles_editar = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Error al carregar venda: " . $e->getMessage() . "</div>";
        }
    }

    // Obtenir llista de clients, empleats i productes per a formularis
    try {
        $sql_clientes = "SELECT id, nombre FROM wp_contabilidad_clientes";
        $stmt_clientes = $pdo->query($sql_clientes);
        $clientes = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);

        $sql_empleados = "SELECT id, nombre FROM wp_contabilidad_empleados ORDER BY nombre ASC";
        $stmt_empleados = $pdo->query($sql_empleados);
        $empleados = $stmt_empleados->fetchAll(PDO::FETCH_ASSOC);

        $sql_productos = "SELECT id, nombre, precio FROM wp_contabilidad_productos ORDER BY nombre ASC";
        $stmt_productos = $pdo->query($sql_productos);
        $productos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);

        // Comprovar si $productos està buit
        if (empty($productos)) {
            echo "<div class='alert alert-danger'>No hi ha productes disponibles per afegir detalls.</div>";
        }
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error al carregar dades: " . $e->getMessage() . "</div>";
        $clientes = [];
        $empleados = [];
        $productos = [];
    }
    ?>
    <div class="w-full p-[5px] m-[5px]">
        <!-- Formulari per crear/actualitzar venda -->
        <h2 class="mb-4">Gestió de Vendes</h2>
        <form method="POST" class="mb-6 bg-white shadow-md rounded-xl p-6" id="ventaForm">
            <input type="hidden" name="action" value="save_venta">
            <input type="hidden" name="id" value="<?php echo $venta_editar ? $venta_editar['id'] : ''; ?>">

            <div class="mb-4">
                <label for="cliente_id" class="block text-sm font-medium text-gray-700 mb-1">Client</label>
                <select class="w-full border border-gray-300 rounded-lg p-2 focus:ring focus:ring-blue-200" name="cliente_id" required>
                    <option value="">Selecciona un client</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?php echo $cliente['id']; ?>" <?php echo ($venta_editar && $venta_editar['cliente_id'] == $cliente['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cliente['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label for="fecha" class="block text-sm font-medium text-gray-700 mb-1">Data</label>
                <input type="text" class="w-full border border-gray-300 rounded-lg p-2 flatpickr focus:ring focus:ring-blue-200" name="fecha" id="fecha" 
                       value="<?php echo $venta_editar ? date('d/m/Y', strtotime($venta_editar['fecha'])) : date('d/m/Y'); ?>" 
                       placeholder="DD/MM/YYYY" required>
            </div>

            <div class="mb-4">
                <label for="iva_porcentaje" class="block text-sm font-medium text-gray-700 mb-1">Percentatge d'IVA (%)</label>
                <input type="number" step="0.01" class="w-full border border-gray-300 rounded-lg p-2 focus:ring focus:ring-blue-200" name="iva_porcentaje" 
                       value="<?php echo $venta_editar ? $venta_editar['iva_porcentaje'] : '21.00'; ?>" required>
            </div>

            <div class="mb-4">
                <label for="notas" class="block text-sm font-medium text-gray-700 mb-1">Mètode de Pagament</label>
                <select class="w-full border border-gray-300 rounded-lg p-2 focus:ring focus:ring-blue-200" name="notas" required>
                    <option value="efectivo" <?php echo ($venta_editar && $venta_editar['notas'] == 'efectivo') ? 'selected' : ''; ?>>Efectiu</option>
                    <option value="tarjeta" <?php echo ($venta_editar && $venta_editar['notas'] == 'tarjeta') ? 'selected' : ''; ?>>Targeta</option>
                    <option value="bono" <?php echo ($venta_editar && $venta_editar['notas'] == 'bono') ? 'selected' : ''; ?>>Bono</option>
                    <option value="pendiente" <?php echo ($venta_editar && $venta_editar['notas'] == 'pendiente') ? 'selected' : ''; ?>>Pendent</option>
                    <option value="bizum_neus" <?php echo ($venta_editar && $venta_editar['notas'] == 'bizum_neus') ? 'selected' : ''; ?>>Bizum Neus</option>
                </select>
            </div>

            <div class="mb-6">
                <label for="empleado_id" class="block text-sm font-medium text-gray-700 mb-1">Empleat (opcional)</label>
                <select class="w-full border border-gray-300 rounded-lg p-2 focus:ring focus:ring-blue-200" name="empleado_id">
                    <option value="">Selecciona un empleat</option>
                    <?php foreach ($empleados as $empleado): ?>
                        <option value="<?php echo $empleado['id']; ?>" <?php echo ($venta_editar && $venta_editar['empleado_id'] == $empleado['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($empleado['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <h4 class="text-lg font-semibold mb-4">Detalls de la Venda</h4>

            <div id="detallesContainer">
                <?php if ($venta_editar && $detalles_editar): ?>
                    <?php foreach ($detalles_editar as $index => $detalle): ?>
                        <div class="detalle-row mb-4 bg-gray-50 border border-gray-200 p-4 rounded-xl shadow-sm">
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Producte</label>
                                    <select class="w-full border border-gray-300 rounded-lg p-2 producto-select focus:ring focus:ring-blue-200" name="detalles[<?php echo $index; ?>][producto_id]" required>
                                        <option value="">Selecciona un producte</option>
                                        <?php foreach ($productos as $producto): ?>
                                            <option value="<?php echo $producto['id']; ?>" data-precio="<?php echo $producto['precio']; ?>" 
                                                    <?php echo ($detalle['producto_id'] == $producto['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($producto['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantitat</label>
                                    <input type="number" class="w-full border border-gray-300 rounded-lg p-2 cantidad-input focus:ring focus:ring-blue-200" 
                                           name="detalles[<?php echo $index; ?>][cantidad]" value="<?php echo $detalle['cantidad']; ?>" min="1" required>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Preu Unitari (€)</label>
                                    <input type="number" step="0.01" class="w-full border border-gray-300 rounded-lg p-2 precio-unitario focus:ring focus:ring-blue-200" 
                                           name="detalles[<?php echo $index; ?>][precio_unitario]" 
                                           value="<?php echo number_format($detalle['precio_unitario'], 2, '.', ''); ?>" required>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Subtotal (€)</label>
                                    <input type="number" step="0.01" class="w-full border border-gray-300 rounded-lg p-2 subtotal bg-gray-100" 
                                           name="detalles[<?php echo $index; ?>][subtotal]" 
                                           value="<?php echo number_format($detalle['subtotal'], 2, '.', ''); ?>" readonly>
                                </div>

                                <div class="flex items-end">
                                    <button type="button" class="w-full bg-red-500 hover:bg-red-600 text-white text-sm py-2 px-4 rounded-lg remove-detalle">
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <button type="button" id="addDetalle" class="mb-4 mt-2 bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded-lg">
                Afegir Detall
            </button>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg">
                <?php echo $venta_editar ? 'Actualitzar' : 'Crear'; ?> Venda
            </button>
        </form>

        <!-- Taula de vendes -->
        <h3 class="mb-3">Llista de Vendes</h3>
        <table id="tablaVentas" class="display nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Data</th>
                    <th>Subtotal (€)</th>
                    <th>IVA (%)</th>
                    <th>IVA Import (€)</th>
                    <th>Total (€)</th>
                    <th>Mètode de Pagament</th>
                    <th>Empleat</th>
                    <th>Accions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ventas as $venta): ?>
                    <tr>
                        <td><?php echo $venta['id']; ?></td>
                        <td><?php echo htmlspecialchars($venta['nombre_cliente'] ?? 'Sense client'); ?></td>
                        <td><?php echo $venta['fecha']; ?></td>
                        <td><?php echo number_format($venta['subtotal'], 2, ',', '.'); ?></td>
                        <td><?php echo number_format($venta['iva_porcentaje'], 2, ',', '.'); ?></td>
                        <td><?php echo number_format($venta['iva_monto'], 2, ',', '.'); ?></td>
                        <td><?php echo number_format($venta['total'], 2, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($venta['notas'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($venta['nombre_empleado'] ?? 'Sense empleat'); ?></td>
                        <td class="text-center">
                            <a href="?tabla=ventas&accion=editar&id=<?php echo $venta['id']; ?>" class="text-orange-300 hover:text-orange-600 me-2" title="Editar">
    <i class="fas fa-edit"></i>
</a>
<a href="?tabla=ventas&accion=eliminar&id=<?php echo $venta['id']; ?>" class="text-red-500 hover:text-red-600 me-2" 
   onclick="return confirm('Segur que vols eliminar?')" title="Eliminar">
    <i class="fas fa-trash-alt"></i>
</a>
<a href="?tabla=ventas&accion=detalles&id=<?php echo $venta['id']; ?>" class="text-blue-500 hover:text-blue-600" title="Veure Detalls">
    <i class="fas fa-eye"></i>
</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Taula de detalls (mostrada al fer clic a "Veure Detalls") -->
        <?php if (isset($_GET['accion']) && $_GET['accion'] === 'detalles' && isset($_GET['id'])): ?>
            <?php
            $venta_id = (int)$_GET['id'];
            try {
                $sql_detalles = "SELECT d.*, p.nombre AS nombre_producto 
                                 FROM wp_contabilidad_detalles_venta d 
                                 LEFT JOIN wp_contabilidad_productos p ON d.producto_id = p.id 
                                 WHERE d.venta_id = ?";
                $stmt_detalles = $pdo->prepare($sql_detalles);
                $stmt_detalles->execute([$venta_id]);
                $detalles = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>Error al carregar detalls: " . $e->getMessage() . "</div>";
                $detalles = [];
            }
            ?>
            <h3 class="mb-3">Detalls de la Venda #<?php echo $venta_id; ?></h3>
            <table id="tablaDetallesVenta" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producte</th>
                        <th>Quantitat</th>
                        <th>Preu Unitari (€)</th>
                        <th>Subtotal (€)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detalles as $detalle): ?>
                        <tr>
                            <td><?php echo $detalle['id']; ?></td>
                            <td><?php echo htmlspecialchars($detalle['nombre_producto'] ?? 'Sense producte'); ?></td>
                            <td><?php echo $detalle['cantidad']; ?></td>
                            <td><?php echo number_format($detalle['precio_unitario'], 2, ',', '.'); ?></td>
                            <td><?php echo number_format($detalle['subtotal'], 2, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Plantilla per a nous detalls (usada per JavaScript) -->
        <script type="text/html" id="detalleTemplate">
            <div class="detalle-row mb-4 bg-gray-50 border border-gray-200 p-4 rounded-xl shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Producte</label>
                        <select class="w-full border border-gray-300 rounded-lg p-2 producto-select focus:ring focus:ring-blue-200" name="detalles[{index}][producto_id]" required>
                            <option value="">Selecciona un producte</option>
                            <?php foreach ($productos as $producto): ?>
                                <option value="<?php echo $producto['id']; ?>" data-precio="<?php echo $producto['precio']; ?>">
                                    <?php echo htmlspecialchars($producto['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantitat</label>
                        <input type="number" class="w-full border border-gray-300 rounded-lg p-2 cantidad-input focus:ring focus:ring-blue-200" 
                               name="detalles[{index}][cantidad]" min="1" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Preu Unitari (€)</label>
                        <input type="number" step="0.01" class="w-full border border-gray-300 rounded-lg p-2 precio-unitario focus:ring focus:ring-blue-200" 
                               name="detalles[{index}][precio_unitario]" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subtotal (€)</label>
                        <input type="number" step="0.01" class="w-full border border-gray-300 rounded-lg p-2 subtotal bg-gray-100" 
                               name="detalles[{index}][subtotal]" readonly>
                    </div>
                    <div class="flex items-end">
                        <button type="button" class="w-full bg-red-500 hover:bg-red-600 text-white text-sm py-2 px-4 rounded-lg remove-detalle">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </script>

        <script>
            // Inicialitzar Flatpickr per al camp de data
            $(document).ready(function() {
                flatpickr("#fecha", {
                    dateFormat: "d/m/Y", // Mostrar en DD/MM/YYYY
                    altInput: true,
                    altFormat: "d/m/Y", // Format visible per a l'usuari
                    defaultDate: "<?php echo $venta_editar ? date('d/m/Y', strtotime($venta_editar['fecha'])) : date('d/m/Y'); ?>",
                    locale: "ca", // Traducció al català
                    onChange: function(selectedDates, dateStr, instance) {
                        if (selectedDates.length > 0) {
                            const date = selectedDates[0];
                            const formattedDate = date.getFullYear() + '-' + 
                                String(date.getMonth() + 1).padStart(2, '0') + '-' + 
                                String(date.getDate()).padStart(2, '0');
                            instance.element.value = formattedDate;
                        }
                    }
                });
            });

            // JavaScript per gestionar detalls dinàmics
            document.addEventListener('DOMContentLoaded', function() {
                const addDetalleBtn = document.getElementById('addDetalle');
                const detallesContainer = document.getElementById('detallesContainer');
                const template = document.getElementById('detalleTemplate')?.innerHTML;
                let detalleIndex = <?php echo $venta_editar ? count($detalles_editar) : 0; ?>;

                // Comprovacions inicials
                console.log('addDetalleBtn:', addDetalleBtn);
                console.log('detallesContainer:', detallesContainer);
                console.log('template:', template);

                if (!addDetalleBtn || !detallesContainer || !template) {
                    console.error('Error: Algun element no es troba: addDetalleBtn, detallesContainer o template');
                    return;
                }

                // Afegir nou detall
                addDetalleBtn.addEventListener('click', function() {
                    const newDetalle = template.replace(/{index}/g, detalleIndex);
                    console.log('newDetalle:', newDetalle);
                    detallesContainer.insertAdjacentHTML('beforeend', newDetalle);
                    const newRow = detallesContainer.lastElementChild;
                    newRow.style.backgroundColor = '#E0FFFF'; // Per depurar visibilitat
                    detalleIndex++;
                    updateSubtotals();
                });

                // Eliminar detall
                detallesContainer.addEventListener('click', function(e) {
                    if (e.target.classList.contains('remove-detalle')) {
                        e.target.closest('.detalle-row').remove();
                        updateSubtotals();
                    }
                });

                // Actualitzar preu unitari i subtotal al canviar producte, quantitat o preu unitari
                detallesContainer.addEventListener('change', function(e) {
                    if (e.target.classList.contains('producto-select')) {
                        const row = e.target.closest('.detalle-row');
                        const select = e.target;
                        const precioUnitarioInput = row.querySelector('.precio-unitario');
                        const selectedOption = select.options[select.selectedIndex];
                        const precio = selectedOption ? parseFloat(selectedOption.getAttribute('data-precio')) || 0 : 0;
                        precioUnitarioInput.value = precio.toFixed(2);
                        updateSubtotals();
                    } else if (e.target.classList.contains('cantidad-input') || e.target.classList.contains('precio-unitario')) {
                        updateSubtotals();
                    }
                });

                function updateSubtotals() {
                    const rows = detallesContainer.querySelectorAll('.detalle-row');
                    rows.forEach(row => {
                        const cantidadInput = row.querySelector('.cantidad-input');
                        const precioUnitarioInput = row.querySelector('.precio-unitario');
                        const subtotalInput = row.querySelector('.subtotal');

                        const cantidad = parseInt(cantidadInput.value) || 0;
                        const precio = parseFloat(precioUnitarioInput.value) || 0;

                        subtotalInput.value = (precio * cantidad).toFixed(2);
                    });
                }

                // Inicialitzar subtotals en càrrega
                updateSubtotals();
            });
        </script>
    </div>
</main>
<?php include 'footer.php';?>