<?php
require_once '../includes/auth.php';
require_once '../includes/security.php';
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

$nombre = trim($_POST['nombre'] ?? '');
$pais = trim($_POST['pais'] ?? '');
$region = trim($_POST['region'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

if ($nombre === '' || $pais === '' || $region === '') {
    redirect('/proyecto_cava_Noble/admin/crear-bodega.php');
}

$sqlExiste = "
    SELECT id
    FROM bodegas
    WHERE nombre = :nombre
    AND pais = :pais
    AND region = :region
    LIMIT 1
";

$stmtExiste = $pdo->prepare($sqlExiste);
$stmtExiste->bindParam(':nombre', $nombre);
$stmtExiste->bindParam(':pais', $pais);
$stmtExiste->bindParam(':region', $region);
$stmtExiste->execute();

if ($stmtExiste->fetch()) {
    die('Ya existe una bodega con ese nombre, país y región.');
}

$sql = "
    INSERT INTO bodegas (nombre, pais, region, descripcion)
    VALUES (:nombre, :pais, :region, :descripcion)
";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':nombre', $nombre);
$stmt->bindParam(':pais', $pais);
$stmt->bindParam(':region', $region);
$stmt->bindParam(':descripcion', $descripcion);
$stmt->execute();

redirect('/proyecto_cava_Noble/admin/bodegas.php');