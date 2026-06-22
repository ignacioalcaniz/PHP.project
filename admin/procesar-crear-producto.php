<?php

require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../includes/upload.php';
require_once '../includes/admin-log.php';
require_once '../config/database.php';

requireAdmin();

if (!isPostRequest()) {
    redirect('/proyecto_cava_Noble/admin/productos.php');
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
$precio = (float)($_POST['precio'] ?? 0);
$categoriaId = (int)($_POST['categoria_id'] ?? 0);
$bodegaId = (int)($_POST['bodega_id'] ?? 0);
$cepa = trim($_POST['cepa'] ?? '');
$anada = (int)($_POST['anada'] ?? 0);
$stock = (int)($_POST['stock'] ?? 0);
$destacado = (int)($_POST['destacado'] ?? 0);

try {
    $rutaImagen = subirImagenProducto($_FILES['imagen']);
} catch (Exception $e) {
    die('Error al subir imagen: ' . e($e->getMessage()));
}

$sqlBodega = "
    SELECT pais, region, nombre
    FROM bodegas
    WHERE id = :id
    LIMIT 1
";

$stmtBodega = $pdo->prepare($sqlBodega);
$stmtBodega->bindParam(':id', $bodegaId, PDO::PARAM_INT);
$stmtBodega->execute();

$bodega = $stmtBodega->fetch();

if (!$bodega) {
    die('Bodega inválida.');
}

$sql = "
    INSERT INTO productos (
        nombre,
        descripcion,
        precio,
        pais,
        region,
        bodega,
        cepa,
        anada,
        stock,
        imagen,
        destacado,
        categoria_id,
        bodega_id
    )
    VALUES (
        :nombre,
        :descripcion,
        :precio,
        :pais,
        :region,
        :bodega,
        :cepa,
        :anada,
        :stock,
        :imagen,
        :destacado,
        :categoria_id,
        :bodega_id
    )
";

$stmt = $pdo->prepare($sql);

$stmt->bindParam(':nombre', $nombre);
$stmt->bindParam(':descripcion', $descripcion);
$stmt->bindParam(':precio', $precio);
$stmt->bindParam(':pais', $bodega['pais']);
$stmt->bindParam(':region', $bodega['region']);
$stmt->bindParam(':bodega', $bodega['nombre']);
$stmt->bindParam(':cepa', $cepa);
$stmt->bindParam(':anada', $anada, PDO::PARAM_INT);
$stmt->bindParam(':stock', $stock, PDO::PARAM_INT);
$stmt->bindParam(':imagen', $rutaImagen);
$stmt->bindParam(':destacado', $destacado, PDO::PARAM_INT);
$stmt->bindParam(':categoria_id', $categoriaId, PDO::PARAM_INT);
$stmt->bindParam(':bodega_id', $bodegaId, PDO::PARAM_INT);

$stmt->execute();

$productoId = (int)$pdo->lastInsertId();

createAdminLog(
    (int)$_SESSION['usuario_id'],
    'CREAR',
    'PRODUCTO',
    $productoId,
    'Producto creado: ' . $nombre
);

redirect('/proyecto_cava_Noble/admin/productos.php');