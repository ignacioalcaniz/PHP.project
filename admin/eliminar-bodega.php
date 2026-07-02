<?php
require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../includes/admin-log.php';
require_once '../config/database.php';

requireAdmin();

if (!isPostRequest()) {
    redirect('/proyecto_cava_Noble/admin/bodegas.php');
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
    redirect('/proyecto_cava_Noble/admin/bodegas.php');
}

$sqlBodega = "
    SELECT
        b.*,
        COUNT(p.id) AS total_productos
    FROM bodegas b
    LEFT JOIN productos p ON p.bodega_id = b.id
    WHERE b.id = :id
    GROUP BY b.id, b.nombre, b.pais, b.region, b.descripcion, b.creado_en
    LIMIT 1
";

$stmtBodega = $pdo->prepare($sqlBodega);
$stmtBodega->bindParam(':id', $id, PDO::PARAM_INT);
$stmtBodega->execute();

$bodega = $stmtBodega->fetch();

if (!$bodega) {
    redirect('/proyecto_cava_Noble/admin/bodegas.php');
}

if ((int)$bodega['total_productos'] > 0) {
    die('No se puede eliminar una bodega con productos asociados.');
}

$sql = "
    DELETE FROM bodegas
    WHERE id = :id
";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();

createAdminLog(
    (int)$_SESSION['usuario_id'],
    'ELIMINAR',
    'BODEGA',
    $id,
    'Bodega eliminada: ' . $bodega['nombre']
);

redirect('/proyecto_cava_Noble/admin/bodegas.php');