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

<main class="section">
    <div class="container">
        <div class="section-header">
            <h2>Bodegas</h2>
            <p>Administración de bodegas nacionales e internacionales.</p>
        </div>

        <div style="margin-bottom: 30px;">
            <a href="/proyecto_cava_Noble/admin/crear-bodega.php" class="btn btn-primary">
                Crear bodega
            </a>
        </div>

        <div class="cart-box" style="max-width:100%;">
            <?php if (empty($bodegas)): ?>
                <p>No hay bodegas cargadas.</p>
            <?php else: ?>
                <?php foreach ($bodegas as $bodega): ?>
                    <div class="cart-item">
                        <div>
                            <h3><?php echo e($bodega['nombre']); ?></h3>
                            <p><?php echo e($bodega['pais']); ?> · <?php echo e($bodega['region']); ?></p>
                            <p><?php echo e($bodega['descripcion'] ?? 'Sin descripción'); ?></p>
                            <p><strong>Productos asociados:</strong> <?php echo (int)$bodega['total_productos']; ?></p>
                        </div>

                        <span class="admin-badge">
                            <?php echo (int)$bodega['total_productos']; ?> productos
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