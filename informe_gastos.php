<?php include 'head.php';?>

<!-- Content -->
<main class="max-w-7xl mx-auto p-6">
    <h1 class="mb-4 text-4xl font-extrabold text-center text-gray-900 md:text-5xl">
        Gestió <span class="inline-block py-2 text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-purple-500">Informe Despeses</span>
    </h1>
    <script>
        document.title = document.querySelector('h1').textContent;
    </script>

    <!-- Informe 1: Despeses per categoria i mes (columnes apilades) -->
    <section class="mb-12">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Despeses per Categoria i Mes (Columnes Apilades)</h2>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <canvas id="stackedBarChart" class="w-full h-96"></canvas>
        </div>
    </section>

    <!-- Informe 2: Despeses anuals per categories (circular) -->
    <section class="mb-12">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Despeses Anuals per Categories (Gràfic Circular)</h2>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <canvas id="doughnutChart" class="w-full h-96"></canvas>
        </div>
    </section>

    <!-- Informe 3: Tendències mensuals de despeses per categoria (gràfic de línies) -->
    <section class="mb-12">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Tendències Mensuals de Despeses per Categoria (Gràfic de Línies)</h2>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="mb-4">
                <label for="categorySelect" class="block text-sm font-medium text-gray-700 mb-1">Seleccionar Categoria</label>
                <select id="categorySelect" class="w-full max-w-xs px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <!-- Opcions carregades via PHP -->
                </select>
            </div>
            <canvas id="lineChart" class="w-full h-96"></canvas>
        </div>
    </section>

    <!-- Taula de dades detallades -->
    <section class="mb-12">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Detall de Despeses</h2>
        <div class="bg-white p-6 rounded-lg shadow-md overflow-x-auto">
            <table id="despesesTable" class="w-full display nowrap">
                <thead class="bg-gray-100">
                    <tr>
                        <th>Data</th>
                        <th>Categoria</th>
                        <th>Producte</th>
                        <th>Proveïdor</th>
                        <th>Subtotal (€)</th>
                        <th>IVA (€)</th>
                        <th>Total (€)</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Dades carregades via PHP -->
                </tbody>
            </table>
        </div>
    </section>

    <!-- Scripts per als gràfics i DataTables -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicialitzar DataTable
            $('#despesesTable').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ca.json'
                },
                order: [[0, 'desc']],
                searching: true,
                paging: true,
                scrollX: true
            });

            // Dades per al gràfic de columnes apilades
            const stackedBarData = {
                labels: <?php
                    // Obtenir mesos únics
                    $sql = "SELECT DISTINCT DATE_FORMAT(fecha, '%Y-%m') AS mes FROM wp_contabilidad_compras ORDER BY mes";
                    $stmt = $pdo->query($sql);
                    $mesos = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    echo json_encode($mesos);
                ?>,
                datasets: [
                    <?php
                    // Obtenir categories
                    $sql = "SELECT id, nombre_categoria FROM wp_contabilidad_categoria_productos WHERE nombre_categoria LIKE 'A_GASTO%'";
                    $stmt = $pdo->query($sql);
                    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'];
                    $colorIndex = 0;

                    foreach ($categories as $categoria) {
                        $sql = "SELECT DATE_FORMAT(c.fecha, '%Y-%m') AS mes, SUM(dc.subtotal) AS total
                                FROM wp_contabilidad_compras c
                                JOIN wp_contabilidad_detalles_compra dc ON c.id = dc.compra_id
                                JOIN wp_contabilidad_productos p ON dc.producto_id = p.id
                                Huntington WHERE p.id_categoria_producto = :categoria_id
                                GROUP BY mes
                                ORDER BY mes";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute(['categoria_id' => $categoria['id']]);
                        $dades = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        $totals = array_fill(0, count($mesos), 0);
                        foreach ($dades as $dada) {
                            $index = array_search($dada['mes'], $mesos);
                            if ($index !== false) {
                                $totals[$index] = (float)$dada['total'];
                            }
                        }

                        echo "{
                            label: '" . addslashes($categoria['nombre_categoria']) . "',
                            data: " . json_encode($totals) . ",
                            backgroundColor: '" . $colors[$colorIndex % count($colors)] . "'
                        },";
                        $colorIndex++;
                    }
                    ?>
                ]
            };

            // Configuració del gràfic de columnes apilades
            new Chart(document.getElementById('stackedBarChart'), {
                type: 'bar',
                data: stackedBarData,
                options: {
                    plugins: {
                        title: {
                            display: true,
                            text: 'Despeses per Categoria i Mes'
                        }
                    },
                    responsive: true,
                    scales: {
                        x: { stacked: true },
                        y: {
                            stacked: true,
                            title: { display: true, text: 'Total (€)' }
                        }
                    }
                }
            });

            // Dades per al gràfic circular
            const doughnutData = {
                labels: <?php
                    $sql = "SELECT cp.nombre_categoria, SUM(dc.subtotal) AS total
                            FROM wp_contabilidad_compras c
                            JOIN wp_contabilidad_detalles_compra dc ON c.id = dc.compra_id
                            JOIN wp_contabilidad_productos p ON dc.producto_id = p.id
                            JOIN wp_contabilidad_categoria_productos cp ON p.id_categoria_producto = cp.id
                            WHERE cp.nombre_categoria LIKE 'A_GASTO%'
                            GROUP BY cp.id";
                    $stmt = $pdo->query($sql);
                    $dades = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $labels = array_column($dades, 'nombre_categoria');
                    echo json_encode($labels);
                ?>,
                datasets: [{
                    data: <?php
                        $totals = array_column($dades, 'total');
                        echo json_encode(array_map('floatval', $totals));
                    ?>,
                    backgroundColor: <?php echo json_encode($colors); ?>
                }]
            };

            // Configuració del gràfic circular
            new Chart(document.getElementById('doughnutChart'), {
                type: 'doughnut',
                data: doughnutData,
                options: {
                    plugins: {
                        title: {
                            display: true,
                            text: 'Despeses Anuals per Categoria'
                        }
                    },
                    responsive: true
                }
            });

            // Carregar categories al selector
            const categorySelect = $('#categorySelect');
            <?php
            foreach ($categories as $categoria) {
                echo "categorySelect.append('<option value=\"{$categoria['id']}\">" . addslashes($categoria['nombre_categoria']) . "</option>');";
            }
            ?>

            // Dades inicials per al gràfic de línies
            function updateLineChart(categoryId) {
                $.ajax({
                    url: 'get_line_chart_data.php',
                    method: 'POST',
                    data: { category_id: categoryId },
                    success: function(response) {
                        const data = JSON.parse(response);
                        lineChart.data.labels = data.labels;
                        lineChart.data.datasets[0].data = data.data;
                        lineChart.update();
                    }
                });
            }

            // Configuració inicial del gràfic de línies
            const lineChart = new Chart(document.getElementById('lineChart'), {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Despeses Mensuals',
                        data: [],
                        borderColor: '#36A2EB',
                        fill: false
                    }]
                },
                options: {
                    plugins: {
                        title: {
                            display: true,
                            text: 'Tendències Mensuals de Despeses'
                        }
                    },
                    responsive: true,
                    scales: {
                        y: {
                            title: { display: true, text: 'Total (€)' }
                        }
                    }
                }
            });

            // Actualitzar gràfic quan es selecciona una categoria
            categorySelect.on('change', function() {
                updateLineChart($(this).val());
            });

            // Carregar dades inicials
            updateLineChart(categorySelect.val());
        });
    </script>

    <!-- PHP per carregar dades de la taula -->
    <?php
    $sql = "SELECT c.fecha, cp.nombre_categoria, p.nombre AS producto, pr.nombre AS proveedor, dc.subtotal, dc.iva_monto, c.total
            FROM wp_contabilidad_compras c
            JOIN wp_contabilidad_detalles_compra dc ON c.id = dc.compra_id
            JOIN wp_contabilidad_productos p ON dc.producto_id = p.id
            JOIN wp_contabilidad_categoria_productos cp ON p.id_categoria_producto = cp.id
            JOIN wp_contabilidad_proveedores pr ON c.proveedor_id = pr.id
            WHERE cp.nombre_categoria LIKE 'A_GASTO%'
            ORDER BY c.fecha DESC";
    $stmt = $pdo->query($sql);
    $despeses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($despeses as $despesa) {
        echo "<tr>
                <td>" . date('d/m/Y', strtotime($despesa['fecha'])) . "</td>
                <td>" . htmlspecialchars($despesa['nombre_categoria']) . "</td>
                <td>" . htmlspecialchars($despesa['producto']) . "</td>
                <td>" . htmlspecialchars($despesa['proveedor']) . "</td>
                <td>" . number_format($despesa['subtotal'], 2) . "</td>
                <td>" . number_format($despesa['iva_monto'], 2) . "</td>
                <td>" . number_format($despesa['total'], 2) . "</td>
              </tr>";
    }
    ?>
</main>

<?php include 'footer.php';?>