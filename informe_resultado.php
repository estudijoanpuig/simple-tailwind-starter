<?php include 'head.php';?>

<!-- Content -->
<main class="max-w-7xl mx-auto p-6">
    <h1 class="mb-4 text-4xl font-extrabold text-center text-gray-900 md:text-5xl">
        Gestió <span class="inline-block py-2 text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-purple-500">Informe Resultats</span>
    </h1>
    <script>
        document.title = document.querySelector('h1').textContent;
    </script>

<?php
try {
    $any_actual = date('Y'); // 2025

    // Obtenir dades de vendes per trimestre
    $sql_ventas = "SELECT 
                QUARTER(fecha) AS quarter,
                COALESCE(SUM(total), 0) AS total_ventas
            FROM wp_contabilidad_ventas
            WHERE YEAR(fecha) = ?
            GROUP BY QUARTER(fecha)
            ORDER BY quarter";
    $stmt_ventas = $pdo->prepare($sql_ventas);
    $stmt_ventas->execute([$any_actual]);
    $ventas = $stmt_ventas->fetchAll(PDO::FETCH_ASSOC);
    unset($stmt_ventas);

    // Obtenir dades de compres per trimestre
    $sql_compras = "SELECT 
                QUARTER(fecha) AS quarter,
                COALESCE(SUM(total), 0) AS total_compras
            FROM wp_contabilidad_compras
            WHERE YEAR(fecha) = ?
            GROUP BY QUARTER(fecha)
            ORDER BY quarter";
    $stmt_compras = $pdo->prepare($sql_compras);
    $stmt_compras->execute([$any_actual]);
    $compras = $stmt_compras->fetchAll(PDO::FETCH_ASSOC);
    unset($stmt_compras);

    // Combinar dades de vendes i compres
    $resultados = [];
    for ($quarter = 1; $quarter <= 4; $quarter++) {
        $resultados[$quarter] = [
            'quarter' => $quarter,
            'total_ventas' => 0,
            'total_compras' => 0,
            'resultado' => 0
        ];
    }

    // Incorporar vendes
    foreach ($ventas as $venta) {
        $quarter = $venta['quarter'];
        $resultados[$quarter]['total_ventas'] = floatval($venta['total_ventas']);
    }

    // Incorporar compres
    foreach ($compras as $compra) {
        $quarter = $compra['quarter'];
        $resultados[$quarter]['total_compras'] = floatval($compra['total_compras']);
    }

    // Calcular resultat (ventes - compres)
    foreach ($resultados as &$resultado) {
        $resultado['resultado'] = $resultado['total_ventas'] - $resultado['total_compras'];
    }

    // Preparar dades per als gràfics
    $quarters = array_keys($resultados);
    $ventas_data = array_column($resultados, 'total_ventas');
    $compras_data = array_column($resultados, 'total_compras');
    $resultados_data = array_column($resultados, 'resultado');

    // Calcular resultat acumulat per al gràfic de línies
    $resultat_acumulat = [];
    $acumulat = 0;
    foreach ($resultados_data as $index => $resultado) {
        $acumulat += $resultado;
        $resultat_acumulat[] = $acumulat;
    }

    // Verificar si hi ha dades
    $has_data = false;
    foreach ($resultados as $resultado) {
        if ($resultado['total_ventas'] > 0 || $resultado['total_compras'] > 0) {
            $has_data = true;
            break;
        }
    }
} catch (PDOException $e) {
    echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4'>Error al carregar dades: " . htmlspecialchars($e->getMessage()) . "</div>";
    $quarters = [1, 2, 3, 4];
    $ventas_data = [0, 0, 0, 0];
    $compras_data = [0, 0, 0, 0];
    $resultados_data = [0, 0, 0, 0];
    $resultat_acumulat = [0, 0, 0, 0];
    $has_data = false;
}
?>

<!-- Missatge si no hi ha dades -->
<?php if (!$has_data): ?>
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4 rounded-md">
        No s'han trobat dades de vendes o compres per a l'any <?php echo $any_actual; ?>. Verifiqueu els registres a la base de dades.
    </div>
<?php endif; ?>

<!-- Secció de Gràfics -->
<div class="grid grid-cols-1 gap-6 mb-8">
    <!-- Gràfic de Barres per Trimestre -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Resultats per Trimestre (<?php echo htmlspecialchars($any_actual); ?>)</h2>
        <div id="grafic_barras" class="h-[500px]"></div>
    </div>

    <!-- Gràfic de Línies Acumulades -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Evolució Acumulada del Resultat (<?php echo htmlspecialchars($any_actual); ?>)</h2>
        <div id="grafic_lineas" class="h-[400px]"></div>
    </div>
</div>

<!-- Taula de Resultats amb DataTables -->
<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Detall de Resultats</h2>
    <table id="tablaResultados" class="w-full text-sm text-left text-gray-500">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
            <tr>
                <th class="px-6 py-3">Trimestre</th>
                <th class="px-6 py-3">Total Vendes (€)</th>
                <th class="px-6 py-3">Total Compres (€)</th>
                <th class="px-6 py-3">Resultat (€)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($resultados as $resultado): ?>
                <tr class="bg-white border-b">
                    <td class="px-6 py-4">Tr<?php echo htmlspecialchars($resultado['quarter']); ?></td>
                    <td class="px-6 py-4"><?php echo number_format($resultado['total_ventas'], 2, ',', '.'); ?></td>
                    <td class="px-6 py-4"><?php echo number_format($resultado['total_compras'], 2, ',', '.'); ?></td>
                    <td class="px-6 py-4"><?php echo number_format($resultado['resultado'], 2, ',', '.'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>



<!-- Highcharts Script -->
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Depuració: Mostrar dades al console.log
        console.log('Dades per als gràfics:', {
            quarters: <?php echo json_encode(array_map(function($q) { return 'Tr' . $q; }, $quarters)); ?>,
            ventas_data: <?php echo json_encode($ventas_data); ?>,
            compras_data: <?php echo json_encode($compras_data); ?>,
            resultados_data: <?php echo json_encode($resultados_data); ?>,
            resultado_acumulado: <?php echo json_encode($resultat_acumulat); ?>
        });

        // Gràfic de Barres per Trimestre
        Highcharts.chart('grafic_barras', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'Resultats per Trimestre (<?php echo htmlspecialchars($any_actual); ?>)',
                style: {
                    fontFamily: 'Instrument Sans, sans-serif',
                    fontSize: '1.5rem',
                    fontWeight: '600',
                    color: '#1f2937'
                }
            },
            xAxis: {
                categories: <?php echo json_encode(array_map(function($q) { return 'Tr' . $q; }, $quarters)); ?>,
                title: {
                    text: 'Trimestre',
                    style: {
                        color: '#374151',
                        fontFamily: 'Instrument Sans, sans-serif'
                    }
                },
                labels: {
                    style: {
                        color: '#374151',
                        fontFamily: 'Instrument Sans, sans-serif'
                    }
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Quantitat (€)',
                    style: {
                        color: '#374151',
                        fontFamily: 'Instrument Sans, sans-serif'
                    }
                },
                labels: {
                    style: {
                        color: '#374151',
                        fontFamily: 'Instrument Sans, sans-serif'
                    },
                    formatter: function () {
                        return Highcharts.numberFormat(this.value, 2) + ' €';
                    }
                }
            },
            legend: {
                align: 'center',
                verticalAlign: 'bottom',
                backgroundColor: '#f9fafb',
                borderColor: '#e5e7eb',
                borderWidth: 1,
                itemStyle: {
                    fontFamily: 'Instrument Sans, sans-serif',
                    color: '#374151'
                }
            },
            tooltip: {
                headerFormat: '<span style="font-size: 1rem; font-family: Instrument Sans, sans-serif">{point.key}</span><br/>',
                pointFormat: '<span style="color:{series.color}; font-family: Instrument Sans, sans-serif">{series.name}</span>: <b>{point.y:.2f} €</b><br/>',
                shared: true,
                backgroundColor: '#ffffff',
                borderColor: '#e5e7eb',
                style: {
                    fontFamily: 'Instrument Sans, sans-serif'
                }
            },
            plotOptions: {
                column: {
                    stacking: 'normal',
                    borderRadius: 4,
                    pointPadding: 0.1,
                    groupPadding: 0.2
                }
            },
            series: [{
                name: 'Vendes',
                data: <?php echo json_encode($ventas_data); ?>,
                color: '#22c55e'
            }, {
                name: 'Compres',
                data: <?php echo json_encode($compras_data); ?>,
                color: '#ef4444'
            }, {
                name: 'Resultat',
                data: <?php echo json_encode($resultados_data); ?>,
                color: '#2563eb'
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
                        },
                        yAxis: {
                            title: {
                                text: ''
                            }
                        }
                    }
                }]
            }
        });

        // Gràfic de Línies Acumulades
        Highcharts.chart('grafic_lineas', {
            chart: {
                type: 'line'
            },
            title: {
                text: 'Evolució Acumulada del Resultat (<?php echo htmlspecialchars($any_actual); ?>)',
                style: {
                    fontFamily: 'Instrument Sans, sans-serif',
                    fontSize: '1.5rem',
                    fontWeight: '600',
                    color: '#1f2937'
                }
            },
            xAxis: {
                categories: <?php echo json_encode(array_map(function($q) { return 'Tr' . $q; }, $quarters)); ?>,
                title: {
                    text: 'Trimestre',
                    style: {
                        color: '#374151',
                        fontFamily: 'Instrument Sans, sans-serif'
                    }
                },
                labels: {
                    style: {
                        color: '#374151',
                        fontFamily: 'Instrument Sans, sans-serif'
                    }
                }
            },
            yAxis: {
                title: {
                    text: 'Resultat Acumulat (€)',
                    style: {
                        color: '#374151',
                        fontFamily: 'Instrument Sans, sans-serif'
                    }
                },
                labels: {
                    style: {
                        color: '#374151',
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
                pointFormat: '<span style="color:{point.color}; font-family: Instrument Sans, sans-serif">{point.key}</span>: <b>{point.y:.2f} €</b>',
                backgroundColor: '#ffffff',
                borderColor: '#e5e7eb',
                style: {
                    fontFamily: 'Instrument Sans, sans-serif'
                }
            },
            series: [{
                name: 'Resultat Acumulat',
                data: <?php echo json_encode($resultat_acumulat); ?>,
                color: '#2563eb',
                marker: {
                    enabled: true
                }
            }],
            responsive: {
                rules: [{
                    condition: {
                        maxWidth: 500
                    },
                    chartOptions: {
                        yAxis: {
                            title: {
                                text: ''
                            }
                        }
                    }
                }]
            }
        });

        // Inicialitzar DataTables
        $('#tablaResultados').DataTable({
            responsive: true,
            paging: true,
            searching: true,
            ordering: true,
            info: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ca.json'
            }
        });
    });
</script>

<?php
// Alliberar memòria final
unset($quarters, $ventas_data, $compras_data, $resultados_data, $resultados, $resultat_acumulat);
?>
<?php include 'footer.php';?>
</main>