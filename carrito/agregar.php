<?php
require_once '../includes/security.php';
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isPostRequest()) {
    redirect('/proyecto_cava_Noble/pages/catalogo.php');
}

if (
    !isset($_POST['csrf_token']) ||
    !validateCsrfToken($_POST['csrf_token'])
) {
    die('Token CSRF inválido.');
}

$pdo = conectarDB();

$productoId = (int)($_POST['producto_id'] ?? 0);
$cantidad = (int)($_POST['cantidad'] ?? 1);

if ($productoId <= 0 || $cantidad <= 0) {
    redirect('/proyecto_cava_Noble/pages/catalogo.php');
}

$sql = "SELECT id, stock FROM productos WHERE id = :id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $productoId, PDO::PARAM_INT);
$stmt->execute();

$producto = $stmt->fetch();

if (!$producto || (int)$producto['stock'] <= 0) {
    redirect('/proyecto_cava_Noble/pages/catalogo.php');
}

if ($cantidad > (int)$producto['stock']) {
    $cantidad = (int)$producto['stock'];
}

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

if (isset($_SESSION['carrito'][$productoId])) {
    $_SESSION['carrito'][$productoId]['cantidad'] += $cantidad;

    if ($_SESSION['carrito'][$productoId]['cantidad'] > (int)$producto['stock']) {
        $_SESSION['carrito'][$productoId]['cantidad'] = (int)$producto['stock'];
    }
} else {
    $_SESSION['carrito'][$productoId] = [
        'cantidad' => $cantidad
    ];
}

redirect('/proyecto_cava_Noble/carrito.php');