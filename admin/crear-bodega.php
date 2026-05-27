<?php
require_once '../includes/auth.php';
require_once '../includes/security.php';

requireAdmin();

$csrfToken = generateCsrfToken();

include '../includes/header.php';
?>

<main class="section">
    <div class="container">
        <div class="section-header">
            <h2>Crear bodega</h2>
            <p>Agregá una nueva bodega al sistema.</p>
        </div>

        <div class="form-container">
            <form action="/proyecto_cava_Noble/admin/procesar-bodega.php" method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">

                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" required>
                </div>

                <div class="form-group">
                    <label>País</label>
                    <input type="text" name="pais" required>
                </div>

                <div class="form-group">
                    <label>Región</label>
                    <input type="text" name="region" required>
                </div>

                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" rows="4"></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    Guardar bodega
                </button>

                <a href="/proyecto_cava_Noble/admin/bodegas.php" class="btn btn-secondary">
                    Cancelar
                </a>
            </form>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>