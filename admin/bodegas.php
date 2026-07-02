<?php
require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../config/database.php';

requireAdmin();

$pdo = conectarDB();

$sql = "
    SELECT
        b.*,
        COUNT(p.id) AS total_productos
    FROM bodegas b
    LEFT JOIN productos p ON p.bodega_id = b.id
    GROUP BY b.id, b.nombre, b.pais, b.region, b.descripcion, b.creado_en
    ORDER BY b.nombre ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$bodegas = $stmt->fetchAll();

include '../includes/header.php';
?>

<main class="section admin-shell">
    <div class="container">

        <div class="section-header">
            <span class="section-kicker">Origen</span>
            <h2>Bodegas</h2>
            <p>Administración de bodegas nacionales e internacionales.</p>
        </div>

        <div class="admin-toolbar">
            <a href="/proyecto_cava_Noble/admin/crear-bodega.php" class="btn btn-primary">
                Crear bodega
            </a>
        </div>

        <div class="admin-products-list">

            <?php if (empty($bodegas)): ?>

                <div class="admin-empty-state">
                    <h3>No hay bodegas cargadas</h3>
                    <p>Creá la primera bodega para organizar el catálogo.</p>
                </div>

            <?php else: ?>

                <?php foreach ($bodegas as $bodega): ?>

                    <article class="admin-product-row">

                        <div class="admin-product-main">
                            <div class="admin-icon">🏛️</div>

                            <div class="admin-product-info">
                                <h3><?php echo e($bodega['nombre']); ?></h3>

                                <p>
                                    <?php echo e($bodega['pais']); ?>
                                    ·
                                    <?php echo e($bodega['region']); ?>
                                </p>

                                <p>
                                    <?php echo e($bodega['descripcion'] ?? 'Sin descripción'); ?>
                                </p>

                                <div class="admin-product-meta">
                                    <span>
                                        <strong>Productos asociados:</strong>
                                        <?php echo (int)$bodega['total_productos']; ?>
                                    </span>

                                    <span class="admin-badge">
                                        <?php echo (int)$bodega['total_productos']; ?> productos
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="admin-product-actions">
                            <a
                                href="/proyecto_cava_Noble/admin/editar-bodega.php?id=<?php echo (int)$bodega['id']; ?>"
                                class="admin-action-btn admin-action-edit"
                            >
                                Editar
                            </a>

                            <a
                                href="/proyecto_cava_Noble/admin/confirmar-eliminar-bodega.php?id=<?php echo (int)$bodega['id']; ?>"
                                class="admin-action-btn admin-action-delete <?php echo (int)$bodega['total_productos'] > 0 ? 'admin-action-disabled' : ''; ?>"
                            >
                                Eliminar
                            </a>
                        </div>

                    </article>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>

        <br>

        <a href="/proyecto_cava_Noble/admin/index.php" class="btn btn-secondary">
            Volver al panel
        </a>

    </div>
</main>

<?php include '../includes/footer.php'; ?>