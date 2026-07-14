<?php

require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../config/database.php';

requireAdmin();

$pdo = conectarDB();

/*
|--------------------------------------------------------------------------
| Filtros permitidos
|--------------------------------------------------------------------------
*/

$vista = trim($_GET['vista'] ?? 'activos');
$estado = trim($_GET['estado'] ?? '');

$vistasPermitidas = [
    'todos',
    'activos',
    'finalizados',
    'cancelados'
];

$estadosPermitidos = [
    'pendiente',
    'procesando',
    'confirmado',
    'preparado',
    'despachado',
    'entregado',
    'cancelado'
];

if (!in_array($vista, $vistasPermitidas, true)) {
    $vista = 'activos';
}

if (
    $estado !== '' &&
    !in_array($estado, $estadosPermitidos, true)
) {
    $estado = '';
}

/*
|--------------------------------------------------------------------------
| KPIs
|--------------------------------------------------------------------------
*/

$sqlKpis = "
    SELECT
        COUNT(*) AS total_pedidos,

        SUM(
            CASE
                WHEN estado IN (
                    'pendiente',
                    'procesando',
                    'confirmado',
                    'preparado',
                    'despachado'
                )
                THEN 1
                ELSE 0
            END
        ) AS pedidos_activos,

        SUM(
            CASE
                WHEN estado = 'entregado'
                THEN 1
                ELSE 0
            END
        ) AS pedidos_finalizados,

        SUM(
            CASE
                WHEN estado = 'cancelado'
                THEN 1
                ELSE 0
            END
        ) AS pedidos_cancelados,

        COALESCE(
            SUM(
                CASE
                    WHEN estado <> 'cancelado'
                    THEN total
                    ELSE 0
                END
            ),
            0
        ) AS facturacion_total
    FROM pedidos
";

$stmtKpis = $pdo->prepare($sqlKpis);
$stmtKpis->execute();

$kpis = $stmtKpis->fetch();

/*
|--------------------------------------------------------------------------
| Consulta de pedidos
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        p.*,
        u.nombre AS usuario_nombre,
        u.apellido AS usuario_apellido,
        u.email AS usuario_email,

        (
            SELECT COALESCE(SUM(pi.cantidad), 0)
            FROM pedido_items pi
            WHERE pi.pedido_id = p.id
        ) AS total_unidades,

        (
            SELECT COUNT(*)
            FROM pedido_items pi
            WHERE pi.pedido_id = p.id
        ) AS total_items

    FROM pedidos p

    LEFT JOIN usuarios u
        ON p.usuario_id = u.id

    WHERE 1 = 1
";

$params = [];

if ($vista === 'activos') {
    $sql .= "
        AND p.estado IN (
            'pendiente',
            'procesando',
            'confirmado',
            'preparado',
            'despachado'
        )
    ";
}

if ($vista === 'finalizados') {
    $sql .= "
        AND p.estado = 'entregado'
    ";
}

if ($vista === 'cancelados') {
    $sql .= "
        AND p.estado = 'cancelado'
    ";
}

if ($estado !== '') {
    $sql .= " AND p.estado = :estado";
    $params[':estado'] = $estado;
}

$sql .= " ORDER BY p.fecha_pedido DESC, p.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$pedidos = $stmt->fetchAll();

include '../includes/header.php';
?>

<main class="section admin-shell">
    <div class="container">

        <div class="section-header">
            <span class="section-kicker">
                Ventas
            </span>

            <h2>Gestión de pedidos</h2>

            <p>
                Administrá las compras realizadas desde el checkout,
                controlá su preparación y consultá los pedidos finalizados.
            </p>
        </div>

        <!-- BOTONERA SOLICITADA POR LA CONSIGNA -->

        <div class="admin-toolbar">

            <a
                href="/proyecto_cava_Noble/pages/catalogo.php"
                class="btn btn-primary"
            >
                Realizar pedido
            </a>

            <a
                href="/proyecto_cava_Noble/admin/pedidos.php?vista=activos"
                class="btn btn-secondary"
            >
                Ver pedidos activos
            </a>

            <a
                href="/proyecto_cava_Noble/admin/pedidos.php?vista=finalizados"
                class="btn btn-secondary"
            >
                Pedidos finalizados
            </a>

        </div>

        <!-- KPIs -->

        <div class="admin-kpi-grid">

            <article class="admin-kpi-card">
                <span>Total pedidos</span>

                <strong>
                    <?php echo (int)($kpis['total_pedidos'] ?? 0); ?>
                </strong>

                <small>
                    Pedidos registrados en el sistema
                </small>
            </article>

            <article class="admin-kpi-card">
                <span>En proceso</span>

                <strong>
                    <?php echo (int)($kpis['pedidos_activos'] ?? 0); ?>
                </strong>

                <small>
                    Pendientes de finalización
                </small>
            </article>

            <article class="admin-kpi-card">
                <span>Finalizados</span>

                <strong>
                    <?php echo (int)($kpis['pedidos_finalizados'] ?? 0); ?>
                </strong>

                <small>
                    Pedidos entregados
                </small>
            </article>

            <article class="admin-kpi-card">
                <span>Facturación</span>

                <strong>
                    $<?php
                    echo number_format(
                        (float)($kpis['facturacion_total'] ?? 0),
                        0,
                        ',',
                        '.'
                    );
                    ?>
                </strong>

                <small>
                    Pedidos no cancelados
                </small>
            </article>

        </div>

        <br><br>

        <!-- MENSAJES -->

        <?php if (isset($_GET['actualizado'])): ?>
            <div class="admin-empty-state">
                <h3>Estado actualizado</h3>
                <p>
                    El estado del pedido se modificó correctamente.
                </p>
            </div>

            <br>
        <?php endif; ?>

        <?php if (isset($_GET['stock_error'])): ?>
            <div class="delete-warning">
                No se pudo reactivar el pedido porque uno o más productos
                no tienen stock suficiente.
            </div>
        <?php endif; ?>

        <!-- FILTROS -->

        <section class="admin-report-panel">

            <div class="admin-panel-header">
                <div>
                    <h2>Filtrar pedidos</h2>

                    <p>
                        Buscá pedidos por vista operativa o por un estado específico.
                    </p>
                </div>
            </div>

            <form
                method="GET"
                action="/proyecto_cava_Noble/admin/pedidos.php"
                class="auth-form"
            >
                <div class="form-group">
                    <label for="vista">Vista</label>

                    <select id="vista" name="vista">
                        <option
                            value="todos"
                            <?php echo $vista === 'todos' ? 'selected' : ''; ?>
                        >
                            Todos los pedidos
                        </option>

                        <option
                            value="activos"
                            <?php echo $vista === 'activos' ? 'selected' : ''; ?>
                        >
                            Pedidos activos
                        </option>

                        <option
                            value="finalizados"
                            <?php echo $vista === 'finalizados' ? 'selected' : ''; ?>
                        >
                            Pedidos finalizados
                        </option>

                        <option
                            value="cancelados"
                            <?php echo $vista === 'cancelados' ? 'selected' : ''; ?>
                        >
                            Pedidos cancelados
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="estado">Estado específico</label>

                    <select id="estado" name="estado">
                        <option value="">
                            Todos los estados
                        </option>

                        <option
                            value="pendiente"
                            <?php echo $estado === 'pendiente' ? 'selected' : ''; ?>
                        >
                            Pendiente
                        </option>

                        <option
                            value="procesando"
                            <?php echo $estado === 'procesando' ? 'selected' : ''; ?>
                        >
                            Procesando
                        </option>

                        <option
                            value="confirmado"
                            <?php echo $estado === 'confirmado' ? 'selected' : ''; ?>
                        >
                            Confirmado
                        </option>

                        <option
                            value="preparado"
                            <?php echo $estado === 'preparado' ? 'selected' : ''; ?>
                        >
                            Preparado
                        </option>

                        <option
                            value="despachado"
                            <?php echo $estado === 'despachado' ? 'selected' : ''; ?>
                        >
                            Despachado
                        </option>

                        <option
                            value="entregado"
                            <?php echo $estado === 'entregado' ? 'selected' : ''; ?>
                        >
                            Entregado
                        </option>

                        <option
                            value="cancelado"
                            <?php echo $estado === 'cancelado' ? 'selected' : ''; ?>
                        >
                            Cancelado
                        </option>
                    </select>
                </div>

                <div class="admin-toolbar">
                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Aplicar filtros
                    </button>

                    <a
                        href="/proyecto_cava_Noble/admin/pedidos.php?vista=activos"
                        class="btn btn-secondary"
                    >
                        Limpiar filtros
                    </a>
                </div>
            </form>

        </section>

        <br><br>

        <!-- LISTADO -->

        <section class="admin-report-panel">

            <div class="admin-panel-header">
                <div>
                    <h2>Pedidos registrados</h2>

                    <p>
                        Resultado de la vista y los filtros seleccionados.
                    </p>
                </div>

                <span class="admin-badge">
                    <?php echo count($pedidos); ?> resultados
                </span>
            </div>

            <?php if (empty($pedidos)): ?>

                <div class="admin-empty-state">
                    <h3>No hay pedidos para mostrar</h3>

                    <p>
                        No existen pedidos que coincidan con los filtros seleccionados.
                    </p>
                </div>

            <?php else: ?>

                <div class="admin-list">

                    <?php foreach ($pedidos as $pedido): ?>

                        <article class="admin-list-row">

                            <div>
                                <span class="admin-badge">
                                    <?php echo e(ucfirst($pedido['estado'])); ?>
                                </span>

                                <h3>
                                    Pedido #<?php echo (int)$pedido['id']; ?>
                                </h3>

                                <p>
                                    <strong>Cliente:</strong>
                                    <?php echo e($pedido['nombre_cliente']); ?>
                                </p>

                                <p>
                                    <strong>Email:</strong>
                                    <?php echo e($pedido['email_cliente']); ?>
                                </p>

                                <p>
                                    <strong>Fecha:</strong>
                                    <?php echo e($pedido['fecha_pedido']); ?>
                                </p>

                                <p>
                                    <strong>Productos:</strong>
                                    <?php echo (int)$pedido['total_items']; ?>
                                    tipos ·
                                    <?php echo (int)$pedido['total_unidades']; ?>
                                    unidades
                                </p>
                            </div>

                            <div class="admin-list-actions">

                                <strong>
                                    $<?php
                                    echo number_format(
                                        (float)$pedido['total'],
                                        0,
                                        ',',
                                        '.'
                                    );
                                    ?>
                                </strong>

                                <a
                                    href="/proyecto_cava_Noble/admin/detalle-pedido.php?id=<?php echo (int)$pedido['id']; ?>"
                                    class="admin-action-btn admin-action-edit"
                                >
                                    Ver detalle
                                </a>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </section>

        <br>

        <a
            href="/proyecto_cava_Noble/admin/index.php"
            class="btn btn-secondary"
        >
            Volver al panel
        </a>

    </div>
</main>

<?php include '../includes/footer.php'; ?>