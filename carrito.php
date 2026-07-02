<?php
require_once 'includes/session.php';
require_once 'includes/security.php';
require_once 'config/database.php';

startSecureSession();

$pdo = conectarDB();
$csrfToken = generateCsrfToken();

$itemsCarrito = [];
$totalGeneral = 0;

if (!empty($_SESSION['carrito']) && is_array($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $productoId => $item) {
        $sql = "
            SELECT id, nombre, precio, imagen, stock
            FROM productos
            WHERE id = :id
            LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $productoId, PDO::PARAM_INT);
        $stmt->execute();

        $producto = $stmt->fetch();

        if ($producto) {
            $cantidad = min((int)$item['cantidad'], (int)$producto['stock']);

            if ($cantidad <= 0) {
                continue;
            }

            $_SESSION['carrito'][$productoId]['cantidad'] = $cantidad;

            $subtotal = (float)$producto['precio'] * $cantidad;
            $totalGeneral += $subtotal;

            $itemsCarrito[] = [
                'id' => (int)$producto['id'],
                'nombre' => $producto['nombre'],
                'precio' => (float)$producto['precio'],
                'imagen' => $producto['imagen'],
                'stock' => (int)$producto['stock'],
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
            <p>Revisá tus productos, ajustá cantidades y continuá al checkout.</p>
        </div>

        <div class="cart-box">
            <?php if (empty($itemsCarrito)): ?>

                <p>Tu carrito está vacío.</p>
                <br>
                <a href="/proyecto_cava_Noble/pages/catalogo.php" class="btn btn-primary">
                    Ir al catálogo
                </a>

            <?php else: ?>

                <?php foreach ($itemsCarrito as $item): ?>
                    <div class="cart-item">
                        <div style="display:flex; gap:18px; align-items:center;">
                            <img
                                src="<?php echo e($item['imagen']); ?>"
                                alt="<?php echo e($item['nombre']); ?>"
                            >

                            <div>
                                <h3><?php echo e($item['nombre']); ?></h3>
                                <p>Stock disponible: <?php echo (int)$item['stock']; ?></p>
                                <p>Precio unitario: $<?php echo number_format($item['precio'], 0, ',', '.'); ?></p>
                                <p><strong>Subtotal:</strong> $<?php echo number_format($item['subtotal'], 0, ',', '.'); ?></p>
                            </div>
                        </div>

                        <div style="display:flex; flex-direction:column; gap:12px; align-items:flex-end;">
                            <form action="/proyecto_cava_Noble/carrito/actualizar.php" method="POST" style="display:flex; gap:8px; align-items:center;">
                                <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
                                <input type="hidden" name="producto_id" value="<?php echo (int)$item['id']; ?>">

                                <input
                                    type="number"
                                    name="cantidad"
                                    min="1"
                                    max="<?php echo (int)$item['stock']; ?>"
                                    value="<?php echo (int)$item['cantidad']; ?>"
                                    style="width:80px;"
                                    required
                                >

                                <button type="submit" class="btn-card">
                                    Actualizar
                                </button>
                            </form>

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

                    <a href="/proyecto_cava_Noble/pages/catalogo.php" class="btn btn-secondary">
                        Seguir comprando
                    </a>

                    <a href="/proyecto_cava_Noble/pages/checkout.php" class="btn btn-primary">
                        Finalizar compra
                    </a>
                </div>

            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>