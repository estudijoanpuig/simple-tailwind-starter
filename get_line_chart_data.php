<?php
include 'config.php';

if (isset($_POST['category_id'])) {
    $category_id = $_POST['category_id'];
    
    $sql = "SELECT DATE_FORMAT(c.fecha, '%Y-%m') AS mes, SUM(dc.subtotal) AS total
            FROM wp_contabilidad_compras c
            JOIN wp_contabilidad_detalles_compra dc ON c.id = dc.compra_id
            JOIN wp_contabilidad_productos p ON dc.producto_id = p.id
            WHERE p.id_categoria_producto = :category_id
            GROUP BY mes
            ORDER BY mes";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['category_id' => $category_id]);
    $dades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $labels = array_column($dades, 'mes');
    $totals = array_map('floatval', array_column($dades, 'total'));

    echo json_encode([
        'labels' => $labels,
        'data' => $totals
    ]);
}
?>