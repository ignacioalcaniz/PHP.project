<?php

require_once '../includes/auth.php';
require_once '../config/database.php';

requireAdmin();

$pdo = conectarDB();

$sql = "
    SELECT
        l.*,
        u.nombre AS admin_nombre,
        u.apellido AS admin_apellido,
        u.email AS admin_email
    FROM admin_logs l
    INNER JOIN usuarios u ON l.admin_id = u.id
    ORDER BY l.created_at DESC
    LIMIT 500
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$logs = $stmt->fetchAll();

include '../includes/header.php';
?>

<main class="section admin-shell">
    <div class="container">

        <div class="section-header">
            <span class="section-kicker">Seguridad</span>
            <h2>Auditoría del sistema</h2>
            <p>Registro de acciones administrativas realizadas dentro de Cava Noble.</p>
        </div>

        <div class="admin-report-panel">

            <?php if (empty($logs)): ?>

                <div class="admin-empty-state">
                    <h3>No existen registros todavía</h3>
                    <p>Cuando un administrador cree, edite o elimine información, aparecerá en esta sección.</p>
                </div>

            <?php else: ?>

                <div class="admin-table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Administrador</th>
                                <th>Acción</th>
                                <th>Entidad</th>
                                <th>ID</th>
                                <th>Descripción</th>
                                <th>IP</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo e($log['created_at']); ?></td>

                                    <td>
                                        <strong>
                                            <?php echo e(trim(($log['admin_nombre'] ?? '') . ' ' . ($log['admin_apellido'] ?? ''))); ?>
                                        </strong>
                                        <small><?php echo e($log['admin_email']); ?></small>
                                    </td>

                                    <td>
                                        <span class="admin-badge">
                                            <?php echo e($log['accion']); ?>
                                        </span>
                                    </td>

                                    <td><?php echo e($log['entidad']); ?></td>

                                    <td>
                                        <?php echo $log['entidad_id'] !== null ? (int)$log['entidad_id'] : '-'; ?>
                                    </td>

                                    <td><?php echo e($log['descripcion']); ?></td>

                                    <td><?php echo e($log['ip_address']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php endif; ?>

        </div>

        <br>

        <a href="/proyecto_cava_Noble/admin/index.php" class="btn btn-secondary">
            Volver al panel
        </a>

    </div>
</main>

<?php include '../includes/footer.php'; ?>