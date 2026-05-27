<?php
require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../config/database.php';

requireAdmin();

if (!isPostRequest()) {
    redirect('/proyecto_cava_Noble/admin/pedidos.php');
}

if (
    !isset($_POST['csrf_token']) ||
    !validateCsrfToken($_POST['csrf_token'])
) {
    die('Token CSRF inválido.');
}

$pdo = conectarDB();

$pedidoId = (int)($_POST['pedido_id'] ?? 0);
$estado = trim($_POST['estado'] ?? '');

$estadosPermitidos = [
    'pendiente',
    'confirmado',
    'preparado',
    'entregado',
    'cancelado'
];

if ($pedidoId <= 0 || !in_array($estado, $estadosPermitidos, true)) {
    redirect('/proyecto_cava_Noble/admin/pedidos.php');
}

$sql = "
    UPDATE pedidos
    SET estado = :estado
    WHERE id = :id
";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':estado', $estado);
$stmt->bindParam(':id', $pedidoId, PDO::PARAM_INT);
$stmt->execute();

redirect('/proyecto_cava_Noble/admin/detalle-pedido.php?id=' . $pedidoId);