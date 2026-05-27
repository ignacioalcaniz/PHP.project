<?php
require_once 'includes/security.php';
require_once 'config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = conectarDB();
$csrfToken = generateCsrfToken();
$itemsCarrito = [];
$totalGeneral = 0;

if (!empty($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $productoId => $item) {
        $sql = "SELECT id, nombre, precio, imagen, stock FROM productos WHERE id = :id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $productoId, PDO::PARAM_INT);
        $stmt->execute();

        $producto = $stmt->fetch();

        if ($producto) {
            $cantidad = min((int)$item['cantidad'], (int)$producto['stock']);
            $subtotal = $producto['precio'] * $cantidad;
            $totalGeneral += $subtotal;

            $itemsCarrito[] = [
                'id' => $producto['id'],
                'nombre' => $producto['nombre'],
                'precio' => $producto['precio'],
                'imagen' => $producto['imagen'],
                'stock' => $producto['stock'],
                'cantidad' => $cantidad,
                'subtotal' => $subtotal
            ];
        }
    }
}

include 'includes/header.php';
?>

<main class="section">
    <div class="container">
        <div class="section-header">
            <h2>Tu carrito</h2>
            <p>Revisá los productos seleccionados antes de continuar.</p>
        </div>

        <div class="cart-box">
            <?php if (empty($itemsCarrito)): ?>
                <p>Tu carrito está vacío.</p>
                <br>
                <a href="/proyecto_cava_Noble/pages/catalogo.php" class="btn btn-primary">Ir al catálogo</a>
            <?php else: ?>

                <?php foreach ($itemsCarrito as $item): ?>
                    <div class="cart-item">
                        <div>
                            <h3><?php echo e($item['nombre']); ?></h3>
                            <p>Cantidad: <?php echo (int)$item['cantidad']; ?></p>
                            <p>Stock disponible: <?php echo (int)$item['stock']; ?></p>
                            <p>Precio unitario: $<?php echo number_format($item['precio'], 0, ',', '.'); ?></p>
                            <p>Subtotal: $<?php echo number_format($item['subtotal'], 0, ',', '.'); ?></p>
                        </div>

                        <div style="display:flex; flex-direction:column; gap:10px; align-items:end;">
                            <img
                                src="<?php echo e($item['imagen']); ?>"
                                alt="<?php echo e($item['nombre']); ?>"
                                style="width:90px; border-radius:10px;"
                            >

                            <form action="/proyecto_cava_Noble/carrito/eliminar.php" method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
                                <input type="hidden" name="producto_id" value="<?php echo (int)$item['id']; ?>">

                                <button type="submit" class="btn-card">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="cart-total">
                    <h3>Total</h3>
                    <span>$<?php echo number_format($totalGeneral, 0, ',', '.'); ?></span>
                </div>

                <div style="display:flex; gap:15px; flex-wrap:wrap;">
                    <form action="/proyecto_cava_Noble/carrito/vaciar.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
                        <button type="submit" class="btn btn-secondary">Vaciar carrito</button>
                    </form>

                    <a href="/proyecto_cava_Noble/pages/catalogo.php" class="btn btn-secondary">Seguir comprando</a>
                    <a href="/proyecto_cava_Noble/pages/checkout.php" class="btn btn-primary">Finalizar compra</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>