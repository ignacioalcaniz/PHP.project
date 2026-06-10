<?php
require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../config/database.php';

requireAdmin();

if (!isPostRequest()) {
    redirect('/proyecto_cava_Noble/admin/usuarios.php');
}

if (
    !isset($_POST['csrf_token']) ||
    !validateCsrfToken($_POST['csrf_token'])
) {
    die('Token CSRF inválido.');
}

$pdo = conectarDB();

$usuarioId = (int)($_POST['usuario_id'] ?? 0);
$rol = trim($_POST['rol'] ?? '');

$rolesPermitidos = ['cliente', 'admin'];

if ($usuarioId <= 0 || !in_array($rol, $rolesPermitidos, true)) {
    redirect('/proyecto_cava_Noble/admin/usuarios.php');
}

if ($usuarioId === (int)$_SESSION['usuario_id'] && $rol !== 'admin') {
    die('No podés quitarte permisos de administrador a vos mismo.');
}

$sql = "
    UPDATE usuarios
    SET rol = :rol
    WHERE id = :id
";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':rol', $rol);
$stmt->bindParam(':id', $usuarioId, PDO::PARAM_INT);
$stmt->execute();

redirect('/proyecto_cava_Noble/admin/detalle-usuario.php?id=' . $usuarioId);