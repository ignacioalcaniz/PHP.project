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

$id = (int)($_POST['id'] ?? 0);
$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$precio = (float)($_POST['precio'] ?? 0);
$categoriaId = (int)($_POST['categoria_id'] ?? 0);
$bodegaId = (int)($_POST['bodega_id'] ?? 0);
$cepa = trim($_POST['cepa'] ?? '');
$anada = (int)($_POST['anada'] ?? 0);
$stock = (int)($_POST['stock'] ?? 0);
$imagenActual = trim($_POST['imagen_actual'] ?? '');
$destacado = (int)($_POST['destacado'] ?? 0);

$errores = [];

if ($id <= 0) $errores[] = 'Producto inválido.';
if ($nombre === '') $errores[] = 'El nombre es obligatorio.';
if ($descripcion === '') $errores[] = 'La descripción es obligatoria.';
if ($precio <= 0) $errores[] = 'El precio debe ser mayor a cero.';
if ($categoriaId <= 0) $errores[] = 'La categoría es obligatoria.';
if ($bodegaId <= 0) $errores[] = 'La bodega es obligatoria.';
if ($cepa === '') $errores[] = 'La cepa es obligatoria.';
if ($anada < 1900 || $anada > 2100) $errores[] = 'La añada no es válida.';
if ($stock < 0) $errores[] = 'El stock no puede ser negativo.';
if ($imagenActual === '') $errores[] = 'La imagen actual no es válida.';

if (!empty($errores)) {
    die(implode('<br>', array_map('e', $errores)));
}

$sqlBodega = "
    SELECT nombre, pais, region
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

$rutaImagen = $imagenActual;
$imagenCambiada = false;

if (
    isset($_FILES['imagen']) &&
    $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE
) {
    try {
        $rutaImagen = subirImagenProducto($_FILES['imagen']);
        $imagenCambiada = true;

        if (strpos($imagenActual, '/assets/uploads/products/') !== false) {
            $rutaVieja = dirname(__DIR__) . str_replace('/proyecto_cava_Noble', '', $imagenActual);

            if (file_exists($rutaVieja)) {
                unlink($rutaVieja);
            }
        }
    } catch (Exception $e) {
        die('Error al subir imagen: ' . e($e->getMessage()));
    }
}

$sql = "
    UPDATE productos
    SET
        nombre = :nombre,
        descripcion = :descripcion,
        precio = :precio,
        pais = :pais,
        region = :region,
        bodega = :bodega,
        cepa = :cepa,
        anada = :anada,
        stock = :stock,
        imagen = :imagen,
        destacado = :destacado,
        categoria_id = :categoria_id,
        bodega_id = :bodega_id
    WHERE id = :id
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
$stmt->bindParam(':id', $id, PDO::PARAM_INT);

$stmt->execute();

$descripcionLog = 'Producto editado: ' . $nombre;

if ($imagenCambiada) {
    $descripcionLog .= ' | Imagen actualizada';
}

createAdminLog(
    (int)$_SESSION['usuario_id'],
    'EDITAR',
    'PRODUCTO',
    $id,
    $descripcionLog
);

redirect('/proyecto_cava_Noble/admin/productos.php');