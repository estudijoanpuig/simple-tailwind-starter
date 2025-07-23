<?php include 'head.php';?>

<!-- Content -->
<main class="p-6">
    <h1 class="mb-2 text-4xl font-extrabold leading-none tracking-normal text-gray-900 md:text-6xl md:tracking-tight text-center">
        Gestió <span class="block w-full py-2 text-transparent bg-clip-text leading-12 bg-gradient-to-r from-green-400 to-purple-500 lg:inline">productes</span>
    </h1>
    <script>
        document.title = document.querySelector('h1').textContent;
    </script>		

<?php
// Configuración de carpetas
$upload_dir_img = 'img/';
$upload_dir_video = 'video/';

// Crear o actualizar producto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $nombre = trim($_POST['nombre']);
    $id_categoria_producto = (int)$_POST['id_categoria_producto'];
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : null;
    $protocol = isset($_POST['protocol']) ? trim($_POST['protocol']) : null;
    $precio = isset($_POST['precio']) && $_POST['precio'] !== '' ? floatval($_POST['precio']) : null;
    $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;

    // Validaciones adicionales
    if ($precio !== null && $precio < 0) {
        echo "<div class='alert alert-danger'>El preu no pot ser negatiu.</div>";
    } elseif ($stock < 0) {
        echo "<div class='alert alert-danger'>L'estoc no pot ser negatiu.</div>";
    } else {
        // Inicializamos img y video con los valores actuales (si existen)
        $img = null;
        $video = null;
        if ($id) {
            // Obtener los valores actuales de img y video si estamos actualizando
            $sql = "SELECT img, video FROM wp_contabilidad_productos WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $producto_actual = $stmt->fetch(PDO::FETCH_ASSOC);
            $img = $producto_actual['img'] ?? null;
            $video = $producto_actual['video'] ?? null;
        }

        // Manejo de subida de archivos
        if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
            $img_file = $_FILES['img'];
            $img_name = time() . '_' . basename($img_file['name']);
            $img_path = $upload_dir_img . $img_name;
            if (move_uploaded_file($img_file['tmp_name'], $img_path)) {
                $img = $img_name;
            } else {
                echo "<div class='alert alert-danger'>Error al subir la imagen.</div>";
            }
        }

        if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
            $video_file = $_FILES['video'];
            $video_name = time() . '_' . basename($video_file['name']);
            $video_path = $upload_dir_video . $video_name;
            if (move_uploaded_file($video_file['tmp_name'], $video_path)) {
                $video = $video_name;
            } else {
                echo "<div class='alert alert-danger'>Error al subir el video.</div>";
            }
        }

        // Validaciones básicas
        if (empty($nombre)) {
            echo "<div class='alert alert-danger'>El nom és obligatori.</div>";
        } elseif (empty($id_categoria_producto)) {
            echo "<div class='alert alert-danger'>La categoria és obligatòria.</div>";
        } else {
            try {
                $pdo->beginTransaction();

                if ($id) {
                    // Actualizar
                    $sql = "UPDATE wp_contabilidad_productos SET nombre = ?, id_categoria_producto = ?, descripcion = ?, protocol = ?, precio = ?, stock = ?, img = ?, video = ? WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$nombre, $id_categoria_producto, $descripcion, $protocol, $precio, $stock, $img, $video, $id]);
                } else {
                    // Crear
                    $sql = "INSERT INTO wp_contabilidad_productos (nombre, id_categoria_producto, descripcion, protocol, precio, stock, img, video) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$nombre, $id_categoria_producto, $descripcion, $protocol, $precio, $stock, $img, $video]);
                    $id = $pdo->lastInsertId();
                }

                $pdo->commit();
                echo "<div class='alert alert-success'>Producte " . ($id ? 'actualitzat' : 'creat') . " correctament.</div>";
            } catch (PDOException $e) {
                $pdo->rollBack();
                echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
            }
        }
    }
}

// Eliminar producto
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $pdo->beginTransaction();

        // Obtener la ruta de los archivos antes de eliminar
        $sql = "SELECT img, video FROM wp_contabilidad_productos WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        // Eliminar archivos si existen
        if ($producto['img'] && file_exists($upload_dir_img . $producto['img'])) {
            unlink($upload_dir_img . $producto['img']);
        }
        if ($producto['video'] && file_exists($upload_dir_video . $producto['video'])) {
            unlink($upload_dir_video . $producto['video']);
        }

        // Eliminar el producto
        $sql = "DELETE FROM wp_contabilidad_productos WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        $pdo->commit();
        echo "<div class='alert alert-success'>Producte eliminat correctament.</div>";
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "<div class='alert alert-danger'>Error al eliminar: " . $e->getMessage() . "</div>";
    }
}

// Obtener lista de productos
try {
    $sql = "SELECT p.*, c.nombre_categoria FROM wp_contabilidad_productos p LEFT JOIN wp_contabilidad_categoria_productos c ON p.id_categoria_producto = c.id";
    $stmt = $pdo->query($sql);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error al carregar productes: " . $e->getMessage() . "</div>";
    $productos = [];
}

// Obtener datos para edición
$producto_editar = null;
if (isset($_GET['accion']) && $_GET['accion'] === 'editar' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $sql = "SELECT * FROM wp_contabilidad_productos WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $producto_editar = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$producto_editar) {
            echo "<div class='alert alert-danger'>Producte no trobat.</div>";
            $producto_editar = null;
        }
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error al carregar producte: " . $e->getMessage() . "</div>";
        $producto_editar = null;
    }
}

// Obtener lista de categorías para el desplegable
try {
    $sql = "SELECT * FROM wp_contabilidad_categoria_productos ORDER BY nombre_categoria ASC";
    $stmt = $pdo->query($sql);
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error al carregar categories: " . $e->getMessage() . "</div>";
    $categorias = [];
}
?>

<div class="m-1 datatables-native" style="margin: 5px !important;">
    <h2 class="text-xl font-bold mb-4">Gestió de Productes</h2>

    <!-- Formulari per crear/actualitzar -->
    <form method="POST" class="mb-4 space-y-4" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $producto_editar && isset($producto_editar['id']) ? htmlspecialchars($producto_editar['id'] ?? '') : ''; ?>">
        
        <div class="mb-3">
            <label for="nombre" class="block mb-1 text-sm font-medium text-gray-700">Nom</label>
            <input type="text" name="nombre"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                   value="<?php echo $producto_editar && isset($producto_editar['nombre']) ? htmlspecialchars($producto_editar['nombre'] ?? '') : ''; ?>" required>
        </div>

        <div class="mb-3">
            <label for="id_categoria_producto" class="block mb-1 text-sm font-medium text-gray-700">Categoria</label>
            <select name="id_categoria_producto" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Selecciona una categoria</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?php echo $categoria['id']; ?>" <?php echo ($producto_editar && isset($producto_editar['id_categoria_producto']) && $producto_editar['id_categoria_producto'] == $categoria['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($categoria['nombre_categoria'] ?? ''); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="descripcion" class="block mb-1 text-sm font-medium text-gray-700">Descripció</label>
            <input id="descripcion" type="hidden" name="descripcion" value="<?php echo $producto_editar && isset($producto_editar['descripcion']) ? htmlspecialchars($producto_editar['descripcion'] ?? '') : ''; ?>">
            <trix-editor input="descripcion" class="trix-content"></trix-editor>
        </div>

        <div class="mb-3">
            <label for="protocol" class="block mb-1 text-sm font-medium text-gray-700">Protocol</label>
            <input id="protocol" type="hidden" name="protocol" value="<?php echo $producto_editar && isset($producto_editar['protocol']) ? htmlspecialchars($producto_editar['protocol'] ?? '') : ''; ?>">
            <trix-editor input="protocol" class="trix-content"></trix-editor>
        </div>

        <div class="mb-3">
            <label for="precio" class="block mb-1 text-sm font-medium text-gray-700">Preu (€)</label>
            <input type="number" step="0.01" name="precio"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                   value="<?php echo $producto_editar && isset($producto_editar['precio']) && $producto_editar['precio'] !== null ? number_format($producto_editar['precio'], 2, '.', '') : ''; ?>">
        </div>

        <div class="mb-3">
            <label for="stock" class="block mb-1 text-sm font-medium text-gray-700">Stock</label>
            <input type="number" name="stock"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                   value="<?php echo $producto_editar && isset($producto_editar['stock']) ? htmlspecialchars($producto_editar['stock'] ?? '0') : '0'; ?>" required>
        </div>

        <div class="mb-3">
            <label for="img" class="block mb-1 text-sm font-medium text-gray-700">Imatge</label>
            <input type="file" name="img" accept="image/*"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <?php if ($producto_editar && isset($producto_editar['img']) && $producto_editar['img']): ?>
                <p class="text-sm mt-1">Imatge actual: 
                    <a class="text-blue-500 underline" href="<?php echo $upload_dir_img . htmlspecialchars($producto_editar['img'] ?? ''); ?>" target="_blank">
                        <?php echo htmlspecialchars($producto_editar['img'] ?? ''); ?>
                    </a>
                </p>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="video" class="block mb-1 text-sm font-medium text-gray-700">Vídeo</label>
            <input type="file" name="video" accept="video/*"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <?php if ($producto_editar && isset($producto_editar['video']) && $producto_editar['video']): ?>
                <p class="text-sm mt-1">Vídeo actual: 
                    <a class="text-blue-500 underline" href="<?php echo $upload_dir_video . htmlspecialchars($producto_editar['video'] ?? ''); ?>" target="_blank">
                        <?php echo htmlspecialchars($producto_editar['video'] ?? ''); ?>
                    </a>
                </p>
            <?php endif; ?>
        </div>

        <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <?php echo $producto_editar ? 'Actualitzar' : 'Crear'; ?> Producte
        </button>
    </form>

    
        <table id="tablaProductos" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Categoria</th>
                    <th>Descripció</th>
                    <th>Protocol</th>
                    <th>Preu (€)</th>
                    <th>Stock</th>
                    <th>Imatge</th>
                    <th>Vídeo</th>
                    <th>Data Actualització</th>
                    <th>Accions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $producto): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($producto['id'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($producto['nombre'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($producto['nombre_categoria'] ?? 'Sense categoria'); ?></td>
                        <td><?php echo isset($producto['descripcion']) && $producto['descripcion'] !== null ? htmlspecialchars(substr(strip_tags($producto['descripcion']), 0, 50)) . (strlen(strip_tags($producto['descripcion'])) > 50 ? '...' : '') : ''; ?></td>
                        <td><?php echo isset($producto['protocol']) && $producto['protocol'] !== null ? htmlspecialchars(substr(strip_tags($producto['protocol']), 0, 50)) . (strlen(strip_tags($producto['protocol'])) > 50 ? '...' : '') : ''; ?></td>
                        <td><?php echo isset($producto['precio']) && $producto['precio'] !== null ? number_format($producto['precio'], 2, ',', '.') : ''; ?></td>
                        <td><?php echo htmlspecialchars($producto['stock'] ?? '0'); ?></td>
                        <td><?php echo isset($producto['img']) && $producto['img'] ? '<a data-fancybox="gallery" href="' . $upload_dir_img . htmlspecialchars($producto['img']) . '" target="_blank">' . htmlspecialchars($producto['img']) . '</a>' : ''; ?></td>
                        <td><?php echo isset($producto['video']) && $producto['video'] ? '<a data-fancybox="gallery" href="' . $upload_dir_video . htmlspecialchars($producto['video']) . '" target="_blank">' . htmlspecialchars($producto['video']) . '</a>' : ''; ?></td>
                        <td><?php echo htmlspecialchars($producto['updated_at'] ?? ''); ?></td>
                        <td class="text-center">
                            <a href="?tabla=productos&accion=editar&id=<?php echo htmlspecialchars($producto['id'] ?? ''); ?>" class="text-yellow-600 hover:text-yellow-900 mr-3" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?tabla=productos&accion=eliminar&id=<?php echo htmlspecialchars($producto['id'] ?? ''); ?>" class="text-red-500" 
                               onclick="return confirm('¿Segur que vols eliminar?')" title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
   
</div>


<?php include 'footer.php';?>