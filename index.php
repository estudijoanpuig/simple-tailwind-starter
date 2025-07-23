<?php include 'head.php';?>


    <!-- Content -->
    <main class="p-6">
	<h1 class="mb-2 text-4xl font-extrabold leading-none tracking-normal text-gray-900 md:text-6xl md:tracking-tight text-center">
                    Gestio<span class="block w-full py-2 text-transparent bg-clip-text leading-12 bg-gradient-to-r from-green-400 to-purple-500 lg:inline">Clients</span>
                </h1>
		<script>
        // Copia el text del <h1> al <title>
        document.title = document.querySelector('h1').textContent;
    </script>		
				
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
    </main>

    <?php include 'footer.php';?>	