<?php
require_once 'config/database.php';

$pdo = conectarDB();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    die('Producto no válido.');
}

$sql = "SELECT * FROM productos WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();

$producto = $stmt->fetch();

if (!$producto) {
    die('Producto no encontrado.');
}

$descuento = 0;

if ((int)$producto['destacado'] === 1) {
    $descuento = 1500;
}

$precioFinal = $producto['precio'] - $descuento;

include 'includes/header.php';
?>

<main class="section">
    <div class="container">
        <div class="product-detail">
            <div class="product-detail-image">
                <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
            </div>

            <div class="product-detail-info">
                <span class="product-category"><?php echo htmlspecialchars($producto['pais']); ?></span>

                <h1><?php echo htmlspecialchars($producto['nombre']); ?></h1>

                <?php if ($descuento > 0): ?>
                    <p class="detail-price">Precio original: $<?php echo number_format($producto['precio'], 0, ',', '.'); ?></p>
                    <p class="detail-price">Precio final: $<?php echo number_format($precioFinal, 0, ',', '.'); ?></p>
                <?php else: ?>
                    <p class="detail-price">$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></p>
                <?php endif; ?>

                <p><?php echo htmlspecialchars($producto['descripcion']); ?></p>

                <ul class="product-data">
                    <li><strong>Bodega:</strong> <?php echo htmlspecialchars($producto['bodega']); ?></li>
                    <li><strong>País:</strong> <?php echo htmlspecialchars($producto['pais']); ?></li>
                    <li><strong>Región:</strong> <?php echo htmlspecialchars($producto['region']); ?></li>
                    <li><strong>Cepa:</strong> <?php echo htmlspecialchars($producto['cepa']); ?></li>
                    <li><strong>Añada:</strong> <?php echo htmlspecialchars($producto['anada']); ?></li>
                    <li><strong>Stock:</strong> <?php echo (int)$producto['stock']; ?> unidades</li>
                </ul>

                <?php if ((int)$producto['stock'] > 0): ?>
                    <form action="/proyecto_cava_Noble/carrito/agregar.php" method="POST" class="auth-form">
                        <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">

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

                        <button type="submit" class="btn btn-primary">Agregar al carrito</button>
                    </form>
                <?php else: ?>
                    <a href="/proyecto_cava_Noble/catalogo.php" class="btn btn-secondary">Volver al catálogo</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>