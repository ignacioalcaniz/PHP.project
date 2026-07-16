<?php

require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../config/database.php';

requireAdmin();

$pdo = conectarDB();

$pedidoId = isset($_GET['id'])
    ? (int)$_GET['id']
    : 0;

if ($pedidoId <= 0) {
    redirect('/proyecto_cava_Noble/admin/pedidos.php?vista=activos');
}

/*
|--------------------------------------------------------------------------
| Obtener pedido
|--------------------------------------------------------------------------
*/

$sqlPedido = "
    SELECT
        p.*,
        u.nombre AS usuario_nombre,
        u.apellido AS usuario_apellido,
        u.email AS usuario_email
    FROM pedidos p
    LEFT JOIN usuarios u
        ON p.usuario_id = u.id
    WHERE p.id = :id
    LIMIT 1
";

$stmtPedido = $pdo->prepare($sqlPedido);

$stmtPedido->bindValue(
    ':id',
    $pedidoId,
    PDO::PARAM_INT
);

$stmtPedido->execute();

$pedido = $stmtPedido->fetch();

if (!$pedido) {
    redirect('/proyecto_cava_Noble/admin/pedidos.php?vista=activos');
}

/*
|--------------------------------------------------------------------------
| Obtener productos del pedido
|--------------------------------------------------------------------------
*/

$sqlItems = "
    SELECT
        pi.*,
        pr.imagen,
        pr.cepa,
        pr.stock AS stock_actual,
        b.nombre AS bodega_nombre,
        c.nombre AS categoria
    FROM pedido_items pi
    LEFT JOIN productos pr
        ON pi.producto_id = pr.id
    LEFT JOIN bodegas b
        ON pr.bodega_id = b.id
    LEFT JOIN categorias c
        ON pr.categoria_id = c.id
    WHERE pi.pedido_id = :pedido_id
    ORDER BY pi.id ASC
";

$stmtItems = $pdo->prepare($sqlItems);

$stmtItems->bindValue(
    ':pedido_id',
    $pedidoId,
    PDO::PARAM_INT
);

$stmtItems->execute();

$items = $stmtItems->fetchAll();

/*
|--------------------------------------------------------------------------
| Datos auxiliares
|--------------------------------------------------------------------------
*/

$csrfToken = generateCsrfToken();

$estadoActual = (string)($pedido['estado'] ?? 'procesando');

$estadosPermitidos = [
    'pendiente' => 'Pendiente',
    'procesando' => 'Procesando',
    'confirmado' => 'Confirmado',
    'preparado' => 'Preparado',
    'despachado' => 'Despachado',
    'entregado' => 'Entregado / finalizado',
    'cancelado' => 'Cancelado'
];

$estadoLabel = $estadosPermitidos[$estadoActual]
    ?? ucfirst($estadoActual);

$pedidoPuedeFinalizar = !in_array(
    $estadoActual,
    ['entregado', 'cancelado'],
    true
);

$pedidoEstaFinalizado = $estadoActual === 'entregado';
$pedidoEstaCancelado = $estadoActual === 'cancelado';

$ciudad = trim((string)($pedido['ciudad'] ?? ''));
$provincia = trim((string)($pedido['provincia'] ?? ''));
$telefono = trim((string)($pedido['telefono'] ?? ''));
$direccion = trim((string)($pedido['direccion'] ?? ''));
$metodoPago = trim((string)($pedido['metodo_pago'] ?? ''));

include '../includes/header.php';
?>

<main class="section admin-shell">
    <div class="container">

        <div class="section-header">
            <span class="section-kicker">
                Ventas
            </span>

            <h2>
                Detalle del pedido #<?php echo $pedidoId; ?>
            </h2>

            <p>
                Información comercial, datos de entrega,
                productos asociados y seguimiento operativo.
            </p>
        </div>

        <!-- RESUMEN GENERAL -->

        <div class="admin-detail-layout">

            <article class="admin-profile-card">
                <span class="admin-badge">
                    Cliente
                </span>

                <h3>
                    <?php echo e($pedido['nombre_cliente']); ?>
                </h3>

                <p>
                    <?php echo e($pedido['email_cliente']); ?>
                </p>

                <p>
                    <?php echo $telefono !== ''
                        ? e($telefono)
                        : 'Teléfono no informado';
                    ?>
                </p>
            </article>

            <article class="admin-profile-card">
                <span class="admin-badge">
                    Entrega
                </span>

                <h3>
                    <?php echo $ciudad !== ''
                        ? e($ciudad)
                        : 'Entrega o retiro';
                    ?>
                </h3>

                <p>
                    <?php echo $direccion !== ''
                        ? e($direccion)
                        : 'Dirección no informada';
                    ?>
                </p>

                <p>
                    <?php echo $provincia !== ''
                        ? e($provincia)
                        : 'Provincia no informada';
                    ?>
                </p>
            </article>

            <article class="admin-profile-card">
                <span
                    class="
                        admin-badge
                        order-status
                        order-status-<?php echo e($estadoActual); ?>
                    "
                >
                    <?php echo e($estadoLabel); ?>
                </span>

                <h3>
                    $<?php
                    echo number_format(
                        (float)$pedido['total'],
                        0,
                        ',',
                        '.'
                    );
                    ?>
                </h3>

                <p>
                    <?php echo $metodoPago !== ''
                        ? e(ucfirst($metodoPago))
                        : 'Método de pago no informado';
                    ?>
                </p>

                <p>
                    <?php echo e($pedido['fecha_pedido']); ?>
                </p>
            </article>

        </div>

        <br><br>

        <!-- MENSAJES -->

        <?php if (isset($_GET['actualizado'])): ?>
            <div class="admin-empty-state">
                <h3>Estado actualizado</h3>

                <p>
                    El cambio fue guardado correctamente y registrado
                    en la auditoría administrativa.
                </p>
            </div>

            <br>
        <?php endif; ?>

        <?php if (isset($_GET['stock_error'])): ?>
            <div class="delete-warning">
                No se pudo reactivar el pedido porque uno o más productos
                no tienen stock suficiente.
            </div>

            <br>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="delete-warning">
                No se pudo actualizar el pedido. Revisá los datos
                e intentá nuevamente.
            </div>

            <br>
        <?php endif; ?>

        <!-- SEGUIMIENTO DEL PEDIDO -->

        <section class="admin-report-panel">

            <div class="admin-panel-header">
                <div>
                    <h2>Seguimiento del pedido</h2>

                    <p>
                        Administrá el flujo operativo de la compra.
                        Los pedidos entregados se consideran finalizados.
                    </p>
                </div>

                <span
                    class="
                        admin-badge
                        order-status
                        order-status-<?php echo e($estadoActual); ?>
                    "
                >
                    Estado actual: <?php echo e($estadoLabel); ?>
                </span>
            </div>

            <?php if ($pedidoEstaFinalizado): ?>

                <div class="admin-empty-state">
                    <h3>Pedido finalizado</h3>

                    <p>
                        Este pedido fue entregado correctamente y forma
                        parte del historial de pedidos finalizados.
                    </p>
                </div>

            <?php elseif ($pedidoEstaCancelado): ?>

                <div class="delete-warning">
                    Este pedido se encuentra cancelado. Si se reactiva,
                    el sistema comprobará nuevamente el stock y descontará
                    las unidades correspondientes.
                </div>

            <?php endif; ?>

            <form
                action="/proyecto_cava_Noble/admin/actualizar-estado-pedido.php"
                method="POST"
                class="auth-form admin-role-form"
            >
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo e($csrfToken); ?>"
                >

                <input
                    type="hidden"
                    name="pedido_id"
                    value="<?php echo $pedidoId; ?>"
                >

                <input
                    type="hidden"
                    name="accion"
                    value="actualizar"
                >

                <div class="form-group">
                    <label for="estado">
                        Nuevo estado
                    </label>

                    <select
                        id="estado"
                        name="estado"
                        required
                    >
                        <?php foreach ($estadosPermitidos as $valorEstado => $etiquetaEstado): ?>
                            <option
                                value="<?php echo e($valorEstado); ?>"
                                <?php echo $estadoActual === $valorEstado
                                    ? 'selected'
                                    : '';
                                ?>
                            >
                                <?php echo e($etiquetaEstado); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Guardar estado
                </button>
            </form>

            <?php if ($pedidoPuedeFinalizar): ?>

                <br>

                <div class="delete-warning">
                    La acción rápida marcará el pedido como entregado
                    y lo moverá al listado de pedidos finalizados.
                </div>

                <form
                    action="/proyecto_cava_Noble/admin/actualizar-estado-pedido.php"
                    method="POST"
                    class="admin-quick-action-form"
                >
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php echo e($csrfToken); ?>"
                    >

                    <input
                        type="hidden"
                        name="pedido_id"
                        value="<?php echo $pedidoId; ?>"
                    >

                    <input
                        type="hidden"
                        name="estado"
                        value="entregado"
                    >

                    <input
                        type="hidden"
                        name="accion"
                        value="finalizar"
                    >

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Finalizar pedido
                    </button>
                </form>

            <?php endif; ?>

        </section>

        <br><br>

        <!-- PRODUCTOS DEL PEDIDO -->

        <section class="admin-report-panel">

            <div class="admin-panel-header">
                <div>
                    <h2>Productos del pedido</h2>

                    <p>
                        Detalle de artículos, imágenes, cantidades,
                        precios y subtotales.
                    </p>
                </div>

                <span class="admin-badge">
                    <?php echo count($items); ?> productos
                </span>
            </div>

            <?php if (empty($items)): ?>

                <div class="admin-empty-state">
                    <h3>Pedido sin productos</h3>

                    <p>
                        Este pedido no tiene ítems asociados.
                    </p>
                </div>

            <?php else: ?>

                <div class="admin-list">

                    <?php foreach ($items as $item): ?>

                        <?php
                        $precioUnitario = $item['precio_unitario']
                            ?? $item['precio']
                            ?? 0;

                        $nombreProducto = (string)(
                            $item['nombre_producto']
                            ?? 'Producto'
                        );

                        $imagenProducto = trim(
                            (string)($item['imagen'] ?? '')
                        );

                        $bodegaProducto = (string)(
                            $item['bodega_nombre']
                            ?? 'Sin bodega'
                        );

                        $categoriaProducto = (string)(
                            $item['categoria']
                            ?? 'Sin categoría'
                        );

                        $cantidadProducto = (int)(
                            $item['cantidad']
                            ?? 0
                        );

                        $subtotalProducto = (float)(
                            $item['subtotal']
                            ?? 0
                        );
                        ?>

                        <article class="admin-list-row">

                            <div class="admin-product-main">

                                <?php if ($imagenProducto !== ''): ?>

                                    <img
                                        src="<?php echo e($imagenProducto); ?>"
                                        alt="<?php echo e($nombreProducto); ?>"
                                        style="
                                            width: 90px;
                                            height: 120px;
                                            object-fit: contain;
                                            background: #ffffff;
                                            padding: 8px;
                                            border-radius: 16px;
                                            flex-shrink: 0;
                                        "
                                    >

                                <?php else: ?>

                                    <div class="admin-icon">
                                        🍷
                                    </div>

                                <?php endif; ?>

                                <div class="admin-product-info">

                                    <h3>
                                        <?php echo e($nombreProducto); ?>
                                    </h3>

                                    <p>
                                        <?php echo e($bodegaProducto); ?>
                                        ·
                                        <?php echo e($categoriaProducto); ?>
                                    </p>

                                    <?php if (!empty($item['cepa'])): ?>
                                        <p>
                                            <strong>Cepa:</strong>
                                            <?php echo e($item['cepa']); ?>
                                        </p>
                                    <?php endif; ?>

                                    <p>
                                        <strong>Cantidad:</strong>
                                        <?php echo $cantidadProducto; ?>
                                    </p>

                                    <p>
                                        <strong>Precio unitario:</strong>

                                        $<?php
                                        echo number_format(
                                            (float)$precioUnitario,
                                            0,
                                            ',',
                                            '.'
                                        );
                                        ?>
                                    </p>

                                </div>
                            </div>

                            <div class="admin-list-actions">

                                <span class="admin-badge">
                                    <?php echo $cantidadProducto; ?>
                                    unidades
                                </span>

                                <strong>
                                    $<?php
                                    echo number_format(
                                        $subtotalProducto,
                                        0,
                                        ',',
                                        '.'
                                    );
                                    ?>
                                </strong>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

            <div class="cart-total">
                <h3>Total del pedido</h3>

                <span>
                    $<?php
                    echo number_format(
                        (float)$pedido['total'],
                        0,
                        ',',
                        '.'
                    );
                    ?>
                </span>
            </div>

        </section>

        <br>

        <!-- NAVEGACIÓN -->

        <div class="admin-toolbar">

            <a
                href="/proyecto_cava_Noble/admin/pedidos.php?vista=activos"
                class="btn btn-secondary"
            >
                Volver a pedidos activos
            </a>

            <a
                href="/proyecto_cava_Noble/admin/pedidos.php?vista=finalizados"
                class="btn btn-secondary"
            >
                Ver pedidos finalizados
            </a>

            <a
                href="/proyecto_cava_Noble/admin/pedidos.php?vista=todos"
                class="btn btn-secondary"
            >
                Ver todos los pedidos
            </a>

        </div>

    </div>
</main>

<?php include '../includes/footer.php'; ?>