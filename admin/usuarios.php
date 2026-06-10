<?php
require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../config/database.php';

requireAdmin();

$pdo = conectarDB();

$rol = trim($_GET['rol'] ?? '');

$sql = "
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
    WHERE 1=1
";

$params = [];

if ($rol !== '') {
    $sql .= " AND u.rol = :rol";
    $params[':rol'] = $rol;
}

$sql .= "
    GROUP BY u.id, u.nombre, u.apellido, u.email, u.rol
    ORDER BY u.id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$usuarios = $stmt->fetchAll();

include '../includes/header.php';
?>

<main class="section">
    <div class="container">
        <div class="section-header">
            <h2>Usuarios</h2>
            <p>Gestión de clientes, administradores y actividad comercial.</p>
        </div>

        <div class="form-container" style="max-width:900px; margin-bottom:35px;">
            <h2>Filtrar usuarios</h2>

            <form method="GET" action="/proyecto_cava_Noble/admin/usuarios.php" class="auth-form">
                <div class="form-group">
                    <label>Rol</label>
                    <select name="rol">
                        <option value="">Todos</option>
                        <option value="cliente" <?php if ($rol === 'cliente') echo 'selected'; ?>>Cliente</option>
                        <option value="admin" <?php if ($rol === 'admin') echo 'selected'; ?>>Admin</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Aplicar filtro</button>
                <a href="/proyecto_cava_Noble/admin/usuarios.php" class="btn btn-secondary">Limpiar</a>
            </form>
        </div>

        <div class="cart-box" style="max-width:100%;">
            <?php if (empty($usuarios)): ?>
                <p>No hay usuarios para mostrar.</p>
            <?php else: ?>
                <?php foreach ($usuarios as $usuario): ?>
                    <div class="cart-item">
                        <div>
                            <h3><?php echo e($usuario['nombre'] . ' ' . $usuario['apellido']); ?></h3>
                            <p><strong>Email:</strong> <?php echo e($usuario['email']); ?></p>
                            <p><strong>Pedidos:</strong> <?php echo (int)$usuario['total_pedidos']; ?></p>
                            <p><strong>Total gastado:</strong> $<?php echo number_format($usuario['total_gastado'], 0, ',', '.'); ?></p>
                        </div>

                        <div style="display:flex; flex-direction:column; gap:10px; align-items:flex-end;">
                            <span class="admin-badge">
                                <?php echo e($usuario['rol']); ?>
                            </span>

                            <a
                                href="/proyecto_cava_Noble/admin/detalle-usuario.php?id=<?php echo (int)$usuario['id']; ?>"
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