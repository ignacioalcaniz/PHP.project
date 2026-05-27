<?php
require_once '../includes/security.php';
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pedidoId = $_SESSION['ultimo_pedido_id'] ?? null;

if (!$pedidoId) {
    redirect('/proyecto_cava_Noble/pages/catalogo.php');
}

$pdo = conectarDB();

$sql = "
    SELECT *
    FROM pedidos
    WHERE id = :id
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $pedidoId, PDO::PARAM_INT);
$stmt->execute();

$pedido = $stmt->fetch();

if (!$pedido) {
    redirect('/proyecto_cava_Noble/pages/catalogo.php');
}

include '../includes/header.php';
?>

<main class="section">
    <div class="container">
        <div class="form-container" style="max-width: 750px;">
            <h2>¡Gracias por tu compra!</h2>

            <p>Tu pedido fue generado correctamente.</p>

            <br>

            <div class="product-data">
                <p><strong>Número de pedido:</strong> #<?php echo (int)$pedido['id']; ?></p>
                <p><strong>Cliente:</strong> <?php echo e($pedido['nombre_cliente']); ?></p>
                <p><strong>Email:</strong> <?php echo e($pedido['email_cliente']); ?></p>
                <p><strong>Estado:</strong> <?php echo e($pedido['estado']); ?></p>
                <p><strong>Método de pago:</strong> <?php echo e($pedido['metodo_pago']); ?></p>
                <p><strong>Total:</strong> $<?php echo number_format($pedido['total'], 0, ',', '.'); ?></p>
                <p><strong>Fecha:</strong> <?php echo e($pedido['fecha_pedido']); ?></p>
            </div>

            <br>

            <p>
                Te contactaremos para coordinar el pago y la entrega/retiro del pedido.
            </p>

            <br>

            <a href="/proyecto_cava_Noble/pages/catalogo.php" class="btn btn-primary">
                Seguir comprando
            </a>
        </div>
    </div>
</main>

<?php
unset($_SESSION['ultimo_pedido_id']);
include '../includes/footer.php';
?>