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

<main class="section">
    <div class="container">
        <div class="section-header">
            <h2>Categorías</h2>
            <p>Administración de categorías del catálogo.</p>
        </div>

        <div style="margin-bottom: 30px;">
            <a href="/proyecto_cava_Noble/admin/crear-categoria.php" class="btn btn-primary">
                Crear categoría
            </a>
        </div>

        <div class="cart-box" style="max-width:100%;">
            <?php if (empty($categorias)): ?>
                <p>No hay categorías cargadas.</p>
            <?php else: ?>
                <?php foreach ($categorias as $categoria): ?>
                    <div class="cart-item">
                        <div>
                            <h3><?php echo e($categoria['nombre']); ?></h3>
                            <p><?php echo e($categoria['descripcion'] ?? 'Sin descripción'); ?></p>
                            <p><strong>Productos asociados:</strong> <?php echo (int)$categoria['total_productos']; ?></p>
                        </div>

                        <span class="admin-badge">
                            <?php echo (int)$categoria['total_productos']; ?> productos
                        </span>
                    </div>
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