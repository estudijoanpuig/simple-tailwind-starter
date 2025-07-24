<?php include 'head.php';?>

<!-- Content -->
<main class="max-w-7xl mx-auto p-6">
    <h1 class="mb-4 text-4xl font-extrabold text-center text-gray-900 md:text-5xl">
        Gestió <span class="inline-block py-2 text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-purple-500">Informe Productes</span>
    </h1>
    <script>
        document.title = document.querySelector('h1').textContent;
    </script>

    <?php
    // Consulta per als 20 productes més venuts per import total
    try {
        $sql_productes = "SELECT 
                            p.nombre AS producto_nombre,
                            SUM(dv.subtotal) AS total_import
                          FROM wp_contabilidad_detalles_venta dv
                          JOIN wp_contabilidad_productos p ON dv.producto_id = p.id
                          GROUP BY p.id, p.nombre
                          ORDER BY total_import DESC
                          LIMIT 20";
        $stmt_productes = $pdo->query($sql_productes);
        $productes_mes_venuts = $stmt_productes->fetchAll(PDO::FETCH_ASSOC);

        // Preparar dades per al gràfic de barres
        $noms_productes = [];
        $imports = [];
        foreach ($productes_mes_venuts as $producte) {
            $noms_productes[] = $producte['producto_nombre'];
            $imports[] = floatval($producte['total_import']);
        }

        // Consulta per a les vendes per categories
        $sql_categories = "SELECT 
                              c.nombre_categoria AS categoria_nombre,
                              SUM(dv.subtotal) AS total_vendes
                           FROM wp_contabilidad_detalles_venta dv
                           JOIN wp_contabilidad_productos p ON dv.producto_id = p.id
                           JOIN wp_contabilidad_categoria_productos c ON p.id_categoria_producto = c.id
                           GROUP BY c.id, c.nombre_categoria
                           ORDER BY total_vendes DESC";
        $stmt_categories = $pdo->query($sql_categories);
        $vendes_per_categoria = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);

        // Preparar dades per al gràfic circular
        $data_pie = [];
        $colores = ['#2563eb', '#22c55e', '#ef4444', '#8b5cf6', '#f59e0b', '#ec4899', '#6b7280', '#14b8a6', '#f97316', '#4b5563']; // Colors de Tailwind
        foreach ($vendes_per_categoria as $index => $categoria) {
            $data_pie[] = [
                'name' => $categoria['categoria_nombre'],
                'y' => floatval($categoria['total_vendes']),
                'color' => $colores[$index % count($colores)]
            ];
        }

    } catch (PDOException $e) {
        echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4'>Error al carregar les dades: " . $e->getMessage() . "</div>";
        $productes_mes_venuts = [];
        $noms_productes = [];
        $imports = [];
        $vendes_per_categoria = [];
        $data_pie = [];
    }
    ?>

    <!-- Contenidor del gràfic de barres (20 productes més venuts per import) -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">20 Productes Més Venuts (per Import Total)</h2>
        <div id="productesChart" class="w-full h-[500px]"></div>
    </div>

    <!-- Taula dels 20 productes més venuts -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Detalls dels 20 Productes Més Venuts (per Import Total)</h2>
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

    <!-- Contenidor del gràfic circular (vendes per categories) -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Vendes per Categories</h2>
        <div id="categoriesChart" class="w-full h-[400px]"></div>
    </div>

    <!-- Taula de vendes per categories -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Detalls de Vendes per Categories</h2>
        <table id="tablaCategories" class="display nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>Categoria</th>
                    <th>Total Vendes (€)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vendes_per_categoria as $categoria): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($categoria['categoria_nombre']); ?></td>
                        <td><?php echo number_format($categoria['total_vendes'], 2, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Highcharts Script -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Gràfic de barres (20 productes més venuts per import)
            Highcharts.chart('productesChart', {
                chart: {
                    type: 'column'
                },
                title: {
                    text: '20 Productes Més Venuts (per Import Total)',
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

            // Gràfic circular (vendes per categories)
            Highcharts.chart('categoriesChart', {
                chart: {
                    type: 'pie'
                },
                title: {
                    text: 'Vendes per Categories',
                    style: {
                        fontFamily: 'Instrument Sans, sans-serif',
                        fontSize: '1.5rem',
                        fontWeight: '600',
                        color: '#1f2937' // gray-800
                    }
                },
                tooltip: {
                    pointFormat: '<span style="color:{point.color}; font-family: Instrument Sans, sans-serif">{point.name}</span>: <b>{point.y:.2f} €</b> ({point.percentage:.1f}%)',
                    backgroundColor: '#ffffff', // white
                    borderColor: '#e5e7eb', // gray-200
                    style: {
                        fontFamily: 'Instrument Sans, sans-serif'
                    }
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.y:.2f} €',
                            style: {
                                fontFamily: 'Instrument Sans, sans-serif',
                                color: '#374151', // gray-700
                                fontWeight: 'normal'
                            }
                        },
                        showInLegend: true
                    }
                },
                legend: {
                    align: 'center',
                    verticalAlign: 'bottom',
                    backgroundColor: '#f9fafb', // gray-50
                    borderColor: '#e5e7eb', // gray-200
                    borderWidth: 1,
                    itemStyle: {
                        fontFamily: 'Instrument Sans, sans-serif',
                        color: '#374151' // gray-700
                    }
                },
                series: [{
                    name: 'Vendes',
                    data: <?php echo json_encode($data_pie); ?>,
                    size: '80%',
                    innerSize: '50%' // Donut style
                }],
                responsive: {
                    rules: [{
                        condition: {
                            maxWidth: 500
                        },
                        chartOptions: {
                            legend: {
                                align: 'center',
                                verticalAlign: 'bottom',
                                layout: 'horizontal'
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

            // Inicialitzar DataTables per a la taula de categories
            $('#tablaCategories').DataTable({
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