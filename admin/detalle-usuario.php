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

<main class="section admin-shell">
    <div class="container">

        <div class="section-header">
            <span class="section-kicker">Clientes</span>
            <h2>Detalle de usuario</h2>
            <p>Información del cliente, permisos, actividad comercial e historial de pedidos.</p>
        </div>

        <div class="admin-detail-layout">

            <section class="admin-profile-card">
                <div class="admin-profile-avatar">
                    <?php echo e(strtoupper(substr($usuario['nombre'], 0, 1))); ?>
                </div>

                <div>
                    <span class="admin-badge"><?php echo e($usuario['rol']); ?></span>
                    <h3><?php echo e($usuario['nombre'] . ' ' . $usuario['apellido']); ?></h3>
                    <p><?php echo e($usuario['email']); ?></p>
                </div>
            </section>

            <section class="admin-profile-card">
                <span class="admin-badge">Compras</span>
                <h3><?php echo (int)$usuario['total_pedidos']; ?> pedidos</h3>
                <p>Total gastado: <strong>$<?php echo number_format($usuario['total_gastado'], 0, ',', '.'); ?></strong></p>
            </section>

            <section class="admin-profile-card">
                <span class="admin-badge">Seguridad</span>
                <h3>Permisos del usuario</h3>
                <p>El acceso al panel se define mediante el rol asignado.</p>
            </section>

        </div>

        <br><br>

        <div class="admin-report-panel">
            <div class="admin-panel-header">
                <div>
                    <h2>Actualizar rol</h2>
                    <p>Modificá los permisos del usuario dentro del sistema.</p>
                </div>
            </div>

            <form action="/proyecto_cava_Noble/admin/actualizar-rol-usuario.php" method="POST" class="auth-form admin-role-form">
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

        <div class="admin-report-panel">
            <div class="admin-panel-header">
                <div>
                    <h2>Historial de pedidos</h2>
                    <p>Pedidos realizados por este usuario.</p>
                </div>
            </div>

            <?php if (empty($pedidos)): ?>

                <div class="admin-empty-state">
                    <h3>Sin pedidos todavía</h3>
                    <p>Este usuario no tiene pedidos registrados.</p>
                </div>

            <?php else: ?>

                <div class="admin-list">
                    <?php foreach ($pedidos as $pedido): ?>
                        <article class="admin-list-row">
                            <div>
                                <h3>Pedido #<?php echo (int)$pedido['id']; ?></h3>
                                <p><strong>Fecha:</strong> <?php echo e($pedido['fecha_pedido']); ?></p>
                                <p><strong>Método de pago:</strong> <?php echo e($pedido['metodo_pago']); ?></p>
                            </div>

                            <div class="admin-list-actions">
                                <span class="admin-badge"><?php echo e($pedido['estado']); ?></span>
                                <strong>$<?php echo number_format($pedido['total'], 0, ',', '.'); ?></strong>

                                <a
                                    href="/proyecto_cava_Noble/admin/detalle-pedido.php?id=<?php echo (int)$pedido['id']; ?>"
                                    class="btn-card"
                                >
                                    Ver pedido
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>
        </div>

        <br>

        <a href="/proyecto_cava_Noble/admin/usuarios.php" class="btn btn-secondary">
            Volver a usuarios
        </a>

    </div>
</main>

<?php include '../includes/footer.php'; ?>