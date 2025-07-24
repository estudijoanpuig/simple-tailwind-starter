<?php include 'head.php';?>

<main class="max-w-7xl mx-auto p-6">
    <h1 class="mb-4 text-4xl font-extrabold text-center text-gray-900 md:text-5xl">
        Seguiment <span class="inline-block py-2 text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-purple-500">Tractaments Làser</span>
    </h1>
    <script>
        document.title = document.querySelector('h1').textContent;
    </script>

    <!-- Incloure Flatpickr CSS i JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Formulari per afegir/editar tractaments -->
    <section class="mb-12">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Gestionar Tractament Làser</h2>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <form method="POST" id="tractamentForm" class="space-y-4">
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
                    <label for="fecha_tratamiento" class="block text-sm font-medium text-gray-700 mb-1">Data del Tractament</label>
                    <input type="text" id="fecha_tratamiento" name="fecha_tratamiento" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Selecciona una data">
                </div>
                <div>
                    <label for="tipo_tratamiento" class="block text-sm font-medium text-gray-700 mb-1">Tipus de Tractament</label>
                    <select id="tipo_tratamiento" name="tipo_tratamiento" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="Depilació làser">Depilació làser</option>
                        <option value="Rejoveniment facial">Rejoveniment facial</option>
                        <option value="Eliminació de taques">Eliminació de taques</option>
                        <option value="Altres">Altres</option>
                    </select>
                </div>
                <div>
                    <label for="zona_cuerpo" class="block text-sm font-medium text-gray-700 mb-1">Zona del Cos</label>
                    <input type="text" id="zona_cuerpo" name="zona_cuerpo" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex: Cames, Cara">
                </div>
                <div>
                    <label for="num_sesion" class="block text-sm font-medium text-gray-700 mb-1">Número de Sessió</label>
                    <input type="number" id="num_sesion" name="num_sesion" required min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="notas" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea id="notas" name="notas" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Observacions del tractament"></textarea>
                </div>
                <button type="submit" id="submitBtn" name="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Registrar Tractament</button>
            </form>

            <?php
            if (isset($_POST['submit'])) {
                try {
                    if (empty($_POST['edit_id'])) {
                        // Crear nou tractament
                        $sql = "INSERT INTO wp_contabilidad_tractaments_laser (cliente_id, fecha_tratamiento, tipo_tratamiento, zona_cuerpo, num_sesion, notas) 
                                VALUES (:cliente_id, :fecha_tratamiento, :tipo_tratamiento, :zona_cuerpo, :num_sesion, :notas)";
                    } else {
                        // Actualitzar tractament existent
                        $sql = "UPDATE wp_contabilidad_tractaments_laser SET cliente_id = :cliente_id, fecha_tratamiento = :fecha_tratamiento, tipo_tratamiento = :tipo_tratamiento, zona_cuerpo = :zona_cuerpo, num_sesion = :num_sesion, notas = :notas WHERE id = :id";
                    }
                    $stmt = $pdo->prepare($sql);
                    $params = [
                        'cliente_id' => $_POST['cliente_id'],
                        'fecha_tratamiento' => $_POST['fecha_tratamiento'],
                        'tipo_tratamiento' => $_POST['tipo_tratamiento'],
                        'zona_cuerpo' => $_POST['zona_cuerpo'] ?: null,
                        'num_sesion' => $_POST['num_sesion'],
                        'notas' => $_POST['notas'] ?: null
                    ];
                    if (!empty($_POST['edit_id'])) {
                        $params['id'] = $_POST['edit_id'];
                    }
                    $stmt->execute($params);
                    echo "<div class='mt-4 p-4 bg-green-100 text-green-700 rounded'>" . (empty($_POST['edit_id']) ? 'Tractament registrat' : 'Tractament actualitzat') . " correctament!</div>";
                    // Reiniciar formulari
                    $_POST = array();
                    echo "<script>$('#tractamentForm')[0].reset(); $('#edit_id').val(''); $('#submitBtn').text('Registrar Tractament');</script>";
                } catch (PDOException $e) {
                    echo "<div class='mt-4 p-4 bg-red-100 text-red-700 rounded'>Error: " . $e->getMessage() . "</div>";
                }
            }

            if (isset($_POST['delete_id'])) {
                try {
                    $sql = "DELETE FROM wp_contabilidad_tractaments_laser WHERE id = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(['id' => $_POST['delete_id']]);
                    echo "<div class='mt-4 p-4 bg-green-100 text-green-700 rounded'>Tractament eliminat correctament!</div>";
                } catch (PDOException $e) {
                    echo "<div class='mt-4 p-4 bg-red-100 text-red-700 rounded'>Error: " . $e->getMessage() . "</div>";
                }
            }
            ?>
        </div>
    </section>

    <!-- Taula de tractaments -->
    <section class="mb-12">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Llista de Tractaments Làser</h2>
        <div class="bg-white p-6 rounded-lg shadow-md overflow-x-auto">
            <table id="tractamentsTable" class="w-full display nowrap" style="width: 100%;">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="whitespace-nowrap">Client</th>
                        <th class="whitespace-nowrap">Data</th>
                        <th class="whitespace-nowrap">Tipus de Tractament</th>
                        <th class="whitespace-nowrap">Zona del Cos</th>
                        <th class="whitespace-nowrap">Nº Sessió</th>
                        <th class="whitespace-nowrap">Notes</th>
                        <th class="whitespace-nowrap">Accions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT t.id, c.nombre AS client, t.fecha_tratamiento, t.tipo_tratamiento, t.zona_cuerpo, t.num_sesion, t.notas
                            FROM wp_contabilidad_tractaments_laser t
                            JOIN wp_contabilidad_clientes c ON t.cliente_id = c.id
                            ORDER BY t.fecha_tratamiento DESC";
                    $stmt = $pdo->query($sql);
                    $tractaments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($tractaments as $tractament) {
                        echo "<tr>
                                <td>" . htmlspecialchars($tractament['client']) . "</td>
                                <td>" . date('d/m/Y', strtotime($tractament['fecha_tratamiento'])) . "</td>
                                <td>" . htmlspecialchars($tractament['tipo_tratamiento']) . "</td>
                                <td>" . htmlspecialchars($tractament['zona_cuerpo'] ?: '-') . "</td>
                                <td>" . $tractament['num_sesion'] . "</td>
                                <td>" . htmlspecialchars($tractament['notas'] ?: '-') . "</td>
                                <td>
                                    <button class='edit-btn text-blue-500 hover:text-blue-700 mr-2' data-id='{$tractament['id']}' 
                                            data-cliente='{$tractament['client']}' 
                                            data-fecha='{$tractament['fecha_tratamiento']}' 
                                            data-tipo='{$tractament['tipo_tratamiento']}' 
                                            data-zona='{$tractament['zona_cuerpo']}' 
                                            data-sesion='{$tractament['num_sesion']}' 
                                            data-notas='{$tractament['notas']}'>
                                        <i class='fas fa-edit'></i>
                                    </button>
                                    <form method='POST' style='display:inline;' onsubmit='return confirm(\"Segur que vols eliminar aquest tractament?\");'>
                                        <input type='hidden' name='delete_id' value='{$tractament['id']}'>
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
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Progrés dels Tractaments Làser</h2>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <canvas id="sessionsChart" class="w-full h-96"></canvas>
        </div>
    </section>

    <!-- Scripts per DataTables, Flatpickr i Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicialitzar Flatpickr
            flatpickr("#fecha_tratamiento", {
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
            $('#tractamentsTable').DataTable({
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
                const tipo = $(this).data('tipo');
                const zona = $(this).data('zona');
                const sesion = $(this).data('sesion');
                const notas = $(this).data('notas');

                // Omplir el formulari amb les dades
                $('#edit_id').val(id);
                $('#cliente_id').val(getClientId(cliente)); // Funció per obtenir ID del client pel nom
                $('#fecha_tratamiento').val(fecha);
                $('#tipo_tratamiento').val(tipo);
                $('#zona_cuerpo').val(zona || '');
                $('#num_sesion').val(sesion);
                $('#notas').val(notas || '');
                $('#submitBtn').text('Actualitzar Tractament');
            });

            // Funció per obtenir l'ID del client pel nom (basada en les opcions del select)
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
            $('#tractamentForm').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: 'seguiment_laser.php',
                    method: 'POST',
                    data: $(this).serialize() + '&submit=true',
                    success: function() {
                        $('#tractamentForm')[0].reset();
                        $('#edit_id').val('');
                        $('#submitBtn').text('Registrar Tractament');
                        location.reload(); // Recarregar per actualitzar la taula
                    }
                });
            });

            // Dades per al gràfic de barres
            const sessionsData = {
                labels: <?php
                    $sql = "SELECT c.nombre
                            FROM wp_contabilidad_tractaments_laser t
                            JOIN wp_contabilidad_clientes c ON t.cliente_id = c.id
                            GROUP BY c.id
                            ORDER BY COUNT(t.id) DESC
                            LIMIT 10";
                    $stmt = $pdo->query($sql);
                    $clients = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    echo json_encode($clients);
                ?>,
                datasets: [{
                    label: 'Nombre de Sessions',
                    data: <?php
                        $sql = "SELECT COUNT(t.id) AS total
                                FROM wp_contabilidad_tractaments_laser t
                                JOIN wp_contabilidad_clientes c ON t.cliente_id = c.id
                                GROUP BY c.id
                                ORDER BY total DESC
                                LIMIT 10";
                        $stmt = $pdo->query($sql);
                        $totals = $stmt->fetchAll(PDO::FETCH_COLUMN);
                        echo json_encode(array_map('intval', $totals));
                    ?>,
                    backgroundColor: '#36A2EB'
                }]
            };

            // Configuració del gràfic de barres
            new Chart(document.getElementById('sessionsChart'), {
                type: 'bar',
                data: sessionsData,
                options: {
                    plugins: {
                        title: {
                            display: true,
                            text: 'Top 10 Clients per Nombre de Sessions Làser'
                        }
                    },
                    responsive: true,
                    scales: {
                        y: {
                            title: { display: true, text: 'Nombre de Sessions' },
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>

    <!-- Estil addicional per corregir alineació -->
    <style>
        #tractamentsTable_wrapper {
            width: 100%;
            overflow-x: auto;
        }
        #tractamentsTable th,
        #tractamentsTable td {
            min-width: 100px;
            padding: 8px 12px;
        }
    </style>
</main>

<?php include 'footer.php';?>