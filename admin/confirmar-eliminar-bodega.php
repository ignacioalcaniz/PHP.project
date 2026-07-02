<?php
require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../config/database.php';

requireAdmin();

$pdo = conectarDB();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    redirect('/proyecto_cava_Noble/admin/bodegas.php');
}

$sql = "
    SELECT
        b.*,
        COUNT(p.id) AS total_productos
    FROM bodegas b
    LEFT JOIN productos p ON p.bodega_id = b.id
    WHERE b.id = :id
    GROUP BY b.id, b.nombre, b.pais, b.region, b.descripcion, b.creado_en
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
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

        <div class="delete-confirm-card">

            <span class="section-kicker">Confirmación</span>

            <h2>Eliminar bodega</h2>

            <p>
                Estás por eliminar la bodega
                <strong><?php echo e($bodega['nombre']); ?></strong>.
            </p>

            <div class="delete-summary">
                <p><strong>País:</strong> <?php echo e($bodega['pais']); ?></p>
                <p><strong>Región:</strong> <?php echo e($bodega['region']); ?></p>
                <p><strong>Productos asociados:</strong> <?php echo (int)$bodega['total_productos']; ?></p>
            </div>

            <?php if ((int)$bodega['total_productos'] > 0): ?>

                <div class="delete-warning">
                    No se puede eliminar esta bodega porque tiene productos asociados.
                </div>

                <a href="/proyecto_cava_Noble/admin/bodegas.php" class="btn btn-secondary">
                    Volver
                </a>

            <?php else: ?>

                <div class="delete-warning">
                    Esta acción no se puede deshacer.
                </div>

                <form action="/proyecto_cava_Noble/admin/eliminar-bodega.php" method="POST" class="delete-actions">
                    <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
                    <input type="hidden" name="id" value="<?php echo (int)$bodega['id']; ?>">

                    <a href="/proyecto_cava_Noble/admin/bodegas.php" class="btn btn-secondary">
                        Cancelar
                    </a>

                    <button type="submit" class="admin-action-btn admin-action-delete">
                        Eliminar bodega
                    </button>
                </form>

            <?php endif; ?>

        </div>

    </div>
</main>

<?php include '../includes/footer.php'; ?>