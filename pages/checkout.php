<?php
require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../includes/captcha.php';
require_once '../config/database.php';

requireLogin();

$pdo = conectarDB();
$csrfToken = generateCsrfToken();

if (empty($_SESSION['checkout_token'])) {
    $_SESSION['checkout_token'] = bin2hex(random_bytes(32));
}

$checkoutToken = $_SESSION['checkout_token'];

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

if (empty($itemsCarrito)) {
    redirect('/proyecto_cava_Noble/carrito.php');
}

$nombreUsuario = $_SESSION['usuario_nombre'] ?? '';
$emailUsuario = $_SESSION['usuario_email'] ?? '';

include '../includes/header.php';
?>

<main class="section">
    <div class="container">

        <div class="section-header">
            <h2>Finalizar compra</h2>
            <p>Confirmá tus datos y revisá el resumen del pedido.</p>
        </div>

        <div class="admin-report-grid">

            <section class="form-container" style="max-width:100%;">
                <h2>Datos del comprador</h2>

                <?php if (isset($_GET['error'])): ?>
                    <p style="color:#8b0000;font-weight:700;">
                        No se pudo procesar la compra. Revisá los datos.
                    </p>
                <?php endif; ?>

                <?php if (isset($_GET['stock'])): ?>
                    <p style="color:#8b0000;font-weight:700;">
                        Algún producto ya no tiene stock suficiente.
                    </p>
                <?php endif; ?>

                <?php if (isset($_GET['captcha'])): ?>
                    <p style="color:#8b0000;font-weight:700;">
                        No se pudo validar la comprobación de seguridad.
                    </p>
                <?php endif; ?>

                <form
                    action="/proyecto_cava_Noble/pages/procesar-checkout.php"
                    method="POST"
                    class="auth-form"
                >
                    <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
                    <input type="hidden" name="checkout_token" value="<?php echo e($checkoutToken); ?>">

                    <div class="form-group">
                        <label>Nombre completo</label>
                        <input
                            type="text"
                            name="nombre_cliente"
                            value="<?php echo e($nombreUsuario); ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input
                            type="email"
                            name="email_cliente"
                            value="<?php echo e($emailUsuario); ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Teléfono</label>
                        <input
                            type="text"
                            name="telefono"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Dirección de entrega</label>
                        <textarea name="direccion" rows="4" required></textarea>
                    </div>

                    <div class="form-group">
                        <label>Método de pago</label>
                        <select name="metodo_pago" required>
                            <option value="">Seleccionar</option>
                            <option value="transferencia">Transferencia bancaria</option>
                            <option value="efectivo">Efectivo al retirar</option>
                        </select>
                    </div>

                    <?php renderTurnstileWidget(); ?>

                    <button type="submit" class="btn btn-primary">
                        Confirmar compra
                    </button>
                </form>
            </section>

            <section class="cart-box" style="max-width:100%;">
                <h2>Resumen del pedido</h2>
                <br>

                <?php foreach ($itemsCarrito as $item): ?>
                    <div class="cart-item">
                        <div>
                            <h3><?php echo e($item['nombre']); ?></h3>
                            <p>Cantidad: <?php echo (int)$item['cantidad']; ?></p>
                            <p>Precio: $<?php echo number_format($item['precio'], 0, ',', '.'); ?></p>
                        </div>

                        <strong>
                            $<?php echo number_format($item['subtotal'], 0, ',', '.'); ?>
                        </strong>
                    </div>
                <?php endforeach; ?>

                <div class="cart-total">
                    <h3>Total</h3>
                    <span>$<?php echo number_format($totalGeneral, 0, ',', '.'); ?></span>
                </div>

                <br>

                <a href="/proyecto_cava_Noble/carrito.php" class="btn btn-secondary">
                    Volver al carrito
                </a>
            </section>

        </div>

    </div>
</main>

<?php include '../includes/footer.php'; ?>