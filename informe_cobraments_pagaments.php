<?php include 'head.php'; ?>

<!-- Content -->
<main class="max-w-7xl mx-auto p-6">
    <h1 class="mb-4 text-4xl font-extrabold text-center text-gray-900 md:text-5xl">
        Gestió <span class="inline-block py-2 text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-purple-500">Informe Metodes de Cobraments/Pagaments</span>
    </h1>
    <script>
        document.title = document.querySelector('h1').textContent;
    </script>

    <?php
    try {
        // Consulta per obtenir els sistemes de pagament a wp_contabilidad_ventas amb sumes
        $sql_notas_vendes = "
            SELECT 
                notas AS metode,
                SUM(subtotal) AS suma_total
            FROM wp_contabilidad_ventas
            WHERE notas IS NOT NULL AND notas != ''
            GROUP BY notas
            ORDER BY notas
        ";
        $stmt_notas_vendes = $pdo->query($sql_notas_vendes);
        $notas_vendes = $stmt_notas_vendes->fetchAll(PDO::FETCH_ASSOC);

        // Consulta per obtenir els sistemes de pagament per tot l'any 2025 (ingressos)
        $sql_ingressos_anual = "
            SELECT 
                notas COLLATE utf8mb4_unicode_ci AS metode,
                SUM(subtotal) AS total
            FROM wp_contabilidad_ventas
            WHERE YEAR(fecha) = 2025 AND notas IS NOT NULL AND notas != ''
            GROUP BY metode
            ORDER BY total DESC
        ";
        $stmt_ingressos_anual = $pdo->query($sql_ingressos_anual);
        $ingressos_anual_data = $stmt_ingressos_anual->fetchAll(PDO::FETCH_ASSOC);

        // Processar dades per al gràfic d'ingressos
        $ingressos_anual = [];
        $total_ingressos = 0;
        $colores = ['#2563eb', '#22c55e', '#ef4444', '#8b5cf6', '#f59e0b', '#ec4899', '#3b82f6', '#16a34a', '#dc2626', '#7c3aed', '#d97706', '#db2777']; // Colors de Tailwind
        $index = 0;
        foreach ($ingressos_anual_data as $row) {
            $metode = $row['metode'] . ' (Ingressos)';
            $total = floatval($row['total']);
            $ingressos_anual[] = [
                'name' => $metode,
                'y' => $total,
                'color' => $colores[$index % count($colores)]
            ];
            $total_ingressos += $total;
            $index++;
        }

        // Consulta per obtenir els sistemes de pagament per tot l'any 2025 (despeses)
        $sql_despeses_anual = "
            SELECT 
                notas COLLATE utf8mb4_unicode_ci AS metode,
                SUM(subtotal) AS total
            FROM wp_contabilidad_compras
            WHERE YEAR(fecha) = 2025 AND notas IS NOT NULL AND notas != ''
            GROUP BY metode
            ORDER BY total DESC
        ";
        $stmt_despeses_anual = $pdo->query($sql_despeses_anual);
        $despeses_anual_data = $stmt_despeses_anual->fetchAll(PDO::FETCH_ASSOC);

        // Processar dades per al gràfic de despeses
        $despeses_anual = [];
        $total_despeses = 0;
        $index = 0;
        foreach ($despeses_anual_data as $row) {
            $metode = $row['metode'] . ' (Despeses)';
            $total = floatval($row['total']);
            $despeses_anual[] = [
                'name' => $metode,
                'y' => $total,
                'color' => $colores[$index % count($colores)]
            ];
            $total_despeses += $total;
            $index++;
        }
    } catch (PDOException $e) {
        echo "<div class='text-red-500 text-center'>Error en la consulta: " . htmlspecialchars($e->getMessage()) . "</div>";
        exit;
    }
    ?>

    <!-- Secció per mostrar els resultats de la consulta de test (sistemes de pagament a vendes amb sumes) -->
    <div class="mb-8">
        <h2 class="text-2xl font-semibold text-gray-700 mb-4 text-center">Sistemes de Pagament a wp_contabilidad_ventas (camp notas amb sumes)</h2>
        <div class="overflow-x-auto">
            <table id="notas-table" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metode (notas)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Suma Total (€)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($notas_vendes)): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-center" colspan="2">No hi ha sistemes de pagament registrats.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($notas_vendes as $nota): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($nota['metode']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo number_format($nota['suma_total'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Secció del gràfic d'ingressos (vendes) -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-semibold text-gray-800">Distribució de Pagaments - Ingressos 2025</h2>
        </div>
        <?php if ($total_ingressos > 0): ?>
            <div id="ingressos-chart" class="w-full h-[400px]"></div>
        <?php else: ?>
            <p class="text-center text-gray-500">No hi ha dades per mostrar el gràfic d'ingressos.</p>
        <?php endif; ?>
    </div>

    <!-- Secció del gràfic de despeses (compres) -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-semibold text-gray-800">Distribució de Pagaments - Despeses 2025</h2>
        </div>
        <?php if ($total_despeses > 0): ?>
            <div id="despeses-chart" class="w-full h-[400px]"></div>
        <?php else: ?>
            <p class="text-center text-gray-500">No hi ha dades per mostrar el gràfic de despeses.</p>
        <?php endif; ?>
    </div>

    <!-- Script per als gràfics de pastís -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Gràfic per ingressos
            <?php if ($total_ingressos > 0): ?>
                Highcharts.chart('ingressos-chart', {
                    chart: {
                        type: 'pie'
                    },
                    title: {
                        text: 'Distribució d\'Ingressos - Any 2025'
                    },
                    tooltip: {
                        pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f} €</b> ({point.percentage:.1f}%)'
                    },
                    accessibility: {
                        point: {
                            valueSuffix: '%'
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
                            showInLegend: true,
                            size: '80%',
                            innerSize: '50%' // Donut style
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
                        name: 'Ingressos',
                        data: <?php echo json_encode($ingressos_anual, JSON_NUMERIC_CHECK); ?>
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
            <?php endif; ?>

            // Gràfic per despeses
            <?php if ($total_despeses > 0): ?>
                Highcharts.chart('despeses-chart', {
                    chart: {
                        type: 'pie'
                    },
                    title: {
                        text: 'Distribució de Despeses - Any 2025'
                    },
                    tooltip: {
                        pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f} €</b> ({point.percentage:.1f}%)'
                    },
                    accessibility: {
                        point: {
                            valueSuffix: '%'
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
                            showInLegend: true,
                            size: '80%',
                            innerSize: '50%' // Donut style
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
                        name: 'Despeses',
                        data: <?php echo json_encode($despeses_anual, JSON_NUMERIC_CHECK); ?>
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
            <?php endif; ?>
        });
    </script>

    <!-- Incloure jQuery i DataTables per a la taula -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#notas-table').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/ca.json'
                },
                order: [[0, 'asc']],
                pageLength: 10
            });
        });
    </script>

    <?php
    // Alliberar memòria final
    unset($notas_vendes, $pagaments_anual_data, $ingressos_anual, $despeses_anual);
    ?>
</main>
<?php include 'footer.php'; ?>