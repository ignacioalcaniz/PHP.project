<?php

require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../config/database.php';

requireAdmin();

$pdo = conectarDB();

$id = isset($_GET['id'])
    ? (int)$_GET['id']
    : 0;

if ($id <= 0) {
    redirect('/proyecto_cava_Noble/admin/bodegas.php');
}

$sql = "
    SELECT *
    FROM bodegas
    WHERE id = :id
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();

$bodega = $stmt->fetch();

if (!$bodega) {
    redirect('/proyecto_cava_Noble/admin/bodegas.php');
}

$csrfToken = generateCsrfToken();

include '../includes/header.php';
?>

<main class="section admin-shell">
<div class="container">

<div class="section-header">
<span class="section-kicker">Origen</span>
<h2>Editar bodega</h2>
<p>Actualizá información comercial.</p>
</div>

<div class="form-container">

<form
action="/proyecto_cava_Noble/admin/procesar-editar-bodega.php"
method="POST"
class="auth-form"
>

<input
type="hidden"
name="csrf_token"
value="<?php echo e($csrfToken); ?>"
>

<input
type="hidden"
name="id"
value="<?php echo (int)$bodega['id']; ?>"
>

<div class="form-group">
<label>Nombre</label>

<input
type="text"
name="nombre"
value="<?php echo e($bodega['nombre']); ?>"
required
>
</div>

<div class="form-group">
<label>País</label>

<input
type="text"
name="pais"
value="<?php echo e($bodega['pais']); ?>"
required
>
</div>

<div class="form-group">
<label>Región</label>

<input
type="text"
name="region"
value="<?php echo e($bodega['region']); ?>"
required
>
</div>

<div class="form-group">
<label>Descripción</label>

<textarea
name="descripcion"
rows="5"
><?php echo e($bodega['descripcion']); ?></textarea>

</div>

<button
type="submit"
class="btn btn-primary"
>
Guardar cambios
</button>

<a
href="/proyecto_cava_Noble/admin/bodegas.php"
class="btn btn-secondary"
>
Cancelar
</a>

</form>

</div>

</div>
</main>

<?php include '../includes/footer.php'; ?>