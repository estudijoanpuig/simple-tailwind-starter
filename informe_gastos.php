<?php include 'head.php';?>

<!-- Content -->
<main class="max-w-7xl mx-auto p-6">
    <h1 class="mb-4 text-4xl font-extrabold text-center text-gray-900 md:text-5xl">
        Gestió <span class="inline-block py-2 text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-purple-500">Informe Gastos/Despeses</span>
    </h1>
    <script>
        document.title = document.querySelector('h1').textContent;
    </script>

<?php
try {
    $any_actual = date('Y'); // 2025

    // Consulta per gastos mensuals per producte
    $sql_mensual = "SELECT 
                DATE_FORMAT(c.fecha, '%Y-%m') AS mes,
                p.nombre AS nombre_producto,
                SUM(dc.cantidad * dc.precio_unitario) AS total_gastos
            FROM wp_contabilidad_detalles_compra dc
            LEFT JOIN wp_contabilidad_compras c ON dc.compra_id = c.id
            LEFT JOIN wp_contabilidad_productos p ON dc.producto_id = p.id
            GROUP BY DATE_FORMAT(c.fecha, '%Y-%m'), p.nombre
            ORDER BY mes ASC
            LIMIT 500"; // Limit per optimitzar memòria
    $stmt_mensual = $pdo->query($sql_mensual);
    $gastos_mensual = $stmt_mensual->fetchAll(PDO::FETCH_ASSOC);
    unset($stmt_mensual); // Alliberar memòria

    // Consulta per gastos anuals per proveïdor
    $sql_anual = "SELECT 
                p.nombre AS nombre_proveidor,
                SUM(c.total) AS total_gastos
            FROM wp_contabilidad_compras c
            LEFT JOIN wp_contabilidad_proveedores p ON c.proveedor_id = p.id
            WHERE YEAR(c.fecha) = ?
            GROUP BY p.nombre
            HAVING total_gastos > 0
            LIMIT 50"; // Limit per optimitzar memòria
    $stmt_anual = $pdo->prepare($sql_anual);
    $stmt_anual->execute([$any_actual]);
    $gastos_anual = $stmt_anual->fetchAll(PDO::FETCH_ASSOC);
    unset($stmt_anual);

    // Consulta per tots els productes amb totals de gastos de l'any actual
    $sql_productos = "SELECT 
                p.nombre AS nombre_producto,
                SUM(dc.cantidad * dc.precio_unitario) AS total_gastos
            FROM wp_contabilidad_detalles_compra dc
            LEFT JOIN wp_contabilidad_compras c ON dc.compra_id = c.id
            LEFT JOIN wp_contabilidad_productos p ON dc.producto_id = p.id
            WHERE YEAR(c.fecha) = ?
            GROUP BY p.nombre
            ORDER BY total_gastos DESC
            LIMIT 100"; // Limit per seguretat, ajustable
    $stmt_productos = $pdo->prepare($sql_productos);
    $stmt_productos->execute([$any_actual]);
    $productos_mas_costosos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);
    unset($stmt_productos);

    // Processament de dades per al gràfic mensual per producte
    $meses = [];
    $productos = [];
    $data_por_producto = [];

    foreach ($gastos_mensual as $gasto) {
        $mes = $gasto['mes'];
        $producto = $gasto['nombre_producto'] ?? 'Sense producte';
        $total = floatval($gasto['total_gastos']);

        if (!in_array($mes, $meses)) {
            $meses[] = $mes;
        }
        if (!in_array($producto, $productos)) {
            $productos[] = $producto;
            $data_por_producto[$producto] = [];
        }

        $data_por_producto[$producto][$mes] = $total;
    }
    unset($gastos_mensual); // Alliberar memòria

    sort($meses);

    $colores = ['#2563eb', '#22c55e', '#ef4444', '#8b5cf6', '#f59e0b', '#ec4899'];
    $series_mensual = [];
    foreach ($productos as $index => $producto) {
        $data = [];
        foreach ($meses as $mes) {
            $data[] = isset($data_por_producto[$producto][$mes]) ? $data_por_producto[$producto][$mes] : 0;
        }
        $series_mensual[] = [
            'name' => $producto,
            'data' => $data,
            'color' => $colores[$index % count($colores)]
        ];
    }
    unset($data_por_producto); // Alliberar memòria

    // Dades per al gràfic anual
    $data_anual = [];
    foreach ($gastos_anual as $gasto) {
        $proveedor = $gasto['nombre_proveidor'] ?? 'Sense proveïdor';
        $total = floatval($gasto['total_gastos']);
        $data_anual[] = [
            'name' => $proveedor,
            'y' => $total,
            'color' => $colores[array_rand($colores)]
        ];
    }
    unset($gastos_anual);

    // Dades per al gràfic de productes
    $data_productos = [];
    foreach ($productos_mas_costosos as $producto) {
        $nombre = $producto['nombre_producto'] ?? 'Sense nom';
        $total = floatval($producto['total_gastos']);
        $data_productos[] = [
            'name' => $nombre,
            'y' => $total,
            'color' => $colores[array_rand($colores)]
        ];
    }
    unset($productos_mas_costosos);
} catch (PDOException $e) {
    echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4'>Error al carregar dades de Gastos/compres: " . $e->getMessage() . "</div>";
    $meses = [];
    $series_mensual = [];
    $data_anual = [];
    $data_productos = [];
    $any_actual = date('Y');
}
?>

    <!-- Secció de Gràfics -->
    <div class="grid grid-cols-1 gap-6 mb-8">
        <!-- Gràfic Mensual -->
        <div class="bg-white rounded-lg shadow-md p-6">
            
            <div id="grafic_mensual" class="h-[500px]"></div>
        </div>

        <!-- Gràfic Anual -->
        <div class="bg-white rounded-lg shadow-md p-6">
           htmlspecialchars($any_actual); ?>)</h2>
            <div id="grafic_anual" class="h-[600px]"></div>
        </div>

        <!-- Gràfic Productes -->
        <div class="bg-white rounded-lg shadow-md p-6">
            
            <div id="grafic_productes" class="h-[400px]"></div>
        </div>
    </div>

    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Gràfic Mensual (Columnes Apilades per Producte)
            Highcharts.chart('grafic_mensual', {
                chart: {
                    type: 'column'
                },
                title: {
                    text: 'Gastos Mensuals per Producte',
                    style: {
                        fontFamily: 'Instrument Sans, sans-serif',
                        fontSize: '1.5rem',
                        fontWeight: '600',
                        color: '#1f2937'
                    }
                },
                xAxis: {
                    categories: <?php echo json_encode($meses); ?>,
                    title: {
                        text: 'Mes-Any',
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
                        text: 'Total Gastos (€)',
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
                    },
                    stackLabels: {
                        enabled: true,
                        style: {
                            fontWeight: 'bold',
                            color: '#1f2937',
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
                    pointFormat: '<span style="color:{series.color}; font-family: Instrument Sans, sans-serif">{series.name}</span>: <b>{point.y:.2f} €</b> ({point.percentage:.0f}%)<br/>',
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
                series: <?php echo json_encode($series_mensual); ?>,
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

            // Gràfic Anual (Pastís per Proveïdor)
            Highcharts.chart('grafic_anual', {
                chart: {
                    type: 'pie'
                },
                title: {
                    text: 'Gastos Anuals per Proveïdor (<?php echo htmlspecialchars($any_actual); ?>)',
                    style: {
                        fontFamily: 'Instrument Sans, sans-serif',
                        fontSize: '1.5rem',
                        fontWeight: '600',
                        color: '#1f2937'
                    }
                },
                tooltip: {
                    pointFormat: '<span style="color:{point.color}; font-family: Instrument Sans, sans-serif">{point.name}</span>: <b>{point.y:.2f} €</b> ({point.percentage:.1f}%)',
                    backgroundColor: '#ffffff',
                    borderColor: '#e5e7eb',
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
                                color: '#374151',
                                fontWeight: 'normal'
                            }
                        },
                        showInLegend: true
                    }
                },
                legend: {
                    align: 'left',
                    verticalAlign: 'middle',
                    backgroundColor: '#f9fafb',
                    borderColor: '#e5e7eb',
                    borderWidth: 1,
                    itemStyle: {
                        fontFamily: 'Instrument Sans, sans-serif',
                        color: '#374151'
                    }
                },
                series: [{
                    name: 'Gastos',
                    data: <?php echo json_encode($data_anual); ?>,
                    size: '80%',
                    innerSize: '50%'
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

            // Gràfic Productes (Barres)
            Highcharts.chart('grafic_productes', {
                chart: {
                    type: 'bar'
                },
                title: {
                    text: 'Productes més Costosos (<?php echo htmlspecialchars($any_actual); ?>)',
                    style: {
                        fontFamily: 'Instrument Sans, sans-serif',
                        fontSize: '1.5rem',
                        fontWeight: '600',
                        color: '#1f2937'
                    }
                },
                xAxis: {
                    categories: <?php echo json_encode(array_column($data_productos, 'name')); ?>,
                    title: {
                        text: 'Producte',
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
                        text: 'Total Gastos (€)',
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
                legend: {
                    enabled: false
                },
                tooltip: {
                    pointFormat: '<span style="color:{point.color}; font-family: Instrument Sans, sans-serif">{point.name}</span>: <b>{point.y:.2f} €</b>',
                    backgroundColor: '#ffffff',
                    borderColor: '#e5e7eb',
                    style: {
                        fontFamily: 'Instrument Sans, sans-serif'
                    }
                },
                series: [{
                    name: 'Total Gastos',
                    data: <?php echo json_encode(array_map(function($item) { return ['y' => $item['y'], 'color' => $item['color']]; }, $data_productos)); ?>
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
        });
    </script>

<?php
// Alliberar memòria final
unset($meses, $series_mensual, $data_anual, $data_productos);
?>
<?php include 'footer.php';?>
</main>