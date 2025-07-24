<?php include 'head.php';?>

<main class="max-w-7xl mx-auto p-6">
    <h1 class="mb-4 text-4xl font-extrabold text-center text-gray-900 md:text-5xl">
        Seguiment <span class="inline-block py-2 text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-purple-500">Corporal</span>
    </h1>
    <script>
        document.title = document.querySelector('h1').textContent;
    </script>

    <!-- Incloure Flatpickr CSS i JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Formulari per afegir/editar mesures corporals -->
    <section class="mb-12">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Gestionar Mesures Corporals</h2>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <form method="POST" id="mesuraForm" class="space-y-4">
                <input type="hidden" id="edit_id" name="edit_id" value="">
                <div>
                    <label for="cliente_id" class="block text-sm font-medium text-gray-700 mb-1">Client</label>
                    <select id="cliente_id" name="cliente_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecciona un client</option>
                        <?php
                        $sql = "SELECT id, nombre FROM wp_contabilidad_clientes ORDER BY nombre ASC";
                        $stmt = $pdo->query($sql);
                        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($clients as $client) {
                            echo "<option value='{$client['id']}'>" . htmlspecialchars($client['nombre']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label for="fecha_medicion" class="block text-sm font-medium text-gray-700 mb-1">Data de la Mesura</label>
                    <input type="text" id="fecha_medicion" name="fecha_medicion" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Selecciona una data">
                </div>
                <div>
                    <label for="peso_kg" class="block text-sm font-medium text-gray-700 mb-1">Pes (kg)</label>
                    <input type="number" id="peso_kg" name="peso_kg" step="0.1" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="cintura_cm" class="block text-sm font-medium text-gray-700 mb-1">Cintura (cm)</label>
                    <input type="number" id="cintura_cm" name="cintura_cm" step="0.1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="malucs_cm" class="block text-sm font-medium text-gray-700 mb-1">Malucs (cm)</label>
                    <input type="number" id="malucs_cm" name="malucs_cm" step="0.1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="cuixes_cm" class="block text-sm font-medium text-gray-700 mb-1">Cuixes (cm)</label>
                    <input type="number" id="cuixes_cm" name="cuixes_cm" step="0.1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="braços_cm" class="block text-sm font-medium text-gray-700 mb-1">Braços (cm)</label>
                    <input type="number" id="braços_cm" name="braços_cm" step="0.1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="greix_percentatge" class="block text-sm font-medium text-gray-700 mb-1">Greix (%)</label>
                    <input type="number" id="greix_percentatge" name="greix_percentatge" step="0.1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="imc" class="block text-sm font-medium text-gray-700 mb-1">IMC</label>
                    <input type="number" id="imc" name="imc" step="0.1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="notas" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea id="notas" name="notas" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Observacions"></textarea>
                </div>
                <button type="submit" id="submitBtn" name="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Registrar Mesura</button>
            </form>

            <?php
            if (isset($_POST['submit'])) {
                try {
                    if (empty($_POST['edit_id'])) {
                        // Crear nova mesura
                        $sql = "INSERT INTO wp_contabilidad_seguiment_corporal (cliente_id, fecha_medicion, peso_kg, cintura_cm, malucs_cm, cuixes_cm, braços_cm, greix_percentatge, imc, notas) 
                                VALUES (:cliente_id, :fecha_medicion, :peso_kg, :cintura_cm, :malucs_cm, :cuixes_cm, :braços_cm, :greix_percentatge, :imc, :notas)";
                    } else {
                        // Actualitzar mesura existent
                        $sql = "UPDATE wp_contabilidad_seguiment_corporal SET cliente_id = :cliente_id, fecha_medicion = :fecha_medicion, peso_kg = :peso_kg, cintura_cm = :cintura_cm, malucs_cm = :malucs_cm, cuixes_cm = :cuixes_cm, braços_cm = :braços_cm, greix_percentatge = :greix_percentatge, imc = :imc, notas = :notas WHERE id = :id";
                    }
                    $stmt = $pdo->prepare($sql);
                    $params = [
                        'cliente_id' => $_POST['cliente_id'],
                        'fecha_medicion' => $_POST['fecha_medicion'],
                        'peso_kg' => $_POST['peso_kg'],
                        'cintura_cm' => $_POST['cintura_cm'] ?: null,
                        'malucs_cm' => $_POST['malucs_cm'] ?: null,
                        'cuixes_cm' => $_POST['cuixes_cm'] ?: null,
                        'braços_cm' => $_POST['braços_cm'] ?: null, // Corregit per assegurar compatibilitat
                        'greix_percentatge' => $_POST['greix_percentatge'] ?: null,
                        'imc' => $_POST['imc'] ?: null,
                        'notas' => $_POST['notas'] ?: null
                    ];
                    if (!empty($_POST['edit_id'])) {
                        $params['id'] = $_POST['edit_id'];
                    }
                    $stmt->execute($params);
                    echo "<div class='mt-4 p-4 bg-green-100 text-green-700 rounded'>" . (empty($_POST['edit_id']) ? 'Mesura registrada' : 'Mesura actualitzada') . " correctament!</div>";
                    // Reiniciar formulari
                    $_POST = array();
                    echo "<script>$('#mesuraForm')[0].reset(); $('#edit_id').val(''); $('#submitBtn').text('Registrar Mesura');</script>";
                } catch (PDOException $e) {
                    echo "<div class='mt-4 p-4 bg-red-100 text-red-700 rounded'>Error: " . $e->getMessage() . "</div>";
                }
            }

            if (isset($_POST['delete_id'])) {
                try {
                    $sql = "DELETE FROM wp_contabilidad_seguiment_corporal WHERE id = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(['id' => $_POST['delete_id']]);
                    echo "<div class='mt-4 p-4 bg-green-100 text-green-700 rounded'>Mesura eliminada correctament!</div>";
                } catch (PDOException $e) {
                    echo "<div class='mt-4 p-4 bg-red-100 text-red-700 rounded'>Error: " . $e->getMessage() . "</div>";
                }
            }
            ?>
        </div>
    </section>

    <!-- Taula de mesures corporals -->
    <section class="mb-12">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Llista de Mesures Corporals</h2>
        <div class="bg-white p-6 rounded-lg shadow-md overflow-x-auto">
            <table id="mesuresTable" class="w-full display nowrap" style="width: 100%;">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="whitespace-nowrap">Client</th>
                        <th class="whitespace-nowrap">Data</th>
                        <th class="whitespace-nowrap">Pes (kg)</th>
                        <th class="whitespace-nowrap">Cintura (cm)</th>
                        <th class="whitespace-nowrap">Malucs (cm)</th>
                        <th class="whitespace-nowrap">Cuixes (cm)</th>
                        <th class="whitespace-nowrap">Braços (cm)</th>
                        <th class="whitespace-nowrap">Greix (%)</th>
                        <th class="whitespace-nowrap">IMC</th>
                        <th class="whitespace-nowrap">Notes</th>
                        <th class="whitespace-nowrap">Accions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT sc.id, c.nombre AS client, sc.fecha_medicion, sc.peso_kg, sc.cintura_cm, sc.malucs_cm, sc.cuixes_cm, sc.braços_cm, sc.greix_percentatge, sc.imc, sc.notas
                            FROM wp_contabilidad_seguiment_corporal sc
                            JOIN wp_contabilidad_clientes c ON sc.cliente_id = c.id
                            WHERE EXISTS (
                                SELECT 1 FROM wp_contabilidad_tractaments_laser tl
                                JOIN wp_contabilidad_productos p ON tl.tipo_tratamiento = p.nombre
                                JOIN wp_contabilidad_categoria_productos cp ON p.id_categoria_producto = cp.id
                                WHERE cp.id = 14 AND tl.cliente_id = sc.cliente_id
                            )
                            ORDER BY sc.fecha_medicion DESC";
                    $stmt = $pdo->query($sql);
                    $mesures = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($mesures as $mesura) {
                        echo "<tr>
                                <td>" . htmlspecialchars($mesura['client']) . "</td>
                                <td>" . date('d/m/Y', strtotime($mesura['fecha_medicion'])) . "</td>
                                <td>" . ($mesura['peso_kg'] ?: '-') . "</td>
                                <td>" . ($mesura['cintura_cm'] ?: '-') . "</td>
                                <td>" . ($mesura['malucs_cm'] ?: '-') . "</td>
                                <td>" . ($mesura['cuixes_cm'] ?: '-') . "</td>
                                <td>" . ($mesura['braços_cm'] ?: '-') . "</td>
                                <td>" . ($mesura['greix_percentatge'] ?: '-') . "</td>
                                <td>" . ($mesura['imc'] ?: '-') . "</td>
                                <td>" . htmlspecialchars($mesura['notas'] ?: '-') . "</td>
                                <td>
                                    <button class='edit-btn text-blue-500 hover:text-blue-700 mr-2' data-id='{$mesura['id']}' 
                                            data-cliente='{$mesura['client']}' 
                                            data-fecha='{$mesura['fecha_medicion']}' 
                                            data-peso='{$mesura['peso_kg']}' 
                                            data-cintura='{$mesura['cintura_cm']}' 
                                            data-malucs='{$mesura['malucs_cm']}' 
                                            data-cuixes='{$mesura['cuixes_cm']}' 
                                            data-braços='{$mesura['braços_cm']}' 
                                            data-greix='{$mesura['greix_percentatge']}' 
                                            data-imc='{$mesura['imc']}' 
                                            data-notas='{$mesura['notas']}'>
                                        <i class='fas fa-edit'></i>
                                    </button>
                                    <form method='POST' style='display:inline;' onsubmit='return confirm(\"Segur que vols eliminar aquesta mesura?\");'>
                                        <input type='hidden' name='delete_id' value='{$mesura['id']}'>
                                        <button type='submit' class='text-red-500 hover:text-red-700'>
                                            <i class='fas fa-trash'></i>
                                        </button>
                                    </form>
                                </td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Gràfic de progrés -->
    <section class="mb-12">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Evolució del Pes</h2>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <canvas id="pesChart" class="w-full h-96"></canvas>
        </div>
    </section>

    <!-- Scripts per DataTables, Flatpickr i Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicialitzar Flatpickr
            flatpickr("#fecha_medicion", {
                dateFormat: "Y-m-d",
                locale: {
                    firstDayOfWeek: 1,
                    weekdays: {
                        shorthand: ['Dg', 'Dl', 'Dt', 'Dc', 'Dj', 'Dv', 'Ds'],
                        longhand: ['Diumenge', 'Dilluns', 'Dimarts', 'Dimecres', 'Dijous', 'Divendres', 'Dissabte']
                    },
                    months: {
                        shorthand: ['Gen', 'Feb', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Oct', 'Nov', 'Des'],
                        longhand: ['Gener', 'Febrer', 'Març', 'Abril', 'Maig', 'Juny', 'Juliol', 'Agost', 'Setembre', 'Octubre', 'Novembre', 'Desembre']
                    }
                }
            });

            // Inicialitzar DataTable amb correccions
            $('#mesuresTable').DataTable({
                responsive: true,
                autoWidth: false,
                scrollX: true,
                scrollCollapse: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ca.json'
                },
                order: [[1, 'desc']],
                searching: true,
                paging: true,
                initComplete: function() {
                    this.api().columns.adjust().draw();
                }
            });

            // Gestionar edició
            $('.edit-btn').click(function() {
                const id = $(this).data('id');
                const cliente = $(this).data('cliente');
                const fecha = $(this).data('fecha');
                const peso = $(this).data('peso');
                const cintura = $(this).data('cintura');
                const malucs = $(this).data('malucs');
                const cuixes = $(this).data('cuixes');
                const braços = $(this).data('braços');
                const greix = $(this).data('greix');
                const imc = $(this).data('imc');
                const notas = $(this).data('notas');

                // Omplir el formulari amb les dades
                $('#edit_id').val(id);
                $('#cliente_id').val(getClientId(cliente));
                $('#fecha_medicion').val(fecha);
                $('#peso_kg').val(peso || '');
                $('#cintura_cm').val(cintura || '');
                $('#malucs_cm').val(malucs || '');
                $('#cuixes_cm').val(cuixes || '');
                $('#braços_cm').val(braços || '');
                $('#greix_percentatge').val(greix || '');
                $('#imc').val(imc || '');
                $('#notas').val(notas || '');
                $('#submitBtn').text('Actualitzar Mesura');
            });

            // Funció per obtenir l'ID del client pel nom
            function getClientId(clientName) {
                let clientId = '';
                $('#cliente_id option').each(function() {
                    if ($(this).text() === clientName) {
                        clientId = $(this).val();
                        return false;
                    }
                });
                return clientId;
            }

            // Reiniciar formulari després d'enviar
            $('#mesuraForm').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: 'seguiment_corporal.php',
                    method: 'POST',
                    data: $(this).serialize() + '&submit=true',
                    success: function() {
                        $('#mesuraForm')[0].reset();
                        $('#edit_id').val('');
                        $('#submitBtn').text('Registrar Mesura');
                        location.reload(); // Recarregar per actualitzar la taula
                    }
                });
            });

            // Dades per al gràfic de línies (evolució del pes)
            const pesData = {
                labels: <?php
                    $sql = "SELECT fecha_medicion FROM wp_contabilidad_seguiment_corporal sc
                            WHERE EXISTS (
                                SELECT 1 FROM wp_contabilidad_tractaments_laser tl
                                JOIN wp_contabilidad_productos p ON tl.tipo_tratamiento = p.nombre
                                JOIN wp_contabilidad_categoria_productos cp ON p.id_categoria_producto = cp.id
                                WHERE cp.id = 14 AND tl.cliente_id = sc.cliente_id
                            )
                            ORDER BY fecha_medicion ASC";
                    $stmt = $pdo->query($sql);
                    $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    echo json_encode($dates);
                ?>,
                datasets: [{
                    label: 'Pes (kg)',
                    data: <?php
                        $sql = "SELECT peso_kg FROM wp_contabilidad_seguiment_corporal sc
                                WHERE EXISTS (
                                    SELECT 1 FROM wp_contabilidad_tractaments_laser tl
                                    JOIN wp_contabilidad_productos p ON tl.tipo_tratamiento = p.nombre
                                    JOIN wp_contabilidad_categoria_productos cp ON p.id_categoria_producto = cp.id
                                    WHERE cp.id = 14 AND tl.cliente_id = sc.cliente_id
                                )
                                ORDER BY fecha_medicion ASC";
                        $stmt = $pdo->query($sql);
                        $pesos = $stmt->fetchAll(PDO::FETCH_COLUMN);
                        echo json_encode(array_map('floatval', $pesos));
                    ?>,
                    borderColor: '#36A2EB',
                    fill: false
                }]
            };

            // Configuració del gràfic de línies
            new Chart(document.getElementById('pesChart'), {
                type: 'line',
                data: pesData,
                options: {
                    plugins: {
                        title: {
                            display: true,
                            text: 'Evolució del Pes dels Clients amb Tractaments Corporals'
                        }
                    },
                    responsive: true,
                    scales: {
                        x: { title: { display: true, text: 'Data' } },
                        y: { title: { display: true, text: 'Pes (kg)' }, beginAtZero: true }
                    }
                }
            });
        });
    </script>

    <!-- Estil addicional per corregir alineació -->
    <style>
        #mesuresTable_wrapper {
            width: 100%;
            overflow-x: auto;
        }
        #mesuresTable th,
        #mesuresTable td {
            min-width: 100px;
            padding: 8px 12px;
        }
    </style>
</main>

<?php include 'footer.php';?>