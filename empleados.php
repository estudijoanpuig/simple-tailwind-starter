<?php include 'head.php';?>
<!-- Content -->
<main class="p-6">
    <h1 class="mb-2 text-4xl font-extrabold leading-none tracking-normal text-gray-900 md:text-6xl md:tracking-tight text-center">
        Gestio<span class="block w-full py-2 text-transparent bg-clip-text leading-12 bg-gradient-to-r from-green-400 to-purple-500 lg:inline">Empleats</span>
    </h1>
    <script>
        // Copia el text del <h1> al <title>
        document.title = document.querySelector('h1').textContent;
    </script>

<?php
// _empleados.php

// Crear o actualizar empleado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $nombre = trim($_POST['nombre']);
    $nif = trim($_POST['nif']);
    $direccion = trim($_POST['direccion']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);
    $fecha_contratacion = $_POST['fecha_contratacion'] ?: null;

    // Validaciones básicas
    if (empty($nombre) || empty($nif)) {
        echo "<div class='alert alert-danger'>Nom i NIF són obligatoris.</div>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)) {
        echo "<div class='alert alert-danger'>Email no vàlid.</div>";
    } else {
        try {
            if ($id) {
                // Actualizar
                $sql = "UPDATE wp_contabilidad_empleados SET nombre = ?, nif = ?, direccion = ?, telefono = ?, email = ?, fecha_contratacion = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre, $nif, $direccion, $telefono, $email, $fecha_contratacion, $id]);
                echo "<div class='alert alert-success'>Empleat actualitzat correctament.</div>";
            } else {
                // Crear
                $sql = "INSERT INTO wp_contabilidad_empleados (nombre, nif, direccion, telefono, email, fecha_contratacion) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre, $nif, $direccion, $telefono, $email, $fecha_contratacion]);
                echo "<div class='alert alert-success'>Empleat creat correctament.</div>";
            }
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    }
}

// Eliminar empleado
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $sql = "DELETE FROM wp_contabilidad_empleados WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        echo "<div class='alert alert-success'>Empleat eliminat correctament.</div>";
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error al eliminar: " . $e->getMessage() . "</div>";
    }
}

// Obtener lista de empleados
try {
    $sql = "SELECT * FROM wp_contabilidad_empleados";
    $stmt = $pdo->query($sql);
    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error al carregar empleats: " . $e->getMessage() . "</div>";
    $empleados = [];
}

// Obtener datos para edición
$empleado_editar = null;
if (isset($_GET['accion']) && $_GET['accion'] === 'editar' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $sql = "SELECT * FROM wp_contabilidad_empleados WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $empleado_editar = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error al carregar empleat: " . $e->getMessage() . "</div>";
    }
}
?>

<div class="container-fluid m-1" style="margin: 5px !important;">
    <h2>Gestió d'Empleats</h2>
    
    <!-- Formulario para crear/actualizar -->
<form method="POST" class="mb-4">
    <input type="hidden" name="id" value="<?php echo $empleado_editar ? $empleado_editar['id'] : ''; ?>">
    <div class="mb-4">
        <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" name="nombre" value="<?php echo $empleado_editar ? htmlspecialchars($empleado_editar['nombre']) : ''; ?>" required>
    </div>
    <div class="mb-4">
        <label for="nif" class="block text-sm font-medium text-gray-700 mb-1">NIF</label>
        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" name="nif" value="<?php echo $empleado_editar ? htmlspecialchars($empleado_editar['nif']) : ''; ?>" required>
    </div>
    <div class="mb-4">
        <label for="direccion" class="block text-sm font-medium text-gray-700 mb-1">Adreça</label>
        <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" name="direccion"><?php echo $empleado_editar ? htmlspecialchars($empleado_editar['direccion']) : ''; ?></textarea>
    </div>
    <div class="mb-4">
        <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">Telèfon</label>
        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" name="telefono" value="<?php echo $empleado_editar ? htmlspecialchars($empleado_editar['telefono']) : ''; ?>">
    </div>
    <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input type="email" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" name="email" value="<?php echo $empleado_editar ? htmlspecialchars($empleado_editar['email']) : ''; ?>">
    </div>
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
        <?php echo $empleado_editar ? 'Actualitzar' : 'Crear'; ?> Empleat
    </button>
</form>

    <!-- Tabla de empleados con DataTables -->
    <table id="tablaDatos" class="display nowrap" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>NIF</th>
                <th>Telèfon</th>
                <th>Email</th>
                <th>Accions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($empleados as $empleado): ?>
                <tr>
                    <td><?php echo $empleado['id']; ?></td>
                    <td><?php echo htmlspecialchars($empleado['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($empleado['nif']); ?></td>
                    <td><?php echo htmlspecialchars($empleado['telefono'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($empleado['email'] ?? ''); ?></td>
                    <td class="text-center">
                        <a href="?tabla=empleados&accion=editar&id=<?php echo $empleado['id']; ?>" class="text-orange-400 hover:text-orange-600 mr-2" title="Editar">
    <i class="fas fa-edit"></i>
</a>
<a href="?tabla=empleados&accion=eliminar&id=<?php echo $empleado['id']; ?>" 
   class="text-red-500 hover:text-red-600 mr-2"
   onclick="return confirm('¿Segur que vols eliminar?')" title="Eliminar">
    <i class="fas fa-trash-alt"></i>
</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Afegir estils per a les icones -->
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
    $('#tablaDatos').DataTable({
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