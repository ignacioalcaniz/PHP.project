<?php
require_once '../includes/session.php';
require_once '../includes/security.php';
require_once '../config/database.php';

startSecureSession();

if (!isPostRequest()) {
    redirect('/proyecto_cava_Noble/carrito.php');
}

if (
    !isset($_POST['csrf_token']) ||
    !validateCsrfToken($_POST['csrf_token'])
) {
    die('Token CSRF inválido.');
}

$pdo = conectarDB();

$productoId = (int)($_POST['producto_id'] ?? 0);
$cantidad = (int)($_POST['cantidad'] ?? 0);

if ($productoId <= 0 || $cantidad <= 0) {
    redirect('/proyecto_cava_Noble/carrito.php');
}

$sql = "
    SELECT stock
    FROM productos
    WHERE id = :id
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $productoId, PDO::PARAM_INT);
$stmt->execute();

$producto = $stmt->fetch();

if (!$producto || !isset($_SESSION['carrito'][$productoId])) {
    redirect('/proyecto_cava_Noble/carrito.php');
}

$cantidad = min($cantidad, (int)$producto['stock']);

if ($cantidad <= 0) {
    unset($_SESSION['carrito'][$productoId]);
} else {
    $_SESSION['carrito'][$productoId]['cantidad'] = $cantidad;
}

redirect('/proyecto_cava_Noble/carrito.php');