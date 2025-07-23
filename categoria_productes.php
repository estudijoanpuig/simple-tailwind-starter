<?php include 'head.php';?>

<!-- Content -->
<main class="max-w-7xl mx-auto p-6">
    <h1 class="mb-4 text-4xl font-extrabold text-center text-gray-900 md:text-5xl">
        Gestió <span class="inline-block py-2 text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-purple-500">Categoria Productes</span>
    </h1>
    <script>
        document.title = document.querySelector('h1').textContent;
    </script>	

<?php
// Crear o actualizar categoria
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $nombre_categoria = trim($_POST['nombre_categoria']);

    // Validacions bàsiques
    if (empty($nombre_categoria)) {
        echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4' role='alert'>El nom de la categoria és obligatori.</div>";
    } else {
        try {
            if ($id) {
                // Actualitzar
                $sql = "UPDATE wp_contabilidad_categoria_productos SET nombre_categoria = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre_categoria, $id]);
                echo "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4' role='alert'>Categoria actualitzada correctament.</div>";
            } else {
                // Crear
                $sql = "INSERT INTO wp_contabilidad_categoria_productos (nombre_categoria) VALUES (?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre_categoria]);
                echo "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4' role='alert'>Categoria creada correctament.</div>";
            }
        } catch (PDOException $e) {
            echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4' role='alert'>Error: " . $e->getMessage() . "</div>";
        }
    }
}

// Eliminar categoria
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        // Verificar si la categoria està en ús en wp_contabilidad_productos
        $sql_check = "SELECT COUNT(*) FROM wp_contabilidad_productos WHERE id_categoria_producto = ?";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([$id]);
        $count = $stmt_check->fetchColumn();

        if ($count > 0) {
            echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4' role='alert'>No es pot eliminar la categoria perquè està en ús per productes.</div>";
        } else {
            $sql = "DELETE FROM wp_contabilidad_categoria_productos WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            echo "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4' role='alert'>Categoria eliminada correctament.</div>";
        }
    } catch (PDOException $e) {
        echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4' role='alert'>Error al eliminar: " . $e->getMessage() . "</div>";
    }
}

// Obtenir llista de categories
try {
    $sql = "SELECT * FROM wp_contabilidad_categoria_productos";
    $stmt = $pdo->query($sql);
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4' role='alert'>Error al carregar categories: " . $e->getMessage() . "</div>";
    $categorias = [];
}

// Obtenir dades per a edició
$categoria_editar = null;
if (isset($_GET['accion']) && $_GET['accion'] === 'editar' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $sql = "SELECT * FROM wp_contabilidad_categoria_productos WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $categoria_editar = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4' role='alert'>Error al carregar categoria: " . $e->getMessage() . "</div>";
    }
}
?>


    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Gestió de Categories de Productes</h2>

    <!-- Formulari per crear/actualitzar amb Tailwind CSS -->
    <form method="POST" class="bg-white p-6 rounded-lg shadow-md space-y-6 mb-8">
        <input type="hidden" name="id" value="<?php echo $categoria_editar ? htmlspecialchars($categoria_editar['id']) : ''; ?>">
        
        <div>
            <label for="nombre_categoria" class="block text-sm font-medium text-gray-700 mb-2">Nom de la Categoria</label>
            <input type="text" id="nombre_categoria" name="nombre_categoria" required
                   class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                   value="<?php echo $categoria_editar ? htmlspecialchars($categoria_editar['nombre_categoria']) : ''; ?>">
        </div>

        <div class="flex justify-end">
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <?php echo $categoria_editar ? 'Actualitzar' : 'Crear'; ?> Categoria
            </button>
        </div>
    </form>

    
        <table id="tablaClientes" class="display nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom de la Categoria</th>
                    <th>Data Creació</th>
                    <th>Data Actualització</th>
                    <th>Accions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categorias as $categoria): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($categoria['id']); ?></td>
                        <td><?php echo htmlspecialchars($categoria['nombre_categoria']); ?></td>
                        <td><?php echo htmlspecialchars($categoria['created_at'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($categoria['updated_at'] ?? ''); ?></td>
                        <td class="text-center">
                            <a href="?tabla=categoria_productos&accion=editar&id=<?php echo htmlspecialchars($categoria['id']); ?>" class="text-yellow-600 hover:text-yellow-900 mr-3" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?tabla=categoria_productos&accion=eliminar&id=<?php echo htmlspecialchars($categoria['id']); ?>" class="text-red-500 hover:text-red-700" 
                               onclick="return confirm('¿Segur que vols eliminar?')" title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    
</div>

<!-- Estils per a les icones -->
<style>
    .text-center i {
        font-size: 1.2rem;
        cursor: pointer;
        margin: 0 8px;
    }
    .text-center i:hover {
        opacity: 0.8;
    }
</style>

<script>
    $(document).ready(function() {
        $('#tablaCategoriaProductos').DataTable({
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

<?php include 'footer.php';?>