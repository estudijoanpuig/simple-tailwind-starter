<?php
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gestió Contable Autónomo</title>
    <link rel="icon" type="image/png" href="img/logo.png">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <style>
        .text-red {
            color: #dc3545 !important;
        }

        .text-red:hover {
            color: #c82333 !important;
        }

        /* Ajustes para DataTables con Tailwind */
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.25rem 1.5rem 0.25rem 0.5rem;
        }

        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.25rem 0.5rem;
            margin-left: 0.5rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.25rem 0.75rem;
            border: 1px solid transparent;
            border-radius: 0.375rem;
            margin-left: 0.25rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #3b82f6;
            color: white !important;
            border: 1px solid #3b82f6;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #e5e7eb;
            border: 1px solid #d1d5db;
        }

        /* Sidebar mobile */

        #sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 50;
        }
       
    </style>
</head>

<body class="bg-gray-100 font-sans">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div id="sidebar"
            class="w-64 bg-blue-500 text-white p-4 transform transition-transform duration-300 ease-in-out md:translate-x-0 -translate-x-full fixed md:relative h-full">
            <div class="flex items-center space-x-2 mb-8">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="text-xl font-bold">Dashboard</span>
            </div>

            <nav>
                <ul class="space-y-2">
                    <li>
                        <a href="index.php" class="flex items-center space-x-2 p-2 rounded bg-blue-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span>Clients</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center space-x-2 p-2 rounded hover:bg-blue-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <span>Analíticas</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center space-x-2 p-2 rounded hover:bg-blue-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Ventas</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center space-x-2 p-2 rounded hover:bg-blue-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>Usuarios</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center space-x-2 p-2 rounded hover:bg-blue-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Ajustes</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto md:ml-64 transition-all duration-300">
            <!-- Header -->
            <header class="bg-white shadow p-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <button id="menu-toggle"
                            class="md:hidden p-2 rounded-md text-gray-500 hover:bg-gray-100 focus:outline-none mr-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <h1 class="text-2xl font-bold text-gray-800">Gestio de Clients</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                       
                        <div class="flex items-center space-x-2">
                            <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="User"
                                class="h-8 w-8 rounded-full">
                            <span class="text-gray-700">Ana Pérez</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="p-6">
                <?php
                // CRUD functionality
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $id = isset($_POST['id']) ? (int) $_POST['id'] : null;
                    $nombre = trim($_POST['nombre']);
                    $nif = trim($_POST['nif']);
                    $direccion = trim($_POST['direccion']);
                    $telefono = trim($_POST['telefono']);
                    $email = trim($_POST['email']);

                    // Validaciones
                    if (empty($nombre)) {
                        echo "<div class='p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg'>El nom és obligatori.</div>";
                    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)) {
                        echo "<div class='p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg'>Email no vàlid.</div>";
                    } else {
                        try {
                            if ($id) {
                                // Actualizar
                                $sql = "UPDATE wp_contabilidad_clientes SET nombre = ?, nif = ?, direccion = ?, telefono = ?, email = ? WHERE id = ?";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([$nombre, $nif, $direccion, $telefono, $email, $id]);
                                echo "<div class='p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg'>Client actualitzat correctament.</div>";
                            } else {
                                // Crear
                                $sql = "INSERT INTO wp_contabilidad_clientes (nombre, nif, direccion, telefono, email) VALUES (?, ?, ?, ?, ?)";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([$nombre, $nif, $direccion, $telefono, $email]);
                                echo "<div class='p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg'>Client creat correctament.</div>";
                            }
                        } catch (PDOException $e) {
                            echo "<div class='p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg'>Error: " . $e->getMessage() . "</div>";
                        }
                    }
                }

                // Eliminar cliente
                if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id'])) {
                    $id = (int) $_GET['id'];
                    try {
                        $sql = "DELETE FROM wp_contabilidad_clientes WHERE id = ?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$id]);
                        echo "<div class='p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg'>Client eliminat correctament.</div>";
                    } catch (PDOException $e) {
                        echo "<div class='p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg'>Error al eliminar: " . $e->getMessage() . "</div>";
                    }
                }

                // Obtener lista de clientes
                try {
                    $sql = "SELECT * FROM wp_contabilidad_clientes";
                    $stmt = $pdo->query($sql);
                    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    echo "<div class='p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg'>Error al carregar clients: " . $e->getMessage() . "</div>";
                    $clientes = [];
                }

                // Obtener datos para edición
                $cliente_editar = null;
                if (isset($_GET['accion']) && $_GET['accion'] === 'editar' && isset($_GET['id'])) {
                    $id = (int) $_GET['id'];
                    try {
                        $sql = "SELECT * FROM wp_contabilidad_clientes WHERE id = ?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$id]);
                        $cliente_editar = $stmt->fetch(PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {
                        echo "<div class='p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg'>Error al carregar client: " . $e->getMessage() . "</div>";
                    }
                }
                ?>

                <!-- Formulari per crear/actualitzar -->
                <form method="POST" class="mb-8 bg-white p-6 rounded-lg shadow-md">
                    <input type="hidden" name="id" value="<?php echo $cliente_editar ? $cliente_editar['id'] : ''; ?>">
                    <div class="mb-4">
                        <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                        <input type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            name="nombre"
                            value="<?php echo $cliente_editar ? htmlspecialchars($cliente_editar['nombre']) : ''; ?>"
                            required>
                    </div>
                    <div class="mb-4">
                        <label for="nif" class="block text-sm font-medium text-gray-700 mb-1">NIF</label>
                        <input type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            name="nif"
                            value="<?php echo $cliente_editar ? htmlspecialchars($cliente_editar['nif']) : ''; ?>">
                    </div>
                    <div class="mb-4">
                        <label for="direccion" class="block text-sm font-medium text-gray-700 mb-1">Adreça</label>
                        <textarea
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            name="direccion"><?php echo $cliente_editar ? htmlspecialchars($cliente_editar['direccion']) : ''; ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">Telèfon</label>
                        <input type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            name="telefono"
                            value="<?php echo $cliente_editar ? htmlspecialchars($cliente_editar['telefono']) : ''; ?>">
                    </div>
                    <div class="mb-6">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            name="email"
                            value="<?php echo $cliente_editar ? htmlspecialchars($cliente_editar['email']) : ''; ?>">
                    </div>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <?php echo $cliente_editar ? 'Actualitzar' : 'Crear'; ?> Client
                    </button>
                </form>

                <!-- Taula de clients amb DataTables -->
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    <table id="tablaClientes" class="display nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>NIF</th>
                                <th>Adreça</th>
                                <th>Telèfon</th>
                                <th>Email</th>
                                <th>Accions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clientes as $cliente): ?>
                                <tr>
                                    <td><?php echo $cliente['id']; ?></td>
                                    <td><?php echo htmlspecialchars($cliente['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['nif'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['direccion'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['email'] ?? ''); ?></td>
                                    <td class="text-center">
                                        <a href="?tabla=clientes&accion=editar&id=<?php echo $cliente['id']; ?>"
                                            class="text-yellow-600 hover:text-yellow-900 mr-3" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?tabla=clientes&accion=eliminar&id=<?php echo $cliente['id']; ?>"
                                            class="text-red-600 hover:text-red-900"
                                            onclick="return confirm('¿Segur que vols eliminar?')" title="Eliminar">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Botó Tornar amunt -->
    <button id="to-top-button" onclick="goToTop()" title="Tornar amunt"
        class="hidden fixed bottom-4 right-4 p-3 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 transition-colors">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <!-- Moment.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <!-- DataTables Moment Plugin -->
    <script src="https://cdn.datatables.net/plug-ins/1.13.6/sorting/datetime-moment.js"></script>

    <script>
        // Funció per gestionar el botó Tornar amunt
        const toTopButton = document.getElementById("to-top-button");
        window.addEventListener("scroll", function () {
            if (window.scrollY > 200) {
                toTopButton.classList.remove("hidden");
                toTopButton.classList.add("block");
            } else {
                toTopButton.classList.remove("block");
                toTopButton.classList.add("hidden");
            }
        });

        function goToTop() {
            window.scrollTo({ top: 0, behavior: "smooth" });
        }

        // Toggle del menú lateral
        document.addEventListener('DOMContentLoaded', function () {
            const menuToggle = document.getElementById('menu-toggle');
            const sidebar = document.getElementById('sidebar');

            menuToggle.addEventListener('click', function () {
                sidebar.classList.toggle('-translate-x-full');
                sidebar.classList.toggle('translate-x-0');
            });

            // Cerrar menú al hacer clic fuera en móviles
            document.addEventListener('click', function (event) {
                if (window.innerWidth < 768 &&
                    !sidebar.contains(event.target) &&
                    !menuToggle.contains(event.target)) {
                    sidebar.classList.add('-translate-x-full');
                    sidebar.classList.remove('translate-x-0');
                }
            });

            // Configuració de DataTables
            $.fn.dataTable.moment('DD/MM/YYYY');

            $('#tablaClientes').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ca.json'
                },
                order: [[0, 'asc']],
                columnDefs: [
                    { orderable: true, targets: '_all' },
                    { orderable: false, targets: -1 }
                ],
                searching: true,
                paging: true,
                scrollX: true
            });
        });
    </script>
</body>

</html>