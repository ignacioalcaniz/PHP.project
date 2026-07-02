<?php
require_once '../includes/session.php';
require_once '../includes/security.php';

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

$productoId = (int)($_POST['producto_id'] ?? 0);

if ($productoId > 0 && isset($_SESSION['carrito'][$productoId])) {
    unset($_SESSION['carrito'][$productoId]);
}

redirect('/proyecto_cava_Noble/carrito.php');