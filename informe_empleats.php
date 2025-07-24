<?php include 'head.php';?>

<!-- Content -->
<main class="max-w-7xl mx-auto p-6">
    <h1 class="mb-4 text-4xl font-extrabold text-center text-gray-900 md:text-5xl">
        Gestió <span class="inline-block py-2 text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-purple-500">Informe Empleats</span>
    </h1>
    <script>
        document.title = document.querySelector('h1').textContent;
    </script>

    <?php
    // Consulta per obtenir la llista d'empleats únics
    try {
        $sql_empleados = "SELECT DISTINCT e.id, e.nombre 
                          FROM wp_contabilidad_empleados e
                          JOIN wp_contabilidad_ventas v ON e.id = v.empleado_id
                          ORDER BY e.nombre";
        $stmt_empleados = $pdo->query($sql_empleados);
        $empleados = $stmt_empleados->fetchAll(PDO::FETCH_ASSOC);

        // Empleat seleccionat (per defecte, el primer empleat)
        $empleado_seleccionado = isset($_GET['empleado_id']) ? (int)$_GET['empleado_id'] : (isset($empleados[0]['id']) ? $empleados[0]['id'] : null);
        $empleado_nombre = '';
        foreach ($empleados as $emp) {
            if ($emp['id'] == $empleado_seleccionado) {
                $empleado_nombre = $emp['nombre'];
                break;
            }
        }

        // Consulta per als 20 productes més venuts per l'empleat seleccionat
        $sql_productes = "SELECT 
                            p.nombre AS producto_nombre,
                            SUM(dv.subtotal) AS total_import
                          FROM wp_contabilidad_detalles_venta dv
                          JOIN wp_contabilidad_ventas v ON dv.venta_id = v.id
                          JOIN wp_contabilidad_productos p ON dv.producto_id = p.id
                          WHERE v.empleado_id = ?
                          GROUP BY p.id, p.nombre
                          ORDER BY total_import DESC
                          LIMIT 20";
        $stmt_productes = $pdo->prepare($sql_productes);
        $stmt_productes->execute([$empleado_seleccionado]);
        $productes_mes_venuts = $stmt_productes->fetchAll(PDO::FETCH_ASSOC);

        // Preparar dades per al gràfic de barres
        $noms_productes = [];
        $imports = [];
        foreach ($productes_mes_venuts as $producte) {
            $noms_productes[] = $producte['producto_nombre'];
            $imports[] = floatval($producte['total_import']);
        }

    } catch (PDOException $e) {
        echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4'>Error al carregar les dades: " . $e->getMessage() . "</div>";
        $empleados = [];
        $productes_mes_venuts = [];
        $noms_productes = [];
        $imports = [];
        $empleado_seleccionado = null;
        $empleado_nombre = '';
    }
    ?>

    <!-- Contenidor del gràfic de barres -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-semibold text-gray-800">20 Productes Més Venuts per Empleat<?php echo $empleado_nombre ? ' (' . htmlspecialchars($empleado_nombre) . ')' : ''; ?></h2>
            <div>
    <label for="empleado_select" class="block text-sm font-medium text-gray-700 mb-1">Seleccionar Empleat</label>
    <select id="empleado_select" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="window.location.href='?empleado_id=' + this.value">
        <?php foreach ($empleados as $empleado): ?>
            <option value="<?php echo $empleado['id']; ?>" <?php echo $empleado['id'] == $empleado_seleccionado ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($empleado['nombre']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
        </div>
        <div id="productesChart" class="w-full h-[500px]"></div>
    </div>

    <!-- Taula dels 20 productes més venuts -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Detalls dels 20 Productes Més Venuts<?php echo $empleado_nombre ? ' (' . htmlspecialchars($empleado_nombre) . ')' : ''; ?></h2>
        <table id="tablaProductes" class="display nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>Producte</th>
                    <th>Import Total (€)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productes_mes_venuts as $producte): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($producte['producto_nombre']); ?></td>
                        <td><?php echo number_format($producte['total_import'], 2, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Highcharts Script -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Gràfic de barres (20 productes més venuts per empleat)
            Highcharts.chart('productesChart', {
                chart: {
                    type: 'column'
                },
                title: {
                    text: '20 Productes Més Venuts per Empleat<?php echo $empleado_nombre ? ' (' . htmlspecialchars($empleado_nombre) . ')' : ''; ?>',
                    style: {
                        fontFamily: 'Instrument Sans, sans-serif',
                        fontSize: '1.5rem',
                        fontWeight: '600',
                        color: '#1f2937' // gray-800
                    }
                },
                xAxis: {
                    categories: <?php echo json_encode($noms_productes); ?>,
                    title: {
                        text: 'Producte',
                        style: {
                            color: '#374151', // gray-700
                            fontFamily: 'Instrument Sans, sans-serif'
                        }
                    },
                    labels: {
                        rotation: -45,
                        style: {
                            color: '#374151', // gray-700
                            fontFamily: 'Instrument Sans, sans-serif'
                        }
                    }
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Import Total (€)',
                        style: {
                            color: '#374151', // gray-700
                            fontFamily: 'Instrument Sans, sans-serif'
                        }
                    },
                    labels: {
                        style: {
                            color: '#374151', // gray-700
                            fontFamily: 'Instrument Sans, sans-serif'
                        },
                        formatter: function () {
                            return Highcharts.numberFormat(this.value, 2) + ' €';
                        }
                    }
                },
                legend: {
                    enabled: false
                },
                tooltip: {
                    pointFormat: '<span style="font-family: Instrument Sans, sans-serif">Import Total: <b>{point.y:.2f} €</b></span>',
                    backgroundColor: '#ffffff', // white
                    borderColor: '#e5e7eb', // gray-200
                    style: {
                        fontFamily: 'Instrument Sans, sans-serif'
                    }
                },
                plotOptions: {
                    column: {
                        color: '#2563eb', // blue-600
                        borderRadius: 4,
                        pointPadding: 0.1,
                        groupPadding: 0.2
                    }
                },
                series: [{
                    name: 'Import Total',
                    data: <?php echo json_encode($imports); ?>
                }],
                responsive: {
                    rules: [{
                        condition: {
                            maxWidth: 500
                        },
                        chartOptions: {
                            xAxis: {
                                labels: {
                                    rotation: -90
                                }
                            }
                        }
                    }]
                }
            });

            // Inicialitzar DataTables per a la taula de productes
            $('#tablaProductes').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ca.json'
                },
                order: [[1, 'desc']],
                columnDefs: [
                    { orderable: true, targets: '_all' }
                ],
                searching: true,
                paging: true
            });
        });
    </script>

</main>

<?php include 'footer.php';?>