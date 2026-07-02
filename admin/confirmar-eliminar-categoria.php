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
    SELECT
        c.*,
        COUNT(p.id) AS total_productos
    FROM categorias c
    LEFT JOIN productos p ON p.categoria_id = c.id
    WHERE c.id = :id
    GROUP BY c.id, c.nombre, c.descripcion, c.creado_en
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

        <div class="delete-confirm-card">

            <span class="section-kicker">Confirmación</span>

            <h2>Eliminar categoría</h2>

            <p>
                Estás por eliminar la categoría
                <strong><?php echo e($categoria['nombre']); ?></strong>.
            </p>

            <div class="delete-summary">
                <p><strong>Descripción:</strong> <?php echo e($categoria['descripcion'] ?? 'Sin descripción'); ?></p>
                <p><strong>Productos asociados:</strong> <?php echo (int)$categoria['total_productos']; ?></p>
            </div>

            <?php if ((int)$categoria['total_productos'] > 0): ?>

                <div class="delete-warning">
                    No se puede eliminar esta categoría porque tiene productos asociados.
                </div>

                <a href="/proyecto_cava_Noble/admin/categorias.php" class="btn btn-secondary">
                    Volver
                </a>

            <?php else: ?>

                <div class="delete-warning">
                    Esta acción no se puede deshacer.
                </div>

                <form action="/proyecto_cava_Noble/admin/eliminar-categoria.php" method="POST" class="delete-actions">
                    <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
                    <input type="hidden" name="id" value="<?php echo (int)$categoria['id']; ?>">

                    <a href="/proyecto_cava_Noble/admin/categorias.php" class="btn btn-secondary">
                        Cancelar
                    </a>

                    <button type="submit" class="admin-action-btn admin-action-delete">
                        Eliminar categoría
                    </button>
                </form>

            <?php endif; ?>

        </div>

    </div>
</main>

<?php include '../includes/footer.php'; ?>