<?php

require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../includes/captcha.php';
require_once '../config/database.php';

requireLogin();

$pdo = conectarDB();
$csrfToken = generateCsrfToken();

/*
|--------------------------------------------------------------------------
| Token único del checkout
|--------------------------------------------------------------------------
*/

if (
    empty($_SESSION['checkout_token']) ||
    !is_string($_SESSION['checkout_token'])
) {
    $_SESSION['checkout_token'] = bin2hex(random_bytes(32));
}

$checkoutToken = $_SESSION['checkout_token'];

/*
|--------------------------------------------------------------------------
| Mensaje técnico solamente en desarrollo
|--------------------------------------------------------------------------
*/

$checkoutDebugError = '';

if (
    defined('APP_DEBUG') &&
    APP_DEBUG === true &&
    !empty($_SESSION['checkout_debug_error'])
) {
    $checkoutDebugError = (string)$_SESSION['checkout_debug_error'];
}

unset($_SESSION['checkout_debug_error']);

/*
|--------------------------------------------------------------------------
| Recuperar carrito desde la base de datos
|--------------------------------------------------------------------------
*/

$itemsCarrito = [];
$totalGeneral = 0.0;

if (
    !empty($_SESSION['carrito']) &&
    is_array($_SESSION['carrito'])
) {
    $sqlProducto = "
        SELECT
            id,
            nombre,
            precio,
            imagen,
            stock
        FROM productos
        WHERE id = :id
        LIMIT 1
    ";

    $stmtProducto = $pdo->prepare($sqlProducto);

    foreach ($_SESSION['carrito'] as $productoId => $item) {
        $productoId = (int)$productoId;
        $cantidadSolicitada = (int)($item['cantidad'] ?? 0);

        if ($productoId <= 0 || $cantidadSolicitada <= 0) {
            continue;
        }

        $stmtProducto->bindValue(
            ':id',
            $productoId,
            PDO::PARAM_INT
        );

        $stmtProducto->execute();

        $producto = $stmtProducto->fetch();

        if (!$producto) {
            unset($_SESSION['carrito'][$productoId]);
            continue;
        }

        $stockDisponible = (int)$producto['stock'];

        if ($stockDisponible <= 0) {
            unset($_SESSION['carrito'][$productoId]);
            continue;
        }

        $cantidad = min(
            $cantidadSolicitada,
            $stockDisponible
        );

        $_SESSION['carrito'][$productoId]['cantidad'] = $cantidad;

        $precioUnitario = round(
            (float)$producto['precio'],
            2
        );

        $subtotal = round(
            $precioUnitario * $cantidad,
            2
        );

        $totalGeneral = round(
            $totalGeneral + $subtotal,
            2
        );

        $itemsCarrito[] = [
            'id' => (int)$producto['id'],
            'nombre' => (string)$producto['nombre'],
            'precio' => $precioUnitario,
            'imagen' => trim((string)($producto['imagen'] ?? '')),
            'stock' => $stockDisponible,
            'cantidad' => $cantidad,
            'subtotal' => $subtotal
        ];
    }
}

if (empty($itemsCarrito)) {
    unset($_SESSION['checkout_token']);

    redirect('/proyecto_cava_Noble/carrito.php');
}

/*
|--------------------------------------------------------------------------
| Datos precargados
|--------------------------------------------------------------------------
*/

$nombreUsuario = trim(
    (string)($_SESSION['usuario_nombre'] ?? '')
);

$apellidoUsuario = trim(
    (string)($_SESSION['usuario_apellido'] ?? '')
);

$nombreCompletoUsuario = trim(
    $nombreUsuario . ' ' . $apellidoUsuario
);

$emailUsuario = trim(
    (string)($_SESSION['usuario_email'] ?? '')
);

include '../includes/header.php';
?>

<main class="section">
    <div class="container">

        <div class="section-header">
            <span class="section-kicker">
                Checkout
            </span>

            <h2>Finalizar compra</h2>

            <p>
                Completá los datos de entrega y revisá tu pedido
                antes de confirmar la compra.
            </p>
        </div>

        <div class="admin-report-grid">

            <section
                class="form-container"
                style="max-width:100%;"
            >
                <h2>Datos del comprador</h2>

                <p>
                    La información será utilizada para coordinar
                    la entrega o el retiro del pedido.
                </p>

                <br>

                <?php if (isset($_GET['error'])): ?>
                    <div class="delete-warning">
                        No se pudo procesar la compra. Revisá los datos
                        ingresados e intentá nuevamente.
                    </div>

                    <br>
                <?php endif; ?>

                <?php if (isset($_GET['stock'])): ?>
                    <div class="delete-warning">
                        Uno o más productos ya no tienen stock suficiente.
                        Volvé al carrito para revisar las cantidades.
                    </div>

                    <br>
                <?php endif; ?>

                <?php if (isset($_GET['captcha'])): ?>
                    <div class="delete-warning">
                        No se pudo completar la comprobación de seguridad.
                        Recargá la página e intentá nuevamente.
                    </div>

                    <br>
                <?php endif; ?>

                <?php if ($checkoutDebugError !== ''): ?>
                    <div class="delete-warning">
                        <strong>Error técnico de desarrollo:</strong><br>
                        <?php echo e($checkoutDebugError); ?>
                    </div>

                    <br>
                <?php endif; ?>

                <form
                    action="/proyecto_cava_Noble/pages/procesar-checkout.php"
                    method="POST"
                    class="auth-form"
                    autocomplete="on"
                >
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php echo e($csrfToken); ?>"
                    >

                    <input
                        type="hidden"
                        name="checkout_token"
                        value="<?php echo e($checkoutToken); ?>"
                    >

                    <div class="form-group">
                        <label for="nombre_cliente">
                            Nombre completo
                        </label>

                        <input
                            type="text"
                            id="nombre_cliente"
                            name="nombre_cliente"
                            value="<?php echo e($nombreCompletoUsuario); ?>"
                            maxlength="100"
                            autocomplete="name"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="email_cliente">
                            Email
                        </label>

                        <input
                            type="email"
                            id="email_cliente"
                            name="email_cliente"
                            value="<?php echo e($emailUsuario); ?>"
                            maxlength="150"
                            autocomplete="email"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="telefono">
                            Teléfono
                        </label>

                        <input
                            type="tel"
                            id="telefono"
                            name="telefono"
                            maxlength="50"
                            autocomplete="tel"
                            placeholder="Ej.: 351 555 1234"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="direccion">
                            Dirección
                        </label>

                        <textarea
                            id="direccion"
                            name="direccion"
                            rows="4"
                            maxlength="200"
                            autocomplete="street-address"
                            placeholder="Calle, número, piso y referencias"
                            required
                        ></textarea>
                    </div>

                    <div class="form-group">
                        <label for="ciudad">
                            Ciudad
                        </label>

                        <input
                            type="text"
                            id="ciudad"
                            name="ciudad"
                            maxlength="100"
                            autocomplete="address-level2"
                            placeholder="Ej.: Córdoba"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="provincia">
                            Provincia
                        </label>

                        <input
                            type="text"
                            id="provincia"
                            name="provincia"
                            maxlength="100"
                            autocomplete="address-level1"
                            placeholder="Ej.: Córdoba"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="metodo_pago">
                            Método de pago
                        </label>

                        <select
                            id="metodo_pago"
                            name="metodo_pago"
                            required
                        >
                            <option value="">
                                Seleccionar método
                            </option>

                            <option value="transferencia">
                                Transferencia bancaria
                            </option>

                            <option value="efectivo">
                                Efectivo al retirar
                            </option>
                        </select>
                    </div>

                    <?php renderTurnstileWidget(); ?>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Confirmar compra
                    </button>
                </form>
            </section>

            <section
                class="cart-box"
                style="max-width:100%;"
            >
                <h2>Resumen del pedido</h2>

                <p>
                    Verificá las cantidades y el total antes de continuar.
                </p>

                <br>

                <?php foreach ($itemsCarrito as $item): ?>

                    <div class="cart-item">

                        <div
                            style="
                                display:flex;
                                align-items:center;
                                gap:16px;
                            "
                        >
                            <?php if ($item['imagen'] !== ''): ?>
                                <img
                                    src="<?php echo e($item['imagen']); ?>"
                                    alt="<?php echo e($item['nombre']); ?>"
                                >
                            <?php endif; ?>

                            <div>
                                <h3>
                                    <?php echo e($item['nombre']); ?>
                                </h3>

                                <p>
                                    <strong>Cantidad:</strong>
                                    <?php echo (int)$item['cantidad']; ?>
                                </p>

                                <p>
                                    <strong>Precio unitario:</strong>

                                    $<?php
                                    echo number_format(
                                        (float)$item['precio'],
                                        0,
                                        ',',
                                        '.'
                                    );
                                    ?>
                                </p>
                            </div>
                        </div>

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

                <?php endforeach; ?>

                <div class="cart-total">
                    <h3>Total</h3>

                    <span>
                        $<?php
                        echo number_format(
                            $totalGeneral,
                            0,
                            ',',
                            '.'
                        );
                        ?>
                    </span>
                </div>

                <br>

                <div
                    style="
                        display:flex;
                        gap:12px;
                        flex-wrap:wrap;
                    "
                >
                    <a
                        href="/proyecto_cava_Noble/carrito.php"
                        class="btn btn-secondary"
                    >
                        Volver al carrito
                    </a>

                    <a
                        href="/proyecto_cava_Noble/pages/catalogo.php"
                        class="btn btn-secondary"
                    >
                        Seguir comprando
                    </a>
                </div>
            </section>

        </div>

    </div>
</main>

<?php include '../includes/footer.php'; ?>