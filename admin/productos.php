<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

requireAdmin();

$pdo = conectarDB();

$sql = "
    SELECT
        p.*,
        c.nombre AS categoria,
        b.nombre AS bodega_nombre
    FROM productos p
    LEFT JOIN categorias c ON p.categoria_id = c.id
    LEFT JOIN bodegas b ON p.bodega_id = b.id
    ORDER BY p.id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$productos = $stmt->fetchAll();

include '../includes/header.php';
?>

<main class="section">
    <div class="container">

        <div class="section-header">
            <h2>Administrar productos</h2>
            <p>Gestión completa del catálogo de vinos.</p>
        </div>

        <div style="margin-bottom: 30px;">
            <a href="/proyecto_cava_Noble/admin/crear-producto.php" class="btn btn-primary">
                Agregar producto
            </a>
        </div>

        <div class="cart-box" style="max-width:100%;">

            <?php if (empty($productos)): ?>
                <p>No hay productos cargados.</p>
            <?php else: ?>

                <?php foreach ($productos as $producto): ?>

                    <div class="cart-item">

                        <div style="display:flex; gap:20px; align-items:center;">

                            <img
                                src="<?php echo htmlspecialchars($producto['imagen']); ?>"
                                alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                style="width:100px; height:120px; object-fit:cover;"
                            >

                            <div>
                                <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>

                                <p>
                                    <?php echo htmlspecialchars($producto['bodega_nombre'] ?? 'Sin bodega'); ?>
                                    ·
                                    <?php echo htmlspecialchars($producto['categoria'] ?? 'Sin categoría'); ?>
                                </p>

                                <p>
                                    <strong>Precio:</strong>
                                    $<?php echo number_format($producto['precio'], 0, ',', '.'); ?>
                                </p>

                                <p>
                                    <strong>Stock:</strong>
                                    <?php echo (int)$producto['stock']; ?>
                                </p>

                                <?php if ((int)$producto['destacado'] === 1): ?>
                                    <span class="admin-badge">Destacado</span>
                                <?php endif; ?>
                            </div>

                        </div>

                        <div style="display:flex; gap:10px; flex-wrap:wrap;">

                            <a
                                href="/proyecto_cava_Noble/admin/editar-producto.php?id=<?php echo $producto['id']; ?>"
                                class="btn btn-secondary"
                            >
                                Editar
                            </a>

                            <a
                                href="/proyecto_cava_Noble/admin/eliminar-producto.php?id=<?php echo $producto['id']; ?>"
                                class="btn btn-primary"
                            >
                                Eliminar
                            </a>

                        </div>

                    </div>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>

    </div>
</main>

<?php include '../includes/footer.php'; ?>