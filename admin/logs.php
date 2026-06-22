<?php

require_once '../includes/auth.php';
require_once '../config/database.php';

requireAdmin();

$pdo = conectarDB();

$sql = "
    SELECT
        l.*,
        u.nombre AS admin_nombre
    FROM admin_logs l
    INNER JOIN usuarios u
        ON l.admin_id = u.id
    ORDER BY l.created_at DESC
    LIMIT 500
";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$logs = $stmt->fetchAll();

include '../includes/header.php';
?>

<main class="section">
    <div class="container">

        <div class="section-header">
            <h2>Auditoría del sistema</h2>
            <p>
                Registro de acciones administrativas realizadas
                dentro de Cava Noble.
            </p>
        </div>

        <div class="cart-box" style="max-width:100%;">

            <?php if (empty($logs)): ?>

                <p>No existen registros todavía.</p>

            <?php else: ?>

                <table style="
                    width:100%;
                    border-collapse:collapse;
                ">

                    <thead>

                        <tr style="
                            background:#f5f5f5;
                        ">

                            <th style="padding:12px;">Fecha</th>
                            <th style="padding:12px;">Administrador</th>
                            <th style="padding:12px;">Acción</th>
                            <th style="padding:12px;">Entidad</th>
                            <th style="padding:12px;">ID</th>
                            <th style="padding:12px;">Descripción</th>
                            <th style="padding:12px;">IP</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($logs as $log): ?>

                            <tr style="
                                border-bottom:1px solid #ececec;
                            ">

                                <td style="padding:12px;">
                                    <?php echo e($log['created_at']); ?>
                                </td>

                                <td style="padding:12px;">
                                    <?php echo e($log['admin_nombre']); ?>
                                </td>

                                <td style="padding:12px;">

                                    <span
                                        class="admin-badge"
                                    >
                                        <?php echo e($log['accion']); ?>
                                    </span>

                                </td>

                                <td style="padding:12px;">
                                    <?php echo e($log['entidad']); ?>
                                </td>

                                <td style="padding:12px;">
                                    <?php echo (int)$log['entidad_id']; ?>
                                </td>

                                <td style="padding:12px;">
                                    <?php echo e($log['descripcion']); ?>
                                </td>

                                <td style="padding:12px;">
                                    <?php echo e($log['ip_address']); ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            <?php endif; ?>

        </div>

    </div>
</main>

<?php include '../includes/footer.php'; ?>