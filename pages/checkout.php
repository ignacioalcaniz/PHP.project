<?php
require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['carrito'])) {
    redirect('/proyecto_cava_Noble/carrito.php');
}

$pdo = conectarDB();
$csrfToken = generateCsrfToken();

$itemsCarrito = [];
$totalGeneral = 0;

foreach ($_SESSION['carrito'] as $productoId => $item) {
    $sql = "
        SELECT id, nombre, precio, stock
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
        $subtotal = $producto['precio'] * $cantidad;
        $totalGeneral += $subtotal;

        $itemsCarrito[] = [
            'id' => $producto['id'],
            'nombre' => $producto['nombre'],
            'precio' => $producto['precio'],
            'cantidad' => $cantidad,
            'subtotal' => $subtotal
        ];
    }
}

if (empty($itemsCarrito)) {
    unset($_SESSION['carrito']);
    redirect('/proyecto_cava_Noble/carrito.php');
}

$nombreUsuario = $_SESSION['usuario_nombre'] ?? '';
$emailUsuario = $_SESSION['usuario_email'] ?? '';

include '../includes/header.php';
?>

<main class="section">
    <div class="container">
        <div class="section-header">
            <h2>Checkout</h2>
            <p>Confirmá tus datos para generar el pedido.</p>
        </div>

        <div class="form-container" style="max-width: 900px;">
            <h2>Datos de compra</h2>

            <form action="/proyecto_cava_Noble/pages/procesar-checkout.php" method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">

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
                    <input type="text" name="telefono" required>
                </div>

                <div class="form-group">
                    <label>Dirección</label>
                    <input type="text" name="direccion" required>
                </div>

                <div class="form-group">
                    <label>Ciudad</label>
                    <input type="text" name="ciudad" required>
                </div>

                <div class="form-group">
                    <label>Provincia</label>
                    <input type="text" name="provincia" required>
                </div>

                <div class="form-group">
                    <label>Método de pago</label>
                    <select name="metodo_pago" required>
                        <option value="">Seleccionar</option>
                        <option value="transferencia">Transferencia bancaria</option>
                        <option value="efectivo">Efectivo al retirar</option>
                        <option value="tarjeta">Tarjeta de crédito/débito</option>
                    </select>
                </div>

                <br>

                <div class="cart-box" style="max-width:100%;">
                    <h3>Resumen del pedido</h3>
                    <br>

                    <?php foreach ($itemsCarrito as $item): ?>
                        <div class="cart-item">
                            <div>
                                <h3><?php echo e($item['nombre']); ?></h3>
                                <p>Cantidad: <?php echo (int)$item['cantidad']; ?></p>
                                <p>Precio unitario: $<?php echo number_format($item['precio'], 0, ',', '.'); ?></p>
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
                </div>

                <br>

                <button type="submit" class="btn btn-primary">
                    Confirmar pedido
                </button>

                <a href="/proyecto_cava_Noble/carrito.php" class="btn btn-secondary">
                    Volver al carrito
                </a>
            </form>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>