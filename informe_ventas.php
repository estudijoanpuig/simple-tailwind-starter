<?php include 'head.php';?>

<!-- Content -->
<main class="max-w-7xl mx-auto p-6">
    <h1 class="mb-4 text-4xl font-extrabold text-center text-gray-900 md:text-5xl">
        Gestió <span class="inline-block py-2 text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-purple-500">Informe Vendes</span>
    </h1>
    <script>
        document.title = document.querySelector('h1').textContent;
    </script>

    <?php
    // Consulta per obtenir les vendes per empleat i mes (gràfic de columnes)
    try {
        $sql = "SELECT 
                    e.nombre AS empleado_nombre,
                    YEAR(v.fecha) AS anyo,
                    MONTH(v.fecha) AS mes,
                    SUM(v.total) AS total_vendas
                FROM wp_contabilidad_ventas v
                JOIN wp_contabilidad_empleados e ON v.empleado_id = e.id
                GROUP BY e.id, e.nombre, YEAR(v.fecha), MONTH(v.fecha)
                ORDER BY anyo, mes, e.nombre";
        $stmt = $pdo->query($sql);
        $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Preparar dades per al gràfic de columnes
        $meses = [];
        $empleados = [];
        $data_por_empleado = [];

        foreach ($ventas as $venta) {
            $mes_anyo = sprintf('%d-%02d', $venta['anyo'], $venta['mes']);
            $empleado = $venta['empleado_nombre'];
            $total = floatval($venta['total_vendas']);

            if (!in_array($mes_anyo, $meses)) {
                $meses[] = $mes_anyo;
            }

            if (!in_array($empleado, $empleados)) {
                $empleados[] = $empleado;
                $data_por_empleado[$empleado] = [];
            }

            $data_por_empleado[$empleado][$mes_anyo] = $total;
        }

        sort($meses);

        $series = [];
        $colores = ['#2563eb', '#22c55e', '#ef4444', '#8b5cf6', '#f59e0b', '#ec4899']; // Colors de Tailwind
        foreach ($empleados as $index => $empleado) {
            $data = [];
            foreach ($meses as $mes) {
                $data[] = isset($data_por_empleado[$empleado][$mes]) ? $data_por_empleado[$empleado][$mes] : 0;
            }
            $series[] = [
                'name' => $empleado,
                'data' => $data,
                'color' => $colores[$index % count($colores)]
            ];
        }

        // Consulta per obtenir els anys únics
        $sql_anys = "SELECT DISTINCT YEAR(fecha) AS anyo FROM wp_contabilidad_ventas ORDER BY anyo";
        $stmt_anys = $pdo->query($sql_anys);
        $anys = $stmt_anys->fetchAll(PDO::FETCH_COLUMN);

        // Any seleccionat (per defecte, l'últim any)
        $anyo_seleccionado = isset($_GET['anyo']) && in_array($_GET['anyo'], $anys) ? $_GET['anyo'] : end($anys);

        // Consulta per obtenir les vendes per empleat i any (gràfic circular)
        $sql_pie = "SELECT 
                        e.nombre AS empleado_nombre,
                        SUM(v.total) AS total_vendas
                    FROM wp_contabilidad_ventas v
                    JOIN wp_contabilidad_empleados e ON v.empleado_id = e.id
                    WHERE YEAR(v.fecha) = ?
                    GROUP BY e.id, e.nombre
                    ORDER BY e.nombre";
        $stmt_pie = $pdo->prepare($sql_pie);
        $stmt_pie->execute([$anyo_seleccionado]);
        $ventas_por_empleat = $stmt_pie->fetchAll(PDO::FETCH_ASSOC);

        // Preparar dades per al gràfic circular
        $data_pie = [];
        foreach ($ventas_por_empleat as $index => $venta) {
            $data_pie[] = [
                'name' => $venta['empleado_nombre'],
                'y' => floatval($venta['total_vendas']),
                'color' => $colores[$index % count($colores)]
            ];
        }

    } catch (PDOException $e) {
        echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4'>Error al carregar les dades: " . $e->getMessage() . "</div>";
        $meses = [];
        $series = [];
        $anys = [];
        $data_pie = [];
        $anyo_seleccionado = null;
    }
    ?>

    <!-- Contenidor del gràfic de columnes -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Vendes per Empleat per Mes</h2>
        <div id="ventasChart" class="w-full h-[500px]"></div>
    </div>

    <!-- Contenidor del gràfic circular -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-semibold text-gray-800">Vendes Anuals per Empleat</h2>
            <div>
                <label for="anyo_select" class="block text-sm font-medium text-gray-700 mb-1">Seleccionar Any</label>
                <select id="anyo_select" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="window.location.href='?anyo=' + this.value">
                    <?php foreach ($anys as $anyo): ?>
                        <option value="<?php echo $anyo; ?>" <?php echo $anyo == $anyo_seleccionado ? 'selected' : ''; ?>>
                            <?php echo $anyo; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div id="ventasPieChart" class="w-full h-[400px]"></div>
    </div>

    <!-- Taula de vendes -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Detalls de Vendes</h2>
        <table id="tablaVendas" class="display nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>Empleat</th>
                    <th>Any</th>
                    <th>Mes</th>
                    <th>Total Vendes (€)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ventas as $venta): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($venta['empleado_nombre']); ?></td>
                        <td><?php echo $venta['anyo']; ?></td>
                        <td><?php echo sprintf('%02d', $venta['mes']); ?></td>
                        <td><?php echo number_format($venta['total_vendas'], 2, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Highcharts Script -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Gràfic de columnes apilades
            Highcharts.chart('ventasChart', {
                chart: {
                    type: 'column'
                },
                title: {
                    text: 'Vendes per Empleat per Mes',
                    style: {
                        fontFamily: 'Instrument Sans, sans-serif',
                        fontSize: '1.5rem',
                        fontWeight: '600',
                        color: '#1f2937' // gray-800
                    }
                },
                xAxis: {
                    categories: <?php echo json_encode($meses); ?>,
                    title: {
                        text: 'Mes-Any',
                        style: {
                            color: '#374151', // gray-700
                            fontFamily: 'Instrument Sans, sans-serif'
                        }
                    },
                    labels: {
                        style: {
                            color: '#374151', // gray-700
                            fontFamily: 'Instrument Sans, sans-serif'
                        }
                    }
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Total Vendes (€)',
                        style: {
                            color: '#374151', // gray-700
                            fontFamily: 'Instrument Sans, sans-serif'
                        }
                    },
                    labels: {
                        style: {
                            color: '#374151', // gray-700
                            fontFamily: 'Instrument Sans, sans-serif'
                        }
                    },
                    stackLabels: {
                        enabled: true,
                        style: {
                            fontWeight: 'bold',
                            color: '#1f2937', // gray-800
                            fontFamily: 'Instrument Sans, sans-serif'
                        },
                        formatter: function () {
                            return Highcharts.numberFormat(this.total, 2) + ' €';
                        }
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
                tooltip: {
                    headerFormat: '<span style="font-size: 1rem; font-family: Instrument Sans, sans-serif">{point.key}</span><br/>',
                    pointFormat: '<span style="color:{series.color}; font-family: Instrument Sans, sans-serif">{series.name}</span>: <b>{point.y:.2f} €</b> ({point.percentage:.0f}%)<br/>',
                    shared: true,
                    backgroundColor: '#ffffff', // white
                    borderColor: '#e5e7eb', // gray-200
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
                series: <?php echo json_encode($series); ?>,
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

            // Gràfic circular
            Highcharts.chart('ventasPieChart', {
                chart: {
                    type: 'pie'
                },
                title: {
                    text: 'Vendes Anuals per Empleat (<?php echo $anyo_seleccionado; ?>)',
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

            // Inicialitzar DataTables
            $('#tablaVendas').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ca.json'
                },
                order: [[1, 'desc'], [2, 'desc']],
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