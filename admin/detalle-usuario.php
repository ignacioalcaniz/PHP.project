<?php
require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../config/database.php';

requireAdmin();

$pdo = conectarDB();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    redirect('/proyecto_cava_Noble/admin/usuarios.php');
}

$sqlUsuario = "
    SELECT
        u.id,
        u.nombre,
        u.apellido,
        u.email,
        u.rol,
        COUNT(p.id) AS total_pedidos,
        COALESCE(SUM(p.total), 0) AS total_gastado
    FROM usuarios u
    LEFT JOIN pedidos p ON p.usuario_id = u.id
    WHERE u.id = :id
    GROUP BY u.id, u.nombre, u.apellido, u.email, u.rol
    LIMIT 1
";

$stmtUsuario = $pdo->prepare($sqlUsuario);
$stmtUsuario->bindParam(':id', $id, PDO::PARAM_INT);
$stmtUsuario->execute();
$usuario = $stmtUsuario->fetch();

if (!$usuario) {
    redirect('/proyecto_cava_Noble/admin/usuarios.php');
}

$sqlPedidos = "
    SELECT
        id,
        estado,
        metodo_pago,
        total,
        fecha_pedido
    FROM pedidos
    WHERE usuario_id = :id
    ORDER BY fecha_pedido DESC
";

$stmtPedidos = $pdo->prepare($sqlPedidos);
$stmtPedidos->bindParam(':id', $id, PDO::PARAM_INT);
$stmtPedidos->execute();
$pedidos = $stmtPedidos->fetchAll();

$csrfToken = generateCsrfToken();

include '../includes/header.php';
?>

<main class="section">
    <div class="container">

        <div class="section-header">
            <h2>Detalle de usuario</h2>
            <p>Información del cliente, permisos y actividad comercial.</p>
        </div>

        <div class="info-cards">
            <div class="info-card">
                <span class="admin-badge">Usuario</span>
                <h3><?php echo e($usuario['nombre'] . ' ' . $usuario['apellido']); ?></h3>
                <p><?php echo e($usuario['email']); ?></p>
                <p><strong>Rol actual:</strong> <?php echo e($usuario['rol']); ?></p>
            </div>

            <div class="info-card">
                <span class="admin-badge">Compras</span>
                <h3><?php echo (int)$usuario['total_pedidos']; ?> pedidos</h3>
                <p>Total gastado: $<?php echo number_format($usuario['total_gastado'], 0, ',', '.'); ?></p>
            </div>

            <div class="info-card">
                <span class="admin-badge">Seguridad</span>
                <h3>Gestión de rol</h3>
                <p>Los permisos del panel dependen del rol del usuario.</p>
            </div>
        </div>

        <br><br>

        <div class="form-container" style="max-width:700px;">
            <h2>Actualizar rol</h2>

            <form action="/proyecto_cava_Noble/admin/actualizar-rol-usuario.php" method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
                <input type="hidden" name="usuario_id" value="<?php echo (int)$usuario['id']; ?>">

                <div class="form-group">
                    <label>Rol</label>
                    <select name="rol" required>
                        <option value="cliente" <?php if ($usuario['rol'] === 'cliente') echo 'selected'; ?>>Cliente</option>
                        <option value="admin" <?php if ($usuario['rol'] === 'admin') echo 'selected'; ?>>Admin</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    Guardar rol
                </button>
            </form>
        </div>

        <br><br>

        <div class="cart-box" style="max-width:100%;">
            <h2>Historial de pedidos</h2>
            <br>

            <?php if (empty($pedidos)): ?>
                <p>Este usuario todavía no tiene pedidos registrados.</p>
            <?php else: ?>
                <?php foreach ($pedidos as $pedido): ?>
                    <div class="cart-item">
                        <div>
                            <h3>Pedido #<?php echo (int)$pedido['id']; ?></h3>
                            <p><strong>Fecha:</strong> <?php echo e($pedido['fecha_pedido']); ?></p>
                            <p><strong>Método de pago:</strong> <?php echo e($pedido['metodo_pago']); ?></p>
                        </div>

                        <div style="display:flex; flex-direction:column; gap:10px; align-items:flex-end;">
                            <span class="admin-badge"><?php echo e($pedido['estado']); ?></span>
                            <strong>$<?php echo number_format($pedido['total'], 0, ',', '.'); ?></strong>

                            <a
                                href="/proyecto_cava_Noble/admin/detalle-pedido.php?id=<?php echo (int)$pedido['id']; ?>"
                                class="btn-card"
                            >
                                Ver pedido
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <br>

        <a href="/proyecto_cava_Noble/admin/usuarios.php" class="btn btn-secondary">
            Volver a usuarios
        </a>
    </div>
</main>

<?php include '../includes/footer.php'; ?>