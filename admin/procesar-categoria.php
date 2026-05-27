<?php
require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../config/database.php';

requireAdmin();

if (!isPostRequest()) {
    redirect('/proyecto_cava_Noble/admin/categorias.php');
}

if (
    !isset($_POST['csrf_token']) ||
    !validateCsrfToken($_POST['csrf_token'])
) {
    die('Token CSRF inválido.');
}

$pdo = conectarDB();

$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

if ($nombre === '') {
    redirect('/proyecto_cava_Noble/admin/crear-categoria.php');
}

$sqlExiste = "
    SELECT id
    FROM categorias
    WHERE nombre = :nombre
    LIMIT 1
";

$stmtExiste = $pdo->prepare($sqlExiste);
$stmtExiste->bindParam(':nombre', $nombre);
$stmtExiste->execute();

if ($stmtExiste->fetch()) {
    die('Ya existe una categoría con ese nombre.');
}

$sql = "
    INSERT INTO categorias (nombre, descripcion)
    VALUES (:nombre, :descripcion)
";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':nombre', $nombre);
$stmt->bindParam(':descripcion', $descripcion);
$stmt->execute();

redirect('/proyecto_cava_Noble/admin/categorias.php');