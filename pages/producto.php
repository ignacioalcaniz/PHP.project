<?php
require_once '../includes/session.php';
require_once '../includes/security.php';
require_once '../config/database.php';

startSecureSession();

$pdo = conectarDB();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    redirect('/proyecto_cava_Noble/pages/catalogo.php');
}

$sql = "
    SELECT
        p.*,
        c.nombre AS categoria,
        b.nombre AS bodega_nombre,
        b.pais AS bodega_pais,
        b.region AS bodega_region,
        b.descripcion AS bodega_descripcion
    FROM productos p
    LEFT JOIN categorias c ON p.categoria_id = c.id
    LEFT JOIN bodegas b ON p.bodega_id = b.id
    WHERE p.id = :id
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();

$producto = $stmt->fetch();

if (!$producto) {
    redirect('/proyecto_cava_Noble/pages/catalogo.php');
}

$descuento = 0;

if ((int)$producto['destacado'] === 1) {
    $descuento = 1500;
}

$precioFinal = max(0, (float)$producto['precio'] - $descuento);
$csrfToken = generateCsrfToken();

include '../includes/header.php';
?>

<main class="section">
    <div class="container">
        <div class="product-detail">
            <div class="product-detail-image">
                <img
                    src="<?php echo e($producto['imagen']); ?>"
                    alt="<?php echo e($producto['nombre']); ?>"
                >
            </div>

            <div class="product-detail-info">
                <span class="product-category">
                    <?php echo e($producto['categoria'] ?? 'Sin categoría'); ?>
                </span>

                <h1><?php echo e($producto['nombre']); ?></h1>

                <?php if ($descuento > 0): ?>
                    <p class="detail-price">
                        Precio original: $<?php echo number_format($producto['precio'], 0, ',', '.'); ?>
                    </p>

                    <p class="detail-price">
                        Precio final: $<?php echo number_format($precioFinal, 0, ',', '.'); ?>
                    </p>
                <?php else: ?>
                    <p class="detail-price">
                        $<?php echo number_format($producto['precio'], 0, ',', '.'); ?>
                    </p>
                <?php endif; ?>

                <p><?php echo e($producto['descripcion']); ?></p>

                <ul class="product-data">
                    <li><strong>Bodega:</strong> <?php echo e($producto['bodega_nombre'] ?? $producto['bodega']); ?></li>
                    <li><strong>País:</strong> <?php echo e($producto['bodega_pais'] ?? $producto['pais']); ?></li>
                    <li><strong>Región:</strong> <?php echo e($producto['bodega_region'] ?? $producto['region']); ?></li>
                    <li><strong>Cepa:</strong> <?php echo e($producto['cepa']); ?></li>
                    <li><strong>Añada:</strong> <?php echo e($producto['anada']); ?></li>
                    <li><strong>Categoría:</strong> <?php echo e($producto['categoria'] ?? 'Sin categoría'); ?></li>
                    <li><strong>Stock:</strong> <?php echo (int)$producto['stock']; ?> unidades</li>
                </ul>

                <?php if (!empty($producto['bodega_descripcion'])): ?>
                    <p>
                        <strong>Sobre la bodega:</strong>
                        <?php echo e($producto['bodega_descripcion']); ?>
                    </p>
                <?php endif; ?>

                <?php if ((int)$producto['stock'] > 0): ?>
                    <form action="/proyecto_cava_Noble/carrito/agregar.php" method="POST" class="auth-form">
                        <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
                        <input type="hidden" name="producto_id" value="<?php echo (int)$producto['id']; ?>">

                        <div class="form-group">
                            <label for="cantidad">Cantidad</label>
                            <input
                                type="number"
                                id="cantidad"
                                name="cantidad"
                                min="1"
                                max="<?php echo (int)$producto['stock']; ?>"
                                value="1"
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Agregar al carrito
                        </button>
                    </form>
                <?php else: ?>
                    <p><strong>Producto sin stock disponible.</strong></p>
                    <br>
                    <a href="/proyecto_cava_Noble/pages/catalogo.php" class="btn btn-secondary">
                        Volver al catálogo
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>