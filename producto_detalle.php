<?php include 'head.php';?>
<!-- Content -->
<main class="p-6">
    <h1 class="mb-2 text-4xl font-extrabold leading-none tracking-normal text-gray-900 md:text-6xl md:tracking-tight text-center">
        detall <span class="block w-full py-2 text-transparent bg-clip-text leading-12 bg-gradient-to-r from-green-400 to-purple-500 lg:inline">producte</span>
    </h1>
    <script>
        document.title = document.querySelector('h1').textContent;
    </script>

<?php
// producto_detalle.php

// Utilitzem la connexió $pdo ja definida a config.php
global $pdo;

$producto_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($producto_id === 0) {
    die("Error: Producte no trobat.");
}

try {
    $sql = "SELECT p.id, p.nombre, p.descripcion, p.img, p.video, p.protocol, p.precio, p.stock, p.created_at, p.updated_at, 
            COALESCE(p.id_categoria_producto, 0) AS id_categoria_producto, c.nombre_categoria 
            FROM wp_contabilidad_productos p
            LEFT JOIN wp_contabilidad_categoria_productos c ON p.id_categoria_producto = c.id
            WHERE p.id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $producto_id, PDO::PARAM_INT);
    $stmt->execute();
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        die("Error: Producte no trobat.");
    }

    // Obtenir el producte anterior
    $sql_previous = "SELECT id, nombre FROM wp_contabilidad_productos WHERE id < :id ORDER BY id DESC LIMIT 1";
    $stmt_previous = $pdo->prepare($sql_previous);
    $stmt_previous->bindParam(':id', $producto_id, PDO::PARAM_INT);
    $stmt_previous->execute();
    $previousProducto = $stmt_previous->fetch(PDO::FETCH_ASSOC);

    // Obtenir el producte següent
    $sql_next = "SELECT id, nombre FROM wp_contabilidad_productos WHERE id > :id ORDER BY id ASC LIMIT 1";
    $stmt_next = $pdo->prepare($sql_next);
    $stmt_next->bindParam(':id', $producto_id, PDO::PARAM_INT);
    $stmt_next->execute();
    $nextProducto = $stmt_next->fetch(PDO::FETCH_ASSOC);

    // Comprovació i assignació segura de id_categoria_producto
    $category_id = $producto['id_categoria_producto'] ?? 0;
    if ($category_id === 0) {
        echo "<!-- Avís: id_categoria_producto no definit per a producte ID = $producto_id, usant 0 com a valor per defecte -->";
    }

    // Obtenir productes relacionats (lògica ajustada per a categoria 17)
    $exclude_category_17 = ($category_id != 17) ? "AND id_categoria_producto != 17" : "";
    $related_sql = "SELECT id, nombre, descripcion, precio, stock, img, created_at 
                    FROM wp_contabilidad_productos 
                    WHERE id_categoria_producto = :category_id AND id != :id $exclude_category_17 
                    ORDER BY created_at DESC 
                    LIMIT 6";
    $stmt_related = $pdo->prepare($related_sql);
    $stmt_related->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    $stmt_related->bindParam(':id', $producto_id, PDO::PARAM_INT);
    $stmt_related->execute();
    $related_products = $stmt_related->fetchAll(PDO::FETCH_ASSOC);

    // Depuració millorada
    $debug_msg = "Categoria ID = " . ($category_id ?? 'null') . 
                 ", Producte ID = $producto_id, Total relacionats = " . count($related_products) . 
                 ", Excloure 17 = " . ($exclude_category_17 ? "Sí" : "No");
    echo "<!-- Depuració: $debug_msg -->";
    if (count($related_products) == 0) {
        echo "<!-- Avís: No s'han trobat productes amb id_categoria_producto = $category_id excepte id = $producto_id -->";
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<div class="max-w-[85rem] px-4 sm:px-6 lg:px-8 mx-auto">
    <div class="grid lg:grid-cols-3 gap-y-8 lg:gap-y-0 lg:gap-x-6">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <div class="py-8 lg:pe-8">
                <div class="space-y-5 lg:space-y-8">
                    <a class="inline-flex items-center gap-x-1.5 text-sm text-gray-600 decoration-2 hover:underline focus:outline-none focus:underline" href="visualizar_productos.php">
                        <i class="fas fa-arrow-left"></i> Tornar a Productes
                    </a>

                    <h2 class="text-3xl font-bold lg:text-5xl"><?= htmlspecialchars($producto['nombre']); ?></h2>

                    <div class="flex items-center gap-x-5">
                        <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full text-xs bg-gray-100 text-gray-800">
                            <?= htmlspecialchars($producto['nombre_categoria']); ?>
                        </span>
                        <p class="text-xs text-gray-800">
                            <?= date("d/m/Y", strtotime($producto['created_at'])); ?>
                        </p>
                    </div>

                    <?php if (!empty($producto['img'])) : ?>
                        <figure class="relative w-full h-auto">
                            <img class="w-full max-h-[500px] object-cover rounded-xl" 
                                 src="img/<?= htmlspecialchars($producto['img']); ?>" 
                                 alt="<?= htmlspecialchars($producto['nombre']); ?>">
                        </figure>
                    <?php endif; ?>

                    <div class="prose max-w-none">
                        <?= htmlspecialchars_decode($producto['descripcion']); ?>
                    </div>

                    <div class="space-y-2">
                        <p><strong>Preu:</strong> €<?php echo ($producto['precio'] !== null) ? number_format($producto['precio'], 2, ',', '.') : 'No definit'; ?></p>
                        <p><strong>Stock:</strong> <?= htmlspecialchars($producto['stock']); ?> unitats</p>
                    </div>

                    <?php if (!empty($producto['video'])) : ?>
                        <div class="mt-4">
                            <h3 class="text-xl font-semibold mb-2">Video del Producte</h3>
                            <a data-fancybox="gallery" href="video/<?= htmlspecialchars($producto['video']); ?>" 
                               class="inline-flex items-center text-blue-600 hover:text-blue-800">
                                <i class="fab fa-youtube mr-2 text-red-600"></i> Veure Video
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- Product Navigation -->
                    <div class="flex flex-col sm:flex-row justify-between gap-4 mt-8 pt-6 border-t border-gray-200">
                        <?php if ($previousProducto): ?>
                            <a href="?tabla=galeria_detalle&id=<?= $previousProducto['id'] ?>" 
                               class="inline-flex items-center text-blue-600 hover:underline">
                                <i class="fas fa-chevron-left mr-2"></i>
                                <span class="truncate max-w-[180px]"><?= htmlspecialchars($previousProducto['nombre']) ?></span>
                            </a>
                        <?php else: ?>
                            <span class="text-gray-500">No hi ha producte anterior</span>
                        <?php endif; ?>

                        <?php if ($nextProducto): ?>
                            <a href="?tabla=galeria_detalle&id=<?= $nextProducto['id'] ?>" 
                               class="inline-flex items-center text-blue-600 hover:underline ml-auto">
                                <span class="truncate max-w-[180px]"><?= htmlspecialchars($nextProducto['nombre']) ?></span>
                                <i class="fas fa-chevron-right ml-2"></i>
                            </a>
                        <?php else: ?>
                            <span class="text-gray-500 ml-auto">No hi ha producte següent</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 lg:w-full lg:h-full lg:bg-gradient-to-r lg:from-gray-50 lg:via-transparent lg:to-transparent">
            <div class="sticky top-0 start-0 py-8 lg:ps-8">
                <!-- Author Info -->
                <div class="group flex items-center gap-x-3 border-b border-gray-200 pb-8 mb-8">
                    <img class="w-[50px] h-[50px] rounded-full" src="img/logo.png" alt="Autor">
                    <div>
                        <h5 class="text-sm font-semibold text-gray-800">Admin</h5>
                        <p class="text-sm text-gray-500">Gestor d'inventari</p>
                    </div>
                </div>

                <!-- Related Products -->
                <h3 class="text-lg font-semibold mb-3">Productes Relacionats</h3>
                <div class="space-y-4">
                    <?php if (count($related_products) > 0) : ?>
                        <?php foreach ($related_products as $related) : ?>
                            <a class="group flex gap-x-4 p-3 bg-white rounded-lg shadow-md hover:bg-gray-100 transition" 
                               href="?tabla=galeria_detalle&id=<?= $related['id']; ?>">
                                <div class="relative w-20 h-20 rounded-lg overflow-hidden shrink-0">
                                    <img class="size-full absolute top-0 start-0 object-cover rounded-lg" 
                                         src="img/<?= htmlspecialchars($related['img']); ?>" 
                                         alt="<?= htmlspecialchars($related['nombre']); ?>">
                                </div>
                                <div class="flex flex-col justify-center px-2 w-full">
                                    <span class="block text-sm font-semibold text-gray-800 leading-tight">
                                        <?= htmlspecialchars($related['nombre']); ?>
                                    </span>
                                    <p class="text-xs text-gray-600 mt-1">
                                        Preu: €<?php echo ($related['precio'] !== null) ? number_format($related['precio'], 2, ',', '.') : 'No definit'; ?>
                                    </p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-sm text-gray-600">No hi ha productes relacionats.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php';?>	