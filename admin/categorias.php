<?php
require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../config/database.php';

requireAdmin();

$pdo = conectarDB();

$sql = "
    SELECT
        c.*,
        COUNT(p.id) AS total_productos
    FROM categorias c
    LEFT JOIN productos p ON p.categoria_id = c.id
    GROUP BY c.id, c.nombre, c.descripcion, c.creado_en
    ORDER BY c.nombre ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$categorias = $stmt->fetchAll();

include '../includes/header.php';
?>

<main class="section admin-shell">
    <div class="container">

        <div class="section-header">
            <span class="section-kicker">Organización</span>
            <h2>Categorías</h2>
            <p>Administración completa de categorías del catálogo.</p>
        </div>

        <div class="admin-toolbar">
            <a href="/proyecto_cava_Noble/admin/crear-categoria.php" class="btn btn-primary">
                Crear categoría
            </a>
        </div>

        <div class="admin-products-list">

            <?php if (empty($categorias)): ?>

                <div class="admin-empty-state">
                    <h3>No hay categorías cargadas</h3>
                    <p>Creá la primera categoría para organizar el catálogo.</p>
                </div>

            <?php else: ?>

                <?php foreach ($categorias as $categoria): ?>

                    <article class="admin-product-row">

                        <div class="admin-product-main">
                            <div class="admin-icon">🏷️</div>

                            <div class="admin-product-info">
                                <h3><?php echo e($categoria['nombre']); ?></h3>

                                <p>
                                    <?php echo e($categoria['descripcion'] ?? 'Sin descripción'); ?>
                                </p>

                                <div class="admin-product-meta">
                                    <span>
                                        <strong>Productos asociados:</strong>
                                        <?php echo (int)$categoria['total_productos']; ?>
                                    </span>

                                    <span class="admin-badge">
                                        <?php echo (int)$categoria['total_productos']; ?> productos
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="admin-product-actions">
                            <a
                                href="/proyecto_cava_Noble/admin/editar-categoria.php?id=<?php echo (int)$categoria['id']; ?>"
                                class="admin-action-btn admin-action-edit"
                            >
                                Editar
                            </a>

                            <a
                                href="/proyecto_cava_Noble/admin/confirmar-eliminar-categoria.php?id=<?php echo (int)$categoria['id']; ?>"
                                class="admin-action-btn admin-action-delete <?php echo (int)$categoria['total_productos'] > 0 ? 'admin-action-disabled' : ''; ?>"
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