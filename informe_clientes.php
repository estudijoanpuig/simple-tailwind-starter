<?php include 'head.php';?>

<?php
// Consulta per obtenir els 30 millors clients per total consumit, excloent nif = 'APARATOLOGIA_EXTERNA'
$query = "
    SELECT 
        c.nombre AS name,
        SUM(v.total) AS total
    FROM wp_contabilidad_ventas v
    INNER JOIN wp_contabilidad_clientes c ON v.cliente_id = c.id
    WHERE c.nif != 'APARATOLOGIA_EXTERNA'
    GROUP BY c.id, c.nombre
    ORDER BY total DESC
    LIMIT 30
";
$stmt = $pdo->prepare($query);
$stmt->execute();
$topClients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta per obtenir tots els clients amb el total consumit, excloent nif = 'APARATOLOGIA_EXTERNA'
$query_all = "
    SELECT 
        c.nombre AS name,
        SUM(v.total) AS total
    FROM wp_contabilidad_ventas v
    INNER JOIN wp_contabilidad_clientes c ON v.cliente_id = c.id
    WHERE c.nif != 'APARATOLOGIA_EXTERNA'
    GROUP BY c.id, c.nombre
    ORDER BY total DESC
";
$stmt_all = $pdo->prepare($query_all);
$stmt_all->execute();
$allClients = $stmt_all->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Content -->
<main class="max-w-7xl mx-auto p-6">
    <h1 class="mb-4 text-4xl font-extrabold text-center text-gray-900 md:text-5xl">
        Gestió <span class="inline-block py-2 text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-purple-500">Informe clientes</span>
    </h1>
    <script>
        document.title = document.querySelector('h1').textContent;
    </script>

    <!-- Highcharts for the top 30 clients -->
    <div class="mb-8">
        <div id="topClientsChart" class="w-full h-96"></div>
    </div>

    <!-- DataTables for client consumption -->
    <div class="overflow-x-auto">
        <table id="clientsTable" class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3">Client</th>
                    <th scope="col" class="px-6 py-3">Total Consumit (€)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($allClients as $client) {
                    echo "<tr class='bg-white border-b'>";
                    echo "<td class='px-6 py-4'>" . htmlspecialchars($client['name']) . "</td>";
                    echo "<td class='px-6 py-4'>" . number_format($client['total'], 2, ',', '.') . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Highcharts Script -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script>
        // Dades per al gràfic (top 30 clients)
        const clientsData = <?php echo json_encode($topClients); ?>;
        const categories = clientsData.map(client => client.name);
        const data = clientsData.map(client => parseFloat(client.total));

        // Configuració del gràfic amb Highcharts
        Highcharts.chart('topClientsChart', {
            chart: {
                type: 'column',
                height: 400
            },
            title: {
                text: 'Top 30 Clients per Total Consumit'
            },
            xAxis: {
                categories: categories,
                title: {
                    text: 'Clients'
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Total Consumit (€)'
                }
            },
            series: [{
                name: 'Total Consumit',
                data: data,
                color: '#4bc0c0'
            }],
            plotOptions: {
                column: {
                    colorByPoint: false
                }
            },
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
            $('#clientsTable').DataTable({
                responsive: true,
                pageLength: 30,
                order: [[1, 'desc']],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ca.json'
                }
            });
        });
    </script>
</main>

<?php include 'footer.php';?>