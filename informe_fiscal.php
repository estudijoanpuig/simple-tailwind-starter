<?php include 'head.php';?>

<?php
// Consulta per obtenir el total facturat per trimestres i mètode de pagament
$query_trimestres = "
    SELECT 
        CONCAT(YEAR(fecha), '-Q', QUARTER(fecha)) AS trimestre,
        notas AS metode_pagament,
        SUM(total) AS total_facturat
    FROM wp_contabilidad_ventas
    WHERE notas IN (
        'Metode de pagament: efectiu',
        'Metode de pagament: targeta',
        'Metode de pagament: BIZUM NEUS',
        'Metode de pagament: Bono',
        'Metode de pagament: Pendent'
    )
    GROUP BY trimestre, metode_pagament
    ORDER BY trimestre, metode_pagament
";
$stmt_trimestres = $pdo->prepare($query_trimestres);
$stmt_trimestres->execute();
$trimestres = $stmt_trimestres->fetchAll(PDO::FETCH_ASSOC);

// Organitzar les dades per al gràfic (per mètode de pagament)
$metodes = [
    'Metode de pagament: efectiu',
    'Metode de pagament: targeta',
    'Metode de pagament: BIZUM NEUS',
    'Metode de pagament: Bono',
    'Metode de pagament: Pendent'
];
$trimestres_unicos = array_unique(array_column($trimestres, 'trimestre'));
$series_data = [];
foreach ($metodes as $metode) {
    $data = [];
    foreach ($trimestres_unicos as $trimestre) {
        $found = false;
        foreach ($trimestres as $row) {
            if ($row['trimestre'] === $trimestre && $row['metode_pagament'] === $metode) {
                $data[] = floatval($row['total_facturat']);
                $found = true;
                break;
            }
        }
        if (!$found) {
            $data[] = 0; // Si no hi ha dades per aquest mètode i trimestre, posar 0
        }
    }
    $series_data[] = [
        'name' => str_replace('Metode de pagament: ', '', $metode),
        'data' => $data
    ];
}

// Consulta per obtenir IVA suportat i repercutit per trimestre (tota la facturació)
$query_iva_total = "
    SELECT 
        CONCAT(YEAR(fecha), '-Q', QUARTER(fecha)) AS trimestre,
        'IVA Repercutit' AS tipus,
        SUM(iva_monto) AS monto,
        COUNT(DISTINCT id) AS num_operacions
    FROM wp_contabilidad_ventas
    GROUP BY trimestre
    UNION ALL
    SELECT 
        CONCAT(YEAR(fecha), '-Q', QUARTER(fecha)) AS trimestre,
        'IVA Suportat' AS tipus,
        SUM(iva_monto) AS monto,
        COUNT(DISTINCT id) AS num_operacions
    FROM wp_contabilidad_compras
    GROUP BY trimestre
    ORDER BY trimestre, tipus
";
$stmt_iva_total = $pdo->prepare($query_iva_total);
$stmt_iva_total->execute();
$iva_total = $stmt_iva_total->fetchAll(PDO::FETCH_ASSOC);

// Consulta per IVA repercutit (pagaments amb targetes) per trimestre
$query_iva_targetes = "
    SELECT 
        CONCAT(YEAR(fecha), '-Q', QUARTER(fecha)) AS trimestre,
        'IVA Repercutit (Targetes)' AS tipus,
        SUM(iva_monto) AS monto,
        COUNT(DISTINCT id) AS num_operacions
    FROM wp_contabilidad_ventas
    WHERE notas = 'Metode de pagament: targeta'
    GROUP BY trimestre
    ORDER BY trimestre
";
$stmt_iva_targetes = $pdo->prepare($query_iva_targetes);
$stmt_iva_targetes->execute();
$iva_targetes = $stmt_iva_targetes->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Content -->
<main class="max-w-7xl mx-auto p-6">
    <h1 class="mb-4 text-4xl font-extrabold text-center text-gray-900 md:text-5xl">
        Gestió <span class="inline-block py-2 text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-purple-500">Informe Resultats</span>
    </h1>
    <script>
        document.title = document.querySelector('h1').textContent;
    </script>

    <!-- Highcharts for total facturat per trimestres (columnes apilades) -->
    <div class="mb-8">
        <div id="trimestresChart" class="w-full h-[600px]"></div>
    </div>

    <!-- DataTables for IVA suportat i repercutit (tota la facturació) -->
    <div class="mb-8">
        <h2 class="mb-4 text-2xl font-bold text-gray-800">IVA Suportat i Repercutit per Trimestre (Tota la Facturació)</h2>
        <div class="overflow-x-auto">
            <table id="ivaTotalTable" class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Trimestre</th>
                        <th scope="col" class="px-6 py-3">Tipus</th>
                        <th scope="col" class="px-6 py-3">Mont (€)</th>
                        <th scope="col" class="px-6 py-3">Nombre d'Operacions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($iva_total as $row) {
                        $monto = is_null($row['monto']) ? 0.00 : $row['monto'];
                        echo "<tr class='bg-white border-b'>";
                        echo "<td class='px-6 py-4'>" . htmlspecialchars($row['trimestre']) . "</td>";
                        echo "<td class='px-6 py-4'>" . htmlspecialchars($row['tipus']) . "</td>";
                        echo "<td class='px-6 py-4'>" . number_format($monto, 2, ',', '.') . "</td>";
                        echo "<td class='px-6 py-4'>" . $row['num_operacions'] . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- DataTables for IVA repercutit (pagaments amb targetes) -->
    <div>
        <h2 class="mb-4 text-2xl font-bold text-gray-800">IVA Repercutit per Trimestre (Pagaments amb Targetes)</h2>
        <div class="overflow-x-auto">
            <table id="ivaTargetesTable" class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Trimestre</th>
                        <th scope="col" class="px-6 py-3">Tipus</th>
                        <th scope="col" class="px-6 py-3">Mont (€)</th>
                        <th scope="col" class="px-6 py-3">Nombre d'Operacions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($iva_targetes as $row) {
                        $monto = is_null($row['monto']) ? 0.00 : $row['monto'];
                        echo "<tr class='bg-white border-b'>";
                        echo "<td class='px-6 py-4'>" . htmlspecialchars($row['trimestre']) . "</td>";
                        echo "<td class='px-6 py-4'>" . htmlspecialchars($row['tipus']) . "</td>";
                        echo "<td class='px-6 py-4'>" . number_format($monto, 2, ',', '.') . "</td>";
                        echo "<td class='px-6 py-4'>" . $row['num_operacions'] . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Highcharts Script -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script>
        // Dades per al gràfic (total facturat per trimestres, apilat per mètode de pagament)
        const trimestresData = <?php echo json_encode($trimestres_unicos); ?>;
        const series = <?php echo json_encode($series_data); ?>;

        // Configuració del gràfic amb Highcharts (columnes apilades)
        Highcharts.chart('trimestresChart', {
            chart: {
                type: 'column',
                height: 600, // Alçada mínima de 600 píxels
                backgroundColor: '#f3f4f6' // Fons suau (gris clar de Tailwind CSS)
            },
            title: {
                text: 'Total Facturat per Trimestres (Apilat per Mètode de Pagament)'
            },
            xAxis: {
                categories: trimestresData,
                title: {
                    text: 'Trimestre'
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Total Facturat (€)'
                },
                stackLabels: {
                    enabled: true,
                    style: {
                        fontWeight: 'bold',
                        color: '#ff0000', // Color sòlid vermell per al total
                        textOutline: 'none' // Eliminar el contorn negre
                    },
                    formatter: function() {
                        return Highcharts.numberFormat(this.total, 2, ',', '.') + ' €';
                    }
                }
            },
            plotOptions: {
                column: {
                    stacking: 'normal',
                    dataLabels: {
                        enabled: true,
                        format: '{y} €'
                    }
                }
            },
            series: series,
            responsive: {
                rules: [{
                    condition: {
                        maxWidth: 500
                    },
                    chartOptions: {
                        legend: {
                            enabled: false
                        }
                    }
                }]
            }
        });
    </script>

    <!-- Incloure jQuery i DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css" rel="stylesheet">

    <script>
        $(document).ready(function() {
            $('#ivaTotalTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[0, 'asc'], [1, 'desc']], // Ordenar per trimestre i després per mont
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ca.json'
                }
            });
            $('#ivaTargetesTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[0, 'asc'], [1, 'desc']], // Ordenar per trimestre i després per mont
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ca.json'
                }
            });
        });
    </script>
</main>

<?php include 'footer.php';?>