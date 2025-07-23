<?php
// config.php
$host = 'localhost';
$dbname = 'autonomo_contabilidad';
$username = 'joan';
$password = 'queMm88/g62123';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de connexió: " . $e->getMessage());
}
?>