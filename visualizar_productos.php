<?php include 'head.php';?>
<!-- Content -->
<main class="p-6">
    <h1 class="mb-2 text-4xl font-extrabold leading-none tracking-normal text-gray-900 md:text-6xl md:tracking-tight text-center">
        galeria <span class="block w-full py-2 text-transparent bg-clip-text leading-12 bg-gradient-to-r from-green-400 to-purple-500 lg:inline">productes</span>
    </h1>
    <script>
        document.title = document.querySelector('h1').textContent;
    </script>		

<?php
// visualizar_productos.php
// Asumim que $pdo està definit a config.php, ja que es carrega via index.php

// Obtener parámetros de filtrado y búsqueda
$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
$query = isset($_GET['query']) ? trim($_GET['query']) : null;

// Variables de paginació
$postsPerPage = 15;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $postsPerPage;

// Obtener categorías (todas, pero ocultaremos la 17 en el display)
try {
    $sql = "SELECT id, nombre_categoria FROM wp_contabilidad_categoria_productos ORDER BY nombre_categoria ASC";
    $stmt = $pdo->query($sql);
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categorias = [];
    echo "<div class='alert alert-danger'>Error al carregar categories: " . $e->getMessage() . "</div>";
}

// Obtener productos con filtros, paginación y exclusión de categoría 17
$sql = "SELECT SQL_CALC_FOUND_ROWS p.*, c.nombre_categoria 
        FROM wp_contabilidad_productos p 
        LEFT JOIN wp_contabilidad_categoria_productos c ON p.id_categoria_producto = c.id";

$conditions = [];
$params = [];
$conditions[] = "p.id_categoria_producto != 17";

if ($categoryId) {
    $conditions[] = "p.id_categoria_producto = :category_id";
    $params[':category_id'] = $categoryId;
}
if ($query) {
    $conditions[] = "(p.nombre LIKE :query OR c.nombre_categoria LIKE :query)";
    $params[':query'] = "%$query%";
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}
$sql .= " ORDER BY p.nombre ASC LIMIT :limit OFFSET :offset";

try {
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $postsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalProductos = $pdo->query("SELECT FOUND_ROWS() as total")->fetch()['total'];
} catch (PDOException $e) {
    $productos = [];
    $totalProductos = 0;
    echo "<div class='alert alert-danger'>Error al carregar productes: " . $e->getMessage() . "</div>";
}

$totalPages = ceil($totalProductos / $postsPerPage);
?>

<!-- Hero -->
<div class="bg-gray-50 py-12">
    <div class="container mx-auto px-4">
        <div class="text-center">
            <p class="text-gray-500 italic">Amb filtres, cerca i paginació (excloent categoria 17).</p>
            <div class="mt-6 mx-auto max-w-md">
                <form method="GET" action="?tabla=galeria" class="flex">
                    <input type="text" 
                           name="query" 
                           placeholder="Cercar producte..." 
                           value="<?php echo htmlspecialchars($query ?? ''); ?>" 
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-l-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all"
                           aria-label="Cercar producte">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-r-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            <div class="mt-6 flex flex-wrap justify-center gap-2">
                <a href="?tabla=galeria" 
                   class="px-4 py-2 rounded-full <?php echo !$categoryId ? 'bg-blue-600 text-white' : 'bg-white text-blue-600 border border-blue-600'; ?> hover:bg-blue-100 transition-colors">
                    Tots
                </a>
                <?php foreach ($categorias as $categoria): ?>
                    <?php if ($categoria['id'] == 17) continue; ?>
                    <a href="?tabla=galeria&category_id=<?php echo $categoria['id']; ?>" 
                       class="px-4 py-2 rounded-full <?php echo $categoryId == $categoria['id'] ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-300'; ?> hover:bg-gray-100 transition-colors">
                        <?php echo htmlspecialchars($categoria['nombre_categoria']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Galería Masonry -->
<div class="container mx-auto p-2">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php foreach ($productos as $producto): ?>
            <div class="border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                <?php if ($producto['img'] && file_exists('img/' . $producto['img'])): ?>
                    <a data-fancybox="gallery" href="img/<?php echo htmlspecialchars($producto['img']); ?>">
                        <img src="img/<?php echo htmlspecialchars($producto['img']); ?>" 
                             alt="<?php echo htmlspecialchars($producto['nombre']); ?>" 
                             class="w-full h-48 object-cover">
                    </a>
                <?php else: ?>
                    <div class="bg-gray-200 text-gray-600 flex items-center justify-center h-48">
                        Imagen no disponible
                    </div>
                <?php endif; ?>
                <div class="p-4">
                    <h2 class="text-lg font-semibold mb-2">
                        <a href="producto_detalle.php?&id=<?php echo $producto['id']; ?>" 
                           class="text-blue-600 hover:text-blue-800 hover:underline">
                            <?php echo htmlspecialchars($producto['nombre']); ?>
                        </a>
                    </h2>
                    <p class="text-gray-500 text-sm mb-3">
                        <?php 
                        if ($producto['descripcion']) {
                            $text = strip_tags($producto['descripcion']);
                            $lines = explode("\n", $text);
                            $firstTwoLines = implode("\n", array_slice($lines, 0, 2));
                            echo htmlspecialchars(substr($firstTwoLines, 0, 100)) . (strlen($firstTwoLines) > 100 ? '...' : '');
                        } else {
                            echo 'Sin descripción';
                        }
                        ?>
                    </p>
                    <p class="text-sm mb-2">
                        <span class="font-medium">Precio:</span> <?php echo $producto['precio'] !== null ? number_format($producto['precio'], 2, ',', '.') . ' €' : 'No definido'; ?>
                    </p>
                    <p class="text-sm mb-3"><span class="font-medium">Stock:</span> <?php echo htmlspecialchars($producto['stock']); ?></p>
                    <p class="text-sm mb-4">
                        <span class="inline-block bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">
                            <?php echo htmlspecialchars($producto['nombre_categoria'] ?? 'Sin categoría'); ?>
                        </span>
                    </p>
                    <div class="flex gap-3">
                        <?php if ($producto['img']): ?>
                            <a href="img/<?php echo htmlspecialchars($producto['img']); ?>" 
                               data-fancybox="gallery" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-image"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($producto['video'] && file_exists('video/' . $producto['video'])): ?>
                            <a href="video/<?php echo htmlspecialchars($producto['video']); ?>" 
                               data-fancybox="gallery" class="text-red-600 hover:text-red-800">
                                <i class="fas fa-video"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Paginación -->
    <nav class="flex justify-center mt-8">
        <ul class="flex items-center gap-1">
            <?php if ($currentPage > 1): ?>
                <li>
                    <a class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100 transition-colors"
                       href="?<?php echo http_build_query(array_merge($_GET, ['page' => $currentPage - 1])); ?>">
                        Anterior
                    </a>
                </li>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li>
                    <a class="px-3 py-1 border rounded <?php echo $i == $currentPage ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 hover:bg-gray-100'; ?> transition-colors"
                       href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>
            <?php if ($currentPage < $totalPages): ?>
                <li>
                    <a class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100 transition-colors"
                       href="?<?php echo http_build_query(array_merge($_GET, ['page' => $currentPage + 1])); ?>">
                        Siguiente
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
<?php include 'footer.php';?>	