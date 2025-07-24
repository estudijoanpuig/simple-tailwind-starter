<?php include 'head.php';?>

<!-- Content -->
<main class="p-6">
    <h1 class="mb-2 text-4xl font-extrabold leading-none tracking-normal text-gray-900 md:text-6xl md:tracking-tight text-center">
        Gestio<span class="block w-full py-2 text-transparent bg-clip-text leading-12 bg-gradient-to-r from-green-400 to-purple-500 lg:inline">Proveedores</span>
    </h1>
    <script>
        // Copia el text del <h1> al <title>
        document.title = document.querySelector('h1').textContent;
    </script>

<?php
// proveedores.php


// Crear o actualizar proveïdor
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $nombre = trim($_POST['nombre']);
    $nif = trim($_POST['nif']);
    $direccion = trim($_POST['direccion']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);

    // Validacions bàsiques
    if (empty($nombre)) {
        echo "<div class='alert alert-danger'>El nom és obligatori.</div>";
    } elseif (empty($nif)) {
        echo "<div class='alert alert-danger'>El NIF és obligatori.</div>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)) {
        echo "<div class='alert alert-danger'>Email no vàlid.</div>";
    } else {
        try {
            if ($id) {
                // Actualitzar
                $sql = "UPDATE wp_contabilidad_proveedores SET nombre = ?, nif = ?, direccion = ?, telefono = ?, email = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre, $nif, $direccion, $telefono, $email, $id]);
                echo "<div class='alert alert-success'>Proveïdor actualitzat correctament.</div>";
            } else {
                // Crear
                $sql = "INSERT INTO wp_contabilidad_proveedores (nombre, nif, direccion, telefono, email) VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre, $nif, $direccion, $telefono, $email]);
                echo "<div class='alert alert-success'>Proveïdor creat correctament.</div>";
            }
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    }
}

// Eliminar proveïdor
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $sql = "DELETE FROM wp_contabilidad_proveedores WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        echo "<div class='alert alert-success'>Proveïdor eliminat correctament.</div>";
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error al eliminar: " . $e->getMessage() . "</div>";
    }
}

// Obtenir llista de proveïdors
try {
    $sql = "SELECT * FROM wp_contabilidad_proveedores";
    $stmt = $pdo->query($sql);
    $proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error al carregar proveïdors: " . $e->getMessage() . "</div>";
    $proveedores = [];
}

// Obtenir dades per a edició
$proveedor_editar = null;
if (isset($_GET['accion']) && $_GET['accion'] === 'editar' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $sql = "SELECT * FROM wp_contabilidad_proveedores WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $proveedor_editar = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error al carregar proveïdor: " . $e->getMessage() . "</div>";
    }
}
?>

 <div class="w-full p-[5px] m-[5px]">
    

    <!-- Formulari per crear/actualitzar -->
    <form method="POST" class="w-full p-[5px] m-[5px]">
    <input type="hidden" name="id" value="<?php echo $proveedor_editar ? $proveedor_editar['id'] : ''; ?>">
    
    <div class="mb-4">
        <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
        <input type="text" 
               class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
               name="nombre" 
               value="<?php echo $proveedor_editar ? htmlspecialchars($proveedor_editar['nombre']) : ''; ?>" 
               required>
    </div>
    
    <div class="mb-4">
        <label for="nif" class="block text-sm font-medium text-gray-700 mb-1">NIF</label>
        <input type="text" 
               class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
               name="nif" 
               value="<?php echo $proveedor_editar ? htmlspecialchars($proveedor_editar['nif']) : ''; ?>" 
               required>
    </div>
    
    <div class="mb-4">
        <label for="direccion" class="block text-sm font-medium text-gray-700 mb-1">Adreça</label>
        <textarea class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all min-h-[100px]"
                  name="direccion"><?php echo $proveedor_editar ? htmlspecialchars($proveedor_editar['direccion']) : ''; ?></textarea>
    </div>
    
    <div class="mb-4">
        <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">Telèfon</label>
        <input type="text" 
               class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
               name="telefono" 
               value="<?php echo $proveedor_editar ? htmlspecialchars($proveedor_editar['telefono']) : ''; ?>">
    </div>
    
    <div class="mb-6">
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input type="email" 
               class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
               name="email" 
               value="<?php echo $proveedor_editar ? htmlspecialchars($proveedor_editar['email']) : ''; ?>">
    </div>
    
    <button type="submit" 
            class="w-full px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors shadow-sm">
        <?php echo $proveedor_editar ? 'Actualitzar' : 'Crear'; ?> Proveïdor
    </button>
</form>

    <!-- Taula de proveïdors amb DataTables -->
    <table id="tablaProveedores" class="display nowrap" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>NIF</th>
                <th>Adreça</th>
                <th>Telèfon</th>
                <th>Email</th>
                <th>Data Actualització</th>
                <th>Accions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($proveedores as $proveedor): ?>
                <tr>
                    <td><?php echo $proveedor['id']; ?></td>
                    <td><?php echo htmlspecialchars($proveedor['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($proveedor['nif'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($proveedor['direccion'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($proveedor['telefono'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($proveedor['email'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($proveedor['updated_at'] ?? ''); ?></td>
                    <td class="text-center">
                        <a href="?tabla=proveedores&accion=editar&id=<?php echo $proveedor['id']; ?>" 
   class="inline-flex items-center justify-center p-2 text-amber-500 hover:text-amber-600 hover:bg-amber-50 rounded-full transition-colors duration-200 mr-2"
   title="Editar">
    <i class="fas fa-edit text-[1.1rem]"></i>
</a>
<a href="?tabla=proveedores&accion=eliminar&id=<?php echo $proveedor['id']; ?>" 
   class="inline-flex items-center justify-center p-2 text-red-500 hover:text-red-600 hover:bg-red-50 rounded-full transition-colors duration-200 mr-2"
   onclick="return confirm('¿Segur que vols eliminar?')" 
   title="Eliminar">
    <i class="fas fa-trash-alt text-[1.1rem]"></i>
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

<!-- Configuració de DataTables -->
<script>
$(document).ready(function() {
    $('#tablaProveedores').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ca.json' // Traducció al català
        },
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: true, targets: '_all' },
            { orderable: false, targets: -1 } // Desactivar ordenació a la columna d'accions
        ],
        searching: true,
        paging: true
    });
});
</script>

<?php include 'footer.php';?>	