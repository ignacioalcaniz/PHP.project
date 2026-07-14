<?php

require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../config/database.php';

requireAdmin();

$pdo = conectarDB();

$id = isset($_GET['id'])
    ? (int)$_GET['id']
    : 0;

if ($id <= 0) {
    redirect('/proyecto_cava_Noble/admin/pedidos.php');
}

/*
|--------------------------------------------------------------------------
| Pedido
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

$stmtPedido->bindParam(
    ':id',
    $id,
    PDO::PARAM_INT
);

$stmtPedido->execute();

$pedido = $stmtPedido->fetch();

if (!$pedido) {
    redirect('/proyecto_cava_Noble/admin/pedidos.php');
}

/*
|--------------------------------------------------------------------------
| Items del pedido
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

$stmtItems->bindParam(
    ':pedido_id',
    $id,
    PDO::PARAM_INT
);

$stmtItems->execute();

$items = $stmtItems->fetchAll();

$csrfToken = generateCsrfToken();

$ciudad = trim((string)($pedido['ciudad'] ?? ''));
$provincia = trim((string)($pedido['provincia'] ?? ''));
$telefono = trim((string)($pedido['telefono'] ?? ''));
$direccion = trim((string)($pedido['direccion'] ?? ''));

include '../includes/header.php';
?>

<main class="section admin-shell">
    <div class="container">

        <div class="section-header">
            <span class="section-kicker">
                Ventas
            </span>

            <h2>
                Detalle del pedido #<?php echo (int)$pedido['id']; ?>
            </h2>

            <p>
                Información comercial, datos de entrega,
                productos y seguimiento operativo.
            </p>
        </div>

        <!-- RESUMEN -->

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
                    <?php
                    echo $telefono !== ''
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
                    <?php
                    echo $ciudad !== ''
                        ? e($ciudad)
                        : 'Entrega o retiro';
                    ?>
                </h3>

                <p>
                    <?php
                    echo $direccion !== ''
                        ? e($direccion)
                        : 'Dirección no informada';
                    ?>
                </p>

                <p>
                    <?php
                    echo $provincia !== ''
                        ? e($provincia)
                        : 'Provincia no informada';
                    ?>
                </p>
            </article>

            <article class="admin-profile-card">
                <span class="admin-badge">
                    <?php echo e(ucfirst($pedido['estado'])); ?>
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
                    <?php echo e(ucfirst($pedido['metodo_pago'])); ?>
                </p>

                <p>
                    <?php echo e($pedido['fecha_pedido']); ?>
                </p>
            </article>

        </div>

        <br><br>

        <!-- ACTUALIZACIÓN DE ESTADO -->

        <section class="admin-report-panel">

            <div class="admin-panel-header">
                <div>
                    <h2>Seguimiento del pedido</h2>

                    <p>
                        Actualizá el estado operativo de la compra.
                        Los pedidos entregados se consideran finalizados.
                    </p>
                </div>

                <span class="admin-badge">
                    Estado actual:
                    <?php echo e(ucfirst($pedido['estado'])); ?>
                </span>
            </div>

            <?php if (isset($_GET['actualizado'])): ?>
                <div class="admin-empty-state">
                    <h3>Estado actualizado</h3>

                    <p>
                        El cambio se guardó correctamente y fue registrado
                        en la auditoría administrativa.
                    </p>
                </div>

                <br>
            <?php endif; ?>

            <?php if (isset($_GET['stock_error'])): ?>
                <div class="delete-warning">
                    No se pudo reactivar este pedido porque no hay stock
                    suficiente para uno o más productos.
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
                    value="<?php echo (int)$pedido['id']; ?>"
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
                        <option
                            value="pendiente"
                            <?php echo $pedido['estado'] === 'pendiente' ? 'selected' : ''; ?>
                        >
                            Pendiente
                        </option>

                        <option
                            value="procesando"
                            <?php echo $pedido['estado'] === 'procesando' ? 'selected' : ''; ?>
                        >
                            Procesando
                        </option>

                        <option
                            value="confirmado"
                            <?php echo $pedido['estado'] === 'confirmado' ? 'selected' : ''; ?>
                        >
                            Confirmado
                        </option>

                        <option
                            value="preparado"
                            <?php echo $pedido['estado'] === 'preparado' ? 'selected' : ''; ?>
                        >
                            Preparado
                        </option>

                        <option
                            value="despachado"
                            <?php echo $pedido['estado'] === 'despachado' ? 'selected' : ''; ?>
                        >
                            Despachado
                        </option>

                        <option
                            value="entregado"
                            <?php echo $pedido['estado'] === 'entregado' ? 'selected' : ''; ?>
                        >
                            Entregado / finalizado
                        </option>

                        <option
                            value="cancelado"
                            <?php echo $pedido['estado'] === 'cancelado' ? 'selected' : ''; ?>
                        >
                            Cancelado
                        </option>
                    </select>
                </div>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Guardar estado
                </button>
            </form>

        </section>

        <br><br>

        <!-- PRODUCTOS -->

        <section class="admin-report-panel">

            <div class="admin-panel-header">
                <div>
                    <h2>Productos del pedido</h2>

                    <p>
                        Detalle de artículos, imágenes, cantidades y precios.
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
                        $precioUnitario =
                            $item['precio_unitario']
                            ?? $item['precio']
                            ?? 0;
                        ?>

                        <article class="admin-list-row">

                            <div class="admin-product-main">

                                <?php if (!empty($item['imagen'])): ?>
                                    <img
                                        src="<?php echo e($item['imagen']); ?>"
                                        alt="<?php echo e($item['nombre_producto']); ?>"
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
                                        <?php echo e($item['nombre_producto']); ?>
                                    </h3>

                                    <p>
                                        <?php
                                        echo e(
                                            $item['bodega_nombre']
                                            ?? 'Sin bodega'
                                        );
                                        ?>
                                        ·
                                        <?php
                                        echo e(
                                            $item['categoria']
                                            ?? 'Sin categoría'
                                        );
                                        ?>
                                    </p>

                                    <?php if (!empty($item['cepa'])): ?>
                                        <p>
                                            <strong>Cepa:</strong>
                                            <?php echo e($item['cepa']); ?>
                                        </p>
                                    <?php endif; ?>

                                    <p>
                                        <strong>Cantidad:</strong>
                                        <?php echo (int)$item['cantidad']; ?>
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
                                    <?php echo (int)$item['cantidad']; ?>
                                    unidades
                                </span>

                                <strong>
                                    $<?php
                                    echo number_format(
                                        (float)$item['subtotal'],
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

        </div>

    </div>
</main>

<?php include '../includes/footer.php'; ?>