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

unset($_SESSION['carrito']);

redirect('/proyecto_cava_Noble/carrito.php');