<?php
require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../config/database.php';

requireLogin();

$pedidoId = $_SESSION['ultimo_pedido_id'] ?? null;

if (!$pedidoId) {
    redirect('/proyecto_cava_Noble/pages/catalogo.php');
}

$pdo = conectarDB();

$sqlPedido = "
    SELECT *
    FROM pedidos
    WHERE id = :id
    AND usuario_id = :usuario_id
    LIMIT 1
";

$stmtPedido = $pdo->prepare($sqlPedido);
$stmtPedido->bindParam(':id', $pedidoId, PDO::PARAM_INT);
$stmtPedido->bindParam(':usuario_id', $_SESSION['usuario_id'], PDO::PARAM_INT);
$stmtPedido->execute();

$pedido = $stmtPedido->fetch();

if (!$pedido) {
    redirect('/proyecto_cava_Noble/pages/catalogo.php');
}

$sqlItems = "
    SELECT *
    FROM pedido_items
    WHERE pedido_id = :pedido_id
";

$stmtItems = $pdo->prepare($sqlItems);
$stmtItems->bindParam(':pedido_id', $pedidoId, PDO::PARAM_INT);
$stmtItems->execute();

$items = $stmtItems->fetchAll();

include '../includes/header.php';
?>

<main class="section">
    <div class="container">

        <div class="section-header">
            <span class="section-kicker">Compra confirmada</span>
            <h2>¡Gracias por tu compra!</h2>
            <p>Tu pedido fue generado correctamente.</p>
        </div>

        <div class="cart-box" style="max-width:900px;">

            <h2>Pedido #<?php echo (int)$pedido['id']; ?></h2>
            <br>

            <p><strong>Cliente:</strong> <?php echo e($pedido['nombre_cliente']); ?></p>
            <p><strong>Email:</strong> <?php echo e($pedido['email_cliente']); ?></p>
            <p><strong>Teléfono:</strong> <?php echo e($pedido['telefono']); ?></p>
            <p><strong>Dirección:</strong> <?php echo e($pedido['direccion']); ?></p>
            <p><strong>Estado:</strong> <span class="admin-badge"><?php echo e($pedido['estado']); ?></span></p>
            <p><strong>Método de pago:</strong> <?php echo e($pedido['metodo_pago']); ?></p>
            <p><strong>Fecha:</strong> <?php echo e($pedido['fecha_pedido']); ?></p>

            <br>

            <h3>Productos comprados</h3>
            <br>

            <?php foreach ($items as $item): ?>
                <div class="cart-item">
                    <div>
                        <h3><?php echo e($item['nombre_producto']); ?></h3>
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
                <span>$<?php echo number_format($pedido['total'], 0, ',', '.'); ?></span>
            </div>

            <br>

            <p>Te contactaremos para coordinar el pago y la entrega/retiro del pedido.</p>

            <br>

            <div style="display:flex; gap:14px; flex-wrap:wrap;">
                <a href="/proyecto_cava_Noble/pages/catalogo.php" class="btn btn-primary">
                    Seguir comprando
                </a>

                <a href="/proyecto_cava_Noble/index.php" class="btn btn-secondary">
                    Volver al inicio
                </a>
            </div>

        </div>

    </div>
</main>

<?php
unset($_SESSION['ultimo_pedido_id']);
include '../includes/footer.php';
?>