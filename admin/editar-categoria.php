<?php
require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../config/database.php';

requireAdmin();

$pdo = conectarDB();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    redirect('/proyecto_cava_Noble/admin/categorias.php');
}

$sql = "
    SELECT *
    FROM categorias
    WHERE id = :id
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();

$categoria = $stmt->fetch();

if (!$categoria) {
    redirect('/proyecto_cava_Noble/admin/categorias.php');
}

$csrfToken = generateCsrfToken();

include '../includes/header.php';
?>

<main class="section admin-shell">
    <div class="container">

        <div class="section-header">
            <span class="section-kicker">Organización</span>
            <h2>Editar categoría</h2>
            <p>Modificá los datos de la categoría seleccionada.</p>
        </div>

        <div class="form-container">
            <form
                action="/proyecto_cava_Noble/admin/procesar-editar-categoria.php"
                method="POST"
                class="auth-form"
            >
                <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
                <input type="hidden" name="id" value="<?php echo (int)$categoria['id']; ?>">

                <div class="form-group">
                    <label>Nombre</label>
                    <input
                        type="text"
                        name="nombre"
                        value="<?php echo e($categoria['nombre']); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" rows="4"><?php echo e($categoria['descripcion']); ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    Guardar cambios
                </button>

                <a href="/proyecto_cava_Noble/admin/categorias.php" class="btn btn-secondary">
                    Cancelar
                </a>
            </form>
        </div>

    </div>
</main>

<?php include '../includes/footer.php'; ?>