<?php
session_start();
require_once '../config/database.php';

$pdo = conectarDB();

$productoId = isset($_POST['producto_id']) ? (int) $_POST['producto_id'] : 0;
$cantidad = isset($_POST['cantidad']) ? (int) $_POST['cantidad'] : 1;

if ($productoId <= 0 || $cantidad <= 0) {
    header('Location: /catalogo.php');
    exit;
}

$sql = "SELECT * FROM productos WHERE id = :id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $productoId, PDO::PARAM_INT);
$stmt->execute();

$producto = $stmt->fetch();

if (!$producto) {
    header('Location: /catalogo.php');
    exit;
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
        'id' => $producto['id'],
        'nombre' => $producto['nombre'],
        'precio' => $producto['precio'],
        'imagen' => $producto['imagen'],
        'stock' => $producto['stock'],
        'cantidad' => $cantidad
    ];
}

header('Location: /carrito.php');
exit;