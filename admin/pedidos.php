<?php
require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../config/database.php';

requireAdmin();

$pdo = conectarDB();

$estado = trim($_GET['estado'] ?? '');

$sql = "
    SELECT
        p.*,
        u.nombre AS usuario_nombre,
        u.email AS usuario_email
    FROM pedidos p
    LEFT JOIN usuarios u ON p.usuario_id = u.id
    WHERE 1=1
";

$params = [];

if ($estado !== '') {
    $sql .= " AND p.estado = :estado";
    $params[':estado'] = $estado;
}

$sql .= " ORDER BY p.fecha_pedido DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pedidos = $stmt->fetchAll();

include '../includes/header.php';
?>

<main class="section">
    <div class="container">

        <div class="section-header">
            <h2>Pedidos</h2>
            <p>Gestión de compras generadas en Cava Noble.</p>
        </div>

        <div class="form-container" style="max-width: 900px; margin-bottom: 35px;">
            <h2>Filtrar pedidos</h2>

            <form method="GET" action="/proyecto_cava_Noble/admin/pedidos.php" class="auth-form">
                <div class="form-group">
                    <label>Estado</label>
                    <select name="estado">
                        <option value="">Todos</option>
                        <option value="pendiente" <?php if ($estado === 'pendiente') echo 'selected'; ?>>Pendiente</option>
                        <option value="confirmado" <?php if ($estado === 'confirmado') echo 'selected'; ?>>Confirmado</option>
                        <option value="preparado" <?php if ($estado === 'preparado') echo 'selected'; ?>>Preparado</option>
                        <option value="entregado" <?php if ($estado === 'entregado') echo 'selected'; ?>>Entregado</option>
                        <option value="cancelado" <?php if ($estado === 'cancelado') echo 'selected'; ?>>Cancelado</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Aplicar filtro</button>
                <a href="/proyecto_cava_Noble/admin/pedidos.php" class="btn btn-secondary">Limpiar</a>
            </form>
        </div>

        <div class="cart-box" style="max-width:100%;">
            <?php if (empty($pedidos)): ?>
                <p>No hay pedidos para mostrar.</p>
            <?php else: ?>
                <?php foreach ($pedidos as $pedido): ?>
                    <div class="cart-item">
                        <div>
                            <h3>Pedido #<?php echo (int)$pedido['id']; ?></h3>
                            <p><strong>Cliente:</strong> <?php echo e($pedido['nombre_cliente']); ?></p>
                            <p><strong>Email:</strong> <?php echo e($pedido['email_cliente']); ?></p>
                            <p><strong>Fecha:</strong> <?php echo e($pedido['fecha_pedido']); ?></p>
                            <p><strong>Total:</strong> $<?php echo number_format($pedido['total'], 0, ',', '.'); ?></p>
                        </div>

                        <div style="display:flex; flex-direction:column; gap:10px; align-items:flex-end;">
                            <span class="admin-badge">
                                <?php echo e($pedido['estado']); ?>
                            </span>

                            <a
                                href="/proyecto_cava_Noble/admin/detalle-pedido.php?id=<?php echo (int)$pedido['id']; ?>"
                                class="btn btn-primary"
                            >
                                Ver detalle
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <br>

        <a href="/proyecto_cava_Noble/admin/index.php" class="btn btn-secondary">
            Volver al panel
        </a>

    </div>
</main>

<?php include '../includes/footer.php'; ?>