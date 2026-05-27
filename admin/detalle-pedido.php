<?php
require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../config/database.php';

requireAdmin();

$pdo = conectarDB();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    redirect('/proyecto_cava_Noble/admin/pedidos.php');
}

$sqlPedido = "
    SELECT
        p.*,
        u.nombre AS usuario_nombre,
        u.email AS usuario_email
    FROM pedidos p
    LEFT JOIN usuarios u ON p.usuario_id = u.id
    WHERE p.id = :id
    LIMIT 1
";

$stmtPedido = $pdo->prepare($sqlPedido);
$stmtPedido->bindParam(':id', $id, PDO::PARAM_INT);
$stmtPedido->execute();
$pedido = $stmtPedido->fetch();

if (!$pedido) {
    redirect('/proyecto_cava_Noble/admin/pedidos.php');
}

$sqlItems = "
    SELECT
        pi.*,
        pr.imagen,
        pr.cepa,
        b.nombre AS bodega_nombre,
        c.nombre AS categoria
    FROM pedido_items pi
    LEFT JOIN productos pr ON pi.producto_id = pr.id
    LEFT JOIN bodegas b ON pr.bodega_id = b.id
    LEFT JOIN categorias c ON pr.categoria_id = c.id
    WHERE pi.pedido_id = :pedido_id
    ORDER BY pi.id ASC
";

$stmtItems = $pdo->prepare($sqlItems);
$stmtItems->bindParam(':pedido_id', $id, PDO::PARAM_INT);
$stmtItems->execute();
$items = $stmtItems->fetchAll();

$csrfToken = generateCsrfToken();

include '../includes/header.php';
?>

<main class="section">
    <div class="container">

        <div class="section-header">
            <h2>Detalle del pedido #<?php echo (int)$pedido['id']; ?></h2>
            <p>Información completa del pedido y productos asociados.</p>
        </div>

        <div class="info-cards">
            <div class="info-card">
                <span class="admin-badge">Cliente</span>
                <h3><?php echo e($pedido['nombre_cliente']); ?></h3>
                <p><?php echo e($pedido['email_cliente']); ?></p>
                <p><?php echo e($pedido['telefono']); ?></p>
            </div>

            <div class="info-card">
                <span class="admin-badge">Entrega</span>
                <h3><?php echo e($pedido['ciudad']); ?></h3>
                <p><?php echo e($pedido['direccion']); ?></p>
                <p><?php echo e($pedido['provincia']); ?></p>
            </div>

            <div class="info-card">
                <span class="admin-badge">Compra</span>
                <h3>$<?php echo number_format($pedido['total'], 0, ',', '.'); ?></h3>
                <p><?php echo e($pedido['metodo_pago']); ?></p>
                <p><?php echo e($pedido['fecha_pedido']); ?></p>
            </div>
        </div>

        <br><br>

        <div class="form-container" style="max-width: 700px;">
            <h2>Actualizar estado</h2>

            <form action="/proyecto_cava_Noble/admin/actualizar-estado-pedido.php" method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
                <input type="hidden" name="pedido_id" value="<?php echo (int)$pedido['id']; ?>">

                <div class="form-group">
                    <label>Estado actual</label>
                    <select name="estado" required>
                        <option value="pendiente" <?php if ($pedido['estado'] === 'pendiente') echo 'selected'; ?>>Pendiente</option>
                        <option value="confirmado" <?php if ($pedido['estado'] === 'confirmado') echo 'selected'; ?>>Confirmado</option>
                        <option value="preparado" <?php if ($pedido['estado'] === 'preparado') echo 'selected'; ?>>Preparado</option>
                        <option value="entregado" <?php if ($pedido['estado'] === 'entregado') echo 'selected'; ?>>Entregado</option>
                        <option value="cancelado" <?php if ($pedido['estado'] === 'cancelado') echo 'selected'; ?>>Cancelado</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    Guardar estado
                </button>
            </form>
        </div>

        <br><br>

        <div class="cart-box" style="max-width:100%;">
            <h2>Productos del pedido</h2>
            <br>

            <?php if (empty($items)): ?>
                <p>Este pedido no tiene productos asociados.</p>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <div class="cart-item">
                        <div style="display:flex; gap:20px; align-items:center;">
                            <?php if (!empty($item['imagen'])): ?>
                                <img
                                    src="<?php echo e($item['imagen']); ?>"
                                    alt="<?php echo e($item['nombre_producto']); ?>"
                                    style="width:90px; height:110px; object-fit:cover;"
                                >
                            <?php endif; ?>

                            <div>
                                <h3><?php echo e($item['nombre_producto']); ?></h3>
                                <p>
                                    <?php echo e($item['bodega_nombre'] ?? 'Sin bodega'); ?>
                                    ·
                                    <?php echo e($item['categoria'] ?? 'Sin categoría'); ?>
                                </p>
                                <p><strong>Cantidad:</strong> <?php echo (int)$item['cantidad']; ?></p>
                                <p><strong>Precio unitario:</strong> $<?php echo number_format($item['precio_unitario'], 0, ',', '.'); ?></p>
                            </div>
                        </div>

                        <strong>
                            $<?php echo number_format($item['subtotal'], 0, ',', '.'); ?>
                        </strong>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="cart-total">
                <h3>Total</h3>
                <span>$<?php echo number_format($pedido['total'], 0, ',', '.'); ?></span>
            </div>
        </div>

        <br>

        <a href="/proyecto_cava_Noble/admin/pedidos.php" class="btn btn-secondary">
            Volver a pedidos
        </a>

    </div>
</main>

<?php include '../includes/footer.php'; ?>