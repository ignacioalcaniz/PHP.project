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

if ($id <= 0) {
    redirect('/proyecto_cava_Noble/admin/categorias.php');
}

$sqlCategoria = "
    SELECT c.*, COUNT(p.id) AS total_productos
    FROM categorias c
    LEFT JOIN productos p ON p.categoria_id = c.id
    WHERE c.id = :id
    GROUP BY c.id, c.nombre, c.descripcion, c.creado_en
    LIMIT 1
";

$stmtCategoria = $pdo->prepare($sqlCategoria);
$stmtCategoria->bindParam(':id', $id, PDO::PARAM_INT);
$stmtCategoria->execute();

$categoria = $stmtCategoria->fetch();

if (!$categoria) {
    redirect('/proyecto_cava_Noble/admin/categorias.php');
}

if ((int)$categoria['total_productos'] > 0) {
    die('No se puede eliminar una categoría con productos asociados.');
}

$sql = "DELETE FROM categorias WHERE id = :id";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();

createAdminLog(
    (int)$_SESSION['usuario_id'],
    'ELIMINAR',
    'CATEGORIA',
    $id,
    'Categoría eliminada: ' . $categoria['nombre']
);

redirect('/proyecto_cava_Noble/admin/categorias.php');