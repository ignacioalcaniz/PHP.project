<?php
require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../includes/rate-limit.php';
require_once '../includes/admin-log.php';
require_once '../config/database.php';

requireAdmin();

if (!isPostRequest()) {
    redirect('/proyecto_cava_Noble/admin/seguridad.php');
}

if (
    !isset($_POST['csrf_token']) ||
    !validateCsrfToken($_POST['csrf_token'])
) {
    die('Token CSRF inválido.');
}

$pdo = conectarDB();

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    redirect('/proyecto_cava_Noble/admin/seguridad.php');
}

$sql = "
    SELECT ip_address
    FROM suspicious_ips
    WHERE id = :id
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();

$ip = $stmt->fetch();

if (!$ip) {
    redirect('/proyecto_cava_Noble/admin/seguridad.php');
}

unblockSuspiciousIp($id);

createAdminLog(
    (int)$_SESSION['usuario_id'],
    'DESBLOQUEAR',
    'IP',
    $id,
    'IP desbloqueada manualmente: ' . $ip['ip_address']
);

redirect('/proyecto_cava_Noble/admin/seguridad.php');