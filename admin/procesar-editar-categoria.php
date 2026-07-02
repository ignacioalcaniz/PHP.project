<?php
require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../includes/admin-log.php';
require_once '../config/database.php';

requireAdmin();

if (!isPostRequest()) {
    redirect('/proyecto_cava_Noble/admin/categorias.php');
}

if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
    die('Token CSRF inválido.');
}

$pdo = conectarDB();

$id = (int)($_POST['id'] ?? 0);
$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

if ($id <= 0 || $nombre === '') {
    redirect('/proyecto_cava_Noble/admin/categorias.php');
}

$sqlExiste = "
    SELECT id
    FROM categorias
    WHERE nombre = :nombre
    AND id != :id
    LIMIT 1
";

$stmtExiste = $pdo->prepare($sqlExiste);
$stmtExiste->bindParam(':nombre', $nombre);
$stmtExiste->bindParam(':id', $id, PDO::PARAM_INT);
$stmtExiste->execute();

if ($stmtExiste->fetch()) {
    die('Ya existe otra categoría con ese nombre.');
}

$sql = "
    UPDATE categorias
    SET nombre = :nombre,
        descripcion = :descripcion
    WHERE id = :id
";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':nombre', $nombre);
$stmt->bindParam(':descripcion', $descripcion);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();

createAdminLog(
    (int)$_SESSION['usuario_id'],
    'EDITAR',
    'CATEGORIA',
    $id,
    'Categoría editada: ' . $nombre
);

redirect('/proyecto_cava_Noble/admin/categorias.php');