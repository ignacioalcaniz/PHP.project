<?php
require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../config/database.php';

requireAdmin();

$pdo = conectarDB();

$csrfToken = generateCsrfToken();

$filtroResultado = $_GET['resultado'] ?? 'todos';

$whereAttempts = '';

if ($filtroResultado === 'exitosos') {
    $whereAttempts = 'WHERE success = 1';
} elseif ($filtroResultado === 'fallidos') {
    $whereAttempts = 'WHERE success = 0';
}

$sqlKpis = "
    SELECT
        (SELECT COUNT(*) FROM login_attempts WHERE success = 0) AS total_fallidos,
        (SELECT COUNT(*) FROM login_attempts WHERE success = 1) AS total_exitosos,
        (SELECT COUNT(*) FROM suspicious_ips) AS total_sospechosas,
        (SELECT COUNT(*) FROM suspicious_ips WHERE blocked_until IS NOT NULL AND blocked_until > NOW()) AS total_bloqueadas
";

$stmtKpis = $pdo->prepare($sqlKpis);
$stmtKpis->execute();
$kpis = $stmtKpis->fetch();

$sqlAttempts = "
    SELECT
        email,
        ip_address,
        success,
        attempted_at
    FROM login_attempts
    $whereAttempts
    ORDER BY attempted_at DESC
    LIMIT 100
";

$stmtAttempts = $pdo->prepare($sqlAttempts);
$stmtAttempts->execute();
$attempts = $stmtAttempts->fetchAll();

$sqlIps = "
    SELECT *
    FROM suspicious_ips
    ORDER BY updated_at DESC
";

$stmtIps = $pdo->prepare($sqlIps);
$stmtIps->execute();
$ips = $stmtIps->fetchAll();

include '../includes/header.php';
?>

<main class="section admin-shell">
    <div class="container">

        <div class="section-header">
            <span class="section-kicker">Seguridad</span>
            <h2>Centro de seguridad</h2>
            <p>
                Monitoreo de intentos de acceso, IPs sospechosas,
                bloqueos temporales y actividad de autenticación.
            </p>
        </div>

        <div class="admin-kpi-grid">
            <div class="admin-kpi-card">
                <span>Login fallidos</span>
                <strong><?php echo (int)$kpis['total_fallidos']; ?></strong>
                <small>Intentos incorrectos registrados</small>
            </div>

            <div class="admin-kpi-card">
                <span>Login exitosos</span>
                <strong><?php echo (int)$kpis['total_exitosos']; ?></strong>
                <small>Accesos válidos registrados</small>
            </div>

            <div class="admin-kpi-card">
                <span>IPs sospechosas</span>
                <strong><?php echo (int)$kpis['total_sospechosas']; ?></strong>
                <small>IPs con actividad anómala</small>
            </div>

            <div class="admin-kpi-card">
                <span>Bloqueadas</span>
                <strong><?php echo (int)$kpis['total_bloqueadas']; ?></strong>
                <small>Bloqueos temporales activos</small>
            </div>
        </div>

        <br><br>

        <section class="admin-report-panel">
            <div class="admin-panel-header">
                <div>
                    <h2>IPs sospechosas</h2>
                    <p>Direcciones IP marcadas por múltiples intentos fallidos.</p>
                </div>
            </div>

            <?php if (empty($ips)): ?>
                <div class="admin-empty-state">
                    <h3>Sin IPs sospechosas</h3>
                    <p>El sistema no detectó actividad sospechosa por ahora.</p>
                </div>
            <?php else: ?>
                <div class="admin-table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>IP</th>
                                <th>Motivo</th>
                                <th>Intentos</th>
                                <th>Estado</th>
                                <th>Actualización</th>
                                <th>Acción</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($ips as $ip): ?>
                                <?php
                                    $bloqueada =
                                        !empty($ip['blocked_until']) &&
                                        strtotime($ip['blocked_until']) > time();
                                ?>

                                <tr>
                                    <td><strong><?php echo e($ip['ip_address']); ?></strong></td>
                                    <td><?php echo e($ip['reason']); ?></td>
                                    <td><?php echo (int)$ip['attempts']; ?></td>

                                    <td>
                                        <?php if ($bloqueada): ?>
                                            <span class="admin-badge">Bloqueada hasta <?php echo e($ip['blocked_until']); ?></span>
                                        <?php else: ?>
                                            Sin bloqueo activo
                                        <?php endif; ?>
                                    </td>

                                    <td><?php echo e($ip['updated_at']); ?></td>

                                    <td>
                                        <?php if ($bloqueada): ?>
                                            <form
                                                action="/proyecto_cava_Noble/admin/desbloquear-ip.php"
                                                method="POST"
                                            >
                                                <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
                                                <input type="hidden" name="id" value="<?php echo (int)$ip['id']; ?>">

                                                <button type="submit" class="admin-action-btn admin-action-edit">
                                                    Desbloquear
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <br><br>

        <section class="admin-report-panel">
            <div class="admin-panel-header">
                <div>
                    <h2>Últimos intentos de login</h2>
                    <p>Registro de accesos exitosos y fallidos.</p>
                </div>

                <div class="admin-product-actions">
                    <a
                        href="/proyecto_cava_Noble/admin/seguridad.php?resultado=todos"
                        class="admin-action-btn <?php echo $filtroResultado === 'todos' ? 'admin-action-delete' : 'admin-action-edit'; ?>"
                    >
                        Todos
                    </a>

                    <a
                        href="/proyecto_cava_Noble/admin/seguridad.php?resultado=fallidos"
                        class="admin-action-btn <?php echo $filtroResultado === 'fallidos' ? 'admin-action-delete' : 'admin-action-edit'; ?>"
                    >
                        Fallidos
                    </a>

                    <a
                        href="/proyecto_cava_Noble/admin/seguridad.php?resultado=exitosos"
                        class="admin-action-btn <?php echo $filtroResultado === 'exitosos' ? 'admin-action-delete' : 'admin-action-edit'; ?>"
                    >
                        Exitosos
                    </a>
                </div>
            </div>

            <?php if (empty($attempts)): ?>
                <div class="admin-empty-state">
                    <h3>Sin intentos registrados</h3>
                    <p>No hay intentos para el filtro seleccionado.</p>
                </div>
            <?php else: ?>
                <div class="admin-table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Email</th>
                                <th>IP</th>
                                <th>Resultado</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($attempts as $attempt): ?>
                                <tr>
                                    <td><?php echo e($attempt['attempted_at']); ?></td>
                                    <td><?php echo e($attempt['email']); ?></td>
                                    <td><?php echo e($attempt['ip_address']); ?></td>
                                    <td>
                                        <?php if ((int)$attempt['success'] === 1): ?>
                                            <span class="admin-badge">Exitoso</span>
                                        <?php else: ?>
                                            <span class="admin-badge">Fallido</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <br>

        <a href="/proyecto_cava_Noble/admin/index.php" class="btn btn-secondary">
            Volver al panel
        </a>

    </div>
</main>

<?php include '../includes/footer.php'; ?>