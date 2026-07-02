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
$nombre = trim($_POST['nombre'] ?? '');
$pais = trim($_POST['pais'] ?? '');
$region = trim($_POST['region'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

if ($id <= 0 || $nombre === '' || $pais === '' || $region === '') {
    redirect('/proyecto_cava_Noble/admin/bodegas.php');
}

$sqlExiste = "
    SELECT id
    FROM bodegas
    WHERE nombre = :nombre
    AND pais = :pais
    AND region = :region
    AND id != :id
    LIMIT 1
";

$stmtExiste = $pdo->prepare($sqlExiste);
$stmtExiste->bindParam(':nombre', $nombre);
$stmtExiste->bindParam(':pais', $pais);
$stmtExiste->bindParam(':region', $region);
$stmtExiste->bindParam(':id', $id, PDO::PARAM_INT);
$stmtExiste->execute();

if ($stmtExiste->fetch()) {
    die('Ya existe otra bodega con ese nombre, país y región.');
}

$sql = "
    UPDATE bodegas
    SET
        nombre = :nombre,
        pais = :pais,
        region = :region,
        descripcion = :descripcion
    WHERE id = :id
";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':nombre', $nombre);
$stmt->bindParam(':pais', $pais);
$stmt->bindParam(':region', $region);
$stmt->bindParam(':descripcion', $descripcion);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();

/* Mantiene consistencia con productos existentes */
$sqlProductos = "
    UPDATE productos
    SET
        bodega = :nombre,
        pais = :pais,
        region = :region
    WHERE bodega_id = :id
";

$stmtProductos = $pdo->prepare($sqlProductos);
$stmtProductos->bindParam(':nombre', $nombre);
$stmtProductos->bindParam(':pais', $pais);
$stmtProductos->bindParam(':region', $region);
$stmtProductos->bindParam(':id', $id, PDO::PARAM_INT);
$stmtProductos->execute();

createAdminLog(
    (int)$_SESSION['usuario_id'],
    'EDITAR',
    'BODEGA',
    $id,
    'Bodega editada: ' . $nombre
);

redirect('/proyecto_cava_Noble/admin/bodegas.php');