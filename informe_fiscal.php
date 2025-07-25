<?php include 'head.php'; ?>

<!-- Content -->
<main class="max-w-7xl mx-auto p-6">
    <h1 class="mb-4 text-4xl font-extrabold text-center text-gray-900 md:text-5xl">
        Gestió <span class="inline-block py-2 text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-purple-500">Informe Fiscal</span>
    </h1>
    <script>
        document.title = document.querySelector('h1').textContent;
    </script>

    <?php
    try {
        // Consulta per obtenir l'IVA repercutit (totes les vendes) per trimestre
        $sql_ventas = "
            SELECT 
                CONCAT(t.any, '-Q', t.trimestre_num) AS trimestre,
                SUM(t.iva_monto) AS iva_repercutit
            FROM (
                SELECT 
                    YEAR(fecha) AS any,
                    QUARTER(fecha) AS trimestre_num,
                    iva_monto
                FROM wp_contabilidad_ventas
            ) AS t
            GROUP BY t.any, t.trimestre_num
            ORDER BY t.any, t.trimestre_num
        ";
        $stmt_ventas = $pdo->query($sql_ventas);
        $ventas_data = $stmt_ventas->fetchAll(PDO::FETCH_ASSOC);

        // Consulta per obtenir l'IVA suportat (compres) per trimestre
        $sql_compras = "
            SELECT 
                CONCAT(t.any, '-Q', t.trimestre_num) AS trimestre,
                SUM(t.iva_monto) AS iva_suportat
            FROM (
                SELECT 
                    YEAR(fecha) AS any,
                    QUARTER(fecha) AS trimestre_num,
                    iva_monto
                FROM wp_contabilidad_compras
            ) AS t
            GROUP BY t.any, t.trimestre_num
            ORDER BY t.any, t.trimestre_num
        ";
        $stmt_compras = $pdo->query($sql_compras);
        $compras_data = $stmt_compras->fetchAll(PDO::FETCH_ASSOC);

        // Consulta per obtenir l'IVA repercutit de vendes en efectiu per trimestre
        $sql_ventas_efectiu = "
            SELECT 
                CONCAT(t.any, '-Q', t.trimestre_num) AS trimestre,
                SUM(t.iva_monto) AS iva_repercutit
            FROM (
                SELECT 
                    YEAR(fecha) AS any,
                    QUARTER(fecha) AS trimestre_num,
                    iva_monto
                FROM wp_contabilidad_ventas
                WHERE notas = 'Metode de pagament: efectiu'
            ) AS t
            GROUP BY t.any, t.trimestre_num
            ORDER BY t.any, t.trimestre_num
        ";
        $stmt_ventas_efectiu = $pdo->query($sql_ventas_efectiu);
        $ventas_efectiu_data = $stmt_ventas_efectiu->fetchAll(PDO::FETCH_ASSOC);

        // Consulta per obtenir l'IVA suportat (compres) per trimestre, alineat amb vendes en efectiu
        $sql_compras_efectiu = "
            SELECT 
                CONCAT(t.any, '-Q', t.trimestre_num) AS trimestre,
                SUM(t.iva_monto) AS iva_suportat
            FROM (
                SELECT 
                    YEAR(fecha) AS any,
                    QUARTER(fecha) AS trimestre_num,
                    iva_monto
                FROM wp_contabilidad_compras
            ) AS t
            GROUP BY t.any, t.trimestre_num
            ORDER BY t.any, t.trimestre_num
        ";
        $stmt_compras_efectiu = $pdo->query($sql_compras_efectiu);
        $compras_efectiu_data = $stmt_compras_efectiu->fetchAll(PDO::FETCH_ASSOC);

        // Combinar les dades per trimestre (totes les vendes i compres)
        $quarters = [];
        $resultados = [];

        // Processar dades de vendes
        foreach ($ventas_data as $venta) {
            $quarters[$venta['trimestre']] = true;
            $resultados[$venta['trimestre']]['iva_repercutit'] = $venta['iva_repercutit'];
        }

        // Processar dades de compres
        foreach ($compras_data as $compra) {
            $quarters[$compra['trimestre']] = true;
            $resultados[$compra['trimestre']]['iva_suportat'] = $compra['iva_suportat'];
        }

        // Crear arrays per al gràfic i la taula de totes les vendes i compres
        $categories = array_keys($quarters);
        $iva_repercutit_data = [];
        $iva_suportat_data = [];
        $diferencia_data = [];
        $resultados_data = [];

        foreach ($categories as $trimestre) {
            $iva_repercutit = isset($resultados[$trimestre]['iva_repercutit']) ? floatval($resultados[$trimestre]['iva_repercutit']) : 0;
            $iva_suportat = isset($resultados[$trimestre]['iva_suportat']) ? floatval($resultados[$trimestre]['iva_suportat']) : 0;
            $diferencia = $iva_repercutit - $iva_suportat;

            $iva_repercutit_data[] = $iva_repercutit;
            $iva_suportat_data[] = $iva_suportat;
            $diferencia_data[] = $diferencia;

            $resultados_data[] = [
                'trimestre' => $trimestre,
                'iva_repercutit' => number_format($iva_repercutit, 2, ',', '.'),
                'iva_suportat' => number_format($iva_suportat, 2, ',', '.'),
                'diferencia' => number_format($diferencia, 2, ',', '.')
            ];
        }

        // Combinar les dades per trimestre (vendes en efectiu i compres alineades)
        $quarters_efectiu = [];
        $resultados_efectiu = [];

        // Processar dades de vendes en efectiu
        foreach ($ventas_efectiu_data as $venta) {
            $quarters_efectiu[$venta['trimestre']] = true;
            $resultados_efectiu[$venta['trimestre']]['iva_repercutit'] = $venta['iva_repercutit'];
        }

        // Processar dades de compres per a vendes en efectiu (usem les mateixes categories)
        foreach ($compras_efectiu_data as $compra) {
            if (isset($quarters_efectiu[$compra['trimestre']])) {
                $resultados_efectiu[$compra['trimestre']]['iva_suportat'] = $compra['iva_suportat'];
            }
        }

        // Crear arrays per al gràfic i la taula de vendes en efectiu
        $categories_efectiu = array_keys($quarters_efectiu);
        $iva_repercutit_efectiu_data = [];
        $iva_suportat_efectiu_data = [];
        $diferencia_efectiu_data = [];
        $resultados_efectiu_data = [];

        foreach ($categories_efectiu as $trimestre) {
            $iva_repercutit = isset($resultados_efectiu[$trimestre]['iva_repercutit']) ? floatval($resultados_efectiu[$trimestre]['iva_repercutit']) : 0;
            $iva_suportat = isset($resultados_efectiu[$trimestre]['iva_suportat']) ? floatval($resultados_efectiu[$trimestre]['iva_suportat']) : 0;
            $diferencia = $iva_repercutit - $iva_suportat;

            $iva_repercutit_efectiu_data[] = $iva_repercutit;
            $iva_suportat_efectiu_data[] = $iva_suportat;
            $diferencia_efectiu_data[] = $diferencia;

            $resultados_efectiu_data[] = [
                'trimestre' => $trimestre,
                'iva_repercutit' => number_format($iva_repercutit, 2, ',', '.'),
                'iva_suportat' => number_format($iva_suportat, 2, ',', '.'),
                'diferencia' => number_format($diferencia, 2, ',', '.')
            ];
        }
    } catch (PDOException $e) {
        echo "<div class='text-red-500 text-center'>Error en la consulta: " . htmlspecialchars($e->getMessage()) . "</div>";
        exit;
    }
    ?>

    <!-- Secció del gràfic: Totes les vendes i compres -->
    <div class="mb-8">
        <h2 class="text-2xl font-semibold text-gray-700 mb-4 text-center">Informe General d'IVA</h2>
        <div id="iva-chart" class="w-full h-96"></div>
    </div>

    <!-- Secció de la taula: Totes les vendes i compres -->
    <div class="mb-12 overflow-x-auto">
        <h2 class="text-2xl font-semibold text-gray-700 mb-4 text-center">Taula General d'IVA</h2>
        <table id="iva-table" class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trimestre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IVA Repercutit (€)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IVA Suportat (€)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diferència (€)</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($resultados_data as $row): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['trimestre']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['iva_repercutit']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['iva_suportat']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['diferencia']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Secció del gràfic: Vendes en efectiu -->
    <div class="mb-8">
        <h2 class="text-2xl font-semibold text-gray-700 mb-4 text-center">Informe d'IVA de Vendes en Efectiu</h2>
        <div id="iva-efectiu-chart" class="w-full h-96"></div>
    </div>

    <!-- Secció de la taula: Vendes en efectiu -->
    <div class="mb-12 overflow-x-auto">
        <h2 class="text-2xl font-semibold text-gray-700 mb-4 text-center">Taula d'IVA de Vendes en Efectiu</h2>
        <table id="iva-efectiu-table" class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trimestre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IVA Repercutit (€)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IVA Suportat (€)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diferència (€)</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($resultados_efectiu_data as $row): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['trimestre']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['iva_repercutit']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['iva_suportat']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['diferencia']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Incloure Highcharts -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script>
        // Gràfic per a totes les vendes i compres
        Highcharts.chart('iva-chart', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'Informe Fiscal de l\'IVA per Trimestre'
            },
            xAxis: {
                categories: <?php echo json_encode($categories); ?>,
                title: {
                    text: 'Trimestre'
                }
            },
            yAxis: {
                title: {
                    text: 'Import (€)'
                }
            },
            series: [{
                name: 'IVA Repercutit',
                data: <?php echo json_encode($iva_repercutit_data); ?>,
                color: '#34D399'
            }, {
                name: 'IVA Suportat',
                data: <?php echo json_encode($iva_suportat_data); ?>,
                color: '#F87171'
            }, {
                name: 'Diferència',
                data: <?php echo json_encode($diferencia_data); ?>,
                color: '#6B7280'
            }],
            credits: {
                enabled: false
            }
        });

        // Gràfic per a vendes en efectiu
        Highcharts.chart('iva-efectiu-chart', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'IVA de Vendes en Efectiu per Trimestre'
            },
            xAxis: {
                categories: <?php echo json_encode($categories_efectiu); ?>,
                title: {
                    text: 'Trimestre'
                }
            },
            yAxis: {
                title: {
                    text: 'Import (€)'
                }
            },
            series: [{
                name: 'IVA Repercutit (Efectiu)',
                data: <?php echo json_encode($iva_repercutit_efectiu_data); ?>,
                color: '#34D399'
            }, {
                name: 'IVA Suportat',
                data: <?php echo json_encode($iva_suportat_efectiu_data); ?>,
                color: '#F87171'
            }, {
                name: 'Diferència',
                data: <?php echo json_encode($diferencia_efectiu_data); ?>,
                color: '#6B7280'
            }],
            credits: {
                enabled: false
            }
        });
    </script>

    <!-- Incloure jQuery i DataTables per a les taules -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            // Taula per a totes les vendes i compres
            $('#iva-table').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/ca.json'
                },
                order: [[0, 'desc']],
                pageLength: 10
            });

            // Taula per a vendes en efectiu
            $('#iva-efectiu-table').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/ca.json'
                },
                order: [[0, 'desc']],
                pageLength: 10
            });
        });
    </script>

    <?php
    // Alliberar memòria final
    unset($quarters, $ventas_data, $compras_data, $ventas_efectiu_data, $compras_efectiu_data, $resultados_data, $resultados, $quarters_efectiu, $resultados_efectiu_data, $pagaments_trimestre_data, $pagaments_ingressos_by_trimestre, $pagaments_despeses_by_trimestre, $trimestres);
    ?>
</main>
<?php include 'footer.php'; ?>