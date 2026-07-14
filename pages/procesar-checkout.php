<?php

require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../includes/captcha.php';
require_once '../config/database.php';

requireLogin();

/*
|--------------------------------------------------------------------------
| Solo solicitudes POST
|--------------------------------------------------------------------------
*/

if (!isPostRequest()) {
    redirect('/proyecto_cava_Noble/carrito.php');
}

/*
|--------------------------------------------------------------------------
| Protección CSRF
|--------------------------------------------------------------------------
*/

$csrfToken = $_POST['csrf_token'] ?? '';

if (
    !is_string($csrfToken) ||
    $csrfToken === '' ||
    !validateCsrfToken($csrfToken)
) {
    http_response_code(403);
    die('Token CSRF inválido.');
}

/*
|--------------------------------------------------------------------------
| Token único del checkout
|--------------------------------------------------------------------------
|
| Evita que el mismo formulario pueda enviarse dos veces accidentalmente.
|
*/

$checkoutTokenPost = $_POST['checkout_token'] ?? '';
$checkoutTokenSession = $_SESSION['checkout_token'] ?? '';

if (
    !is_string($checkoutTokenPost) ||
    !is_string($checkoutTokenSession) ||
    $checkoutTokenPost === '' ||
    $checkoutTokenSession === '' ||
    !hash_equals($checkoutTokenSession, $checkoutTokenPost)
) {
    redirect('/proyecto_cava_Noble/carrito.php');
}

/*
|--------------------------------------------------------------------------
| Verificación Cloudflare Turnstile
|--------------------------------------------------------------------------
*/

if (!verifyTurnstileToken()) {
    redirect('/proyecto_cava_Noble/pages/checkout.php?captcha=1');
}

/*
|--------------------------------------------------------------------------
| Verificar carrito
|--------------------------------------------------------------------------
*/

if (
    empty($_SESSION['carrito']) ||
    !is_array($_SESSION['carrito'])
) {
    redirect('/proyecto_cava_Noble/carrito.php');
}

$pdo = conectarDB();

/*
|--------------------------------------------------------------------------
| Datos del comprador
|--------------------------------------------------------------------------
*/

$usuarioId = (int)($_SESSION['usuario_id'] ?? 0);

$nombreCliente = trim((string)($_POST['nombre_cliente'] ?? ''));
$emailCliente = strtolower(
    trim((string)($_POST['email_cliente'] ?? ''))
);
$telefono = trim((string)($_POST['telefono'] ?? ''));
$direccion = trim((string)($_POST['direccion'] ?? ''));
$metodoPago = trim((string)($_POST['metodo_pago'] ?? ''));

$metodosPagoPermitidos = [
    'transferencia',
    'efectivo'
];

if (
    $usuarioId <= 0 ||
    $nombreCliente === '' ||
    $emailCliente === '' ||
    $telefono === '' ||
    $direccion === '' ||
    $metodoPago === '' ||
    !filter_var($emailCliente, FILTER_VALIDATE_EMAIL) ||
    !in_array($metodoPago, $metodosPagoPermitidos, true)
) {
    redirect('/proyecto_cava_Noble/pages/checkout.php?error=1');
}

/*
|--------------------------------------------------------------------------
| Límites básicos de longitud
|--------------------------------------------------------------------------
*/

if (
    mb_strlen($nombreCliente) > 150 ||
    mb_strlen($emailCliente) > 190 ||
    mb_strlen($telefono) > 50 ||
    mb_strlen($direccion) > 500
) {
    redirect('/proyecto_cava_Noble/pages/checkout.php?error=1');
}

try {
    /*
    |--------------------------------------------------------------------------
    | Inicio de transacción
    |--------------------------------------------------------------------------
    */

    $pdo->beginTransaction();

    $itemsFinales = [];
    $totalGeneral = 0.0;

    /*
    |--------------------------------------------------------------------------
    | Validación y bloqueo de productos
    |--------------------------------------------------------------------------
    */

    foreach ($_SESSION['carrito'] as $productoId => $itemCarrito) {
        $productoId = (int)$productoId;
        $cantidad = (int)($itemCarrito['cantidad'] ?? 0);

        if ($productoId <= 0 || $cantidad <= 0) {
            continue;
        }

        $sqlProducto = "
            SELECT
                id,
                nombre,
                precio,
                stock
            FROM productos
            WHERE id = :id
            LIMIT 1
            FOR UPDATE
        ";

        $stmtProducto = $pdo->prepare($sqlProducto);

        $stmtProducto->bindValue(
            ':id',
            $productoId,
            PDO::PARAM_INT
        );

        $stmtProducto->execute();

        $producto = $stmtProducto->fetch();

        if (!$producto) {
            $pdo->rollBack();

            redirect(
                '/proyecto_cava_Noble/pages/checkout.php?stock=1'
            );
        }

        $stockDisponible = (int)$producto['stock'];

        if (
            $stockDisponible <= 0 ||
            $stockDisponible < $cantidad
        ) {
            $pdo->rollBack();

            redirect(
                '/proyecto_cava_Noble/pages/checkout.php?stock=1'
            );
        }

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

        $itemsFinales[] = [
            'producto_id' => (int)$producto['id'],
            'nombre_producto' => (string)$producto['nombre'],
            'precio' => $precioUnitario,
            'cantidad' => $cantidad,
            'subtotal' => $subtotal
        ];
    }

    if (empty($itemsFinales) || $totalGeneral <= 0) {
        $pdo->rollBack();

        redirect('/proyecto_cava_Noble/carrito.php');
    }

    /*
    |--------------------------------------------------------------------------
    | Crear pedido
    |--------------------------------------------------------------------------
    |
    | Se inicia como "procesando", cumpliendo la consigna académica y
    | manteniendo un flujo profesional de estados.
    |
    */

    $estadoInicial = 'procesando';

    $sqlPedido = "
        INSERT INTO pedidos (
            usuario_id,
            nombre_cliente,
            email_cliente,
            telefono,
            direccion,
            metodo_pago,
            estado,
            total
        )
        VALUES (
            :usuario_id,
            :nombre_cliente,
            :email_cliente,
            :telefono,
            :direccion,
            :metodo_pago,
            :estado,
            :total
        )
    ";

    $stmtPedido = $pdo->prepare($sqlPedido);

    $stmtPedido->bindValue(
        ':usuario_id',
        $usuarioId,
        PDO::PARAM_INT
    );

    $stmtPedido->bindValue(
        ':nombre_cliente',
        $nombreCliente,
        PDO::PARAM_STR
    );

    $stmtPedido->bindValue(
        ':email_cliente',
        $emailCliente,
        PDO::PARAM_STR
    );

    $stmtPedido->bindValue(
        ':telefono',
        $telefono,
        PDO::PARAM_STR
    );

    $stmtPedido->bindValue(
        ':direccion',
        $direccion,
        PDO::PARAM_STR
    );

    $stmtPedido->bindValue(
        ':metodo_pago',
        $metodoPago,
        PDO::PARAM_STR
    );

    $stmtPedido->bindValue(
        ':estado',
        $estadoInicial,
        PDO::PARAM_STR
    );

    $stmtPedido->bindValue(
        ':total',
        $totalGeneral
    );

    $stmtPedido->execute();

    $pedidoId = (int)$pdo->lastInsertId();

    if ($pedidoId <= 0) {
        throw new RuntimeException(
            'No se pudo obtener el ID del pedido.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Preparar consultas reutilizables
    |--------------------------------------------------------------------------
    */

    $sqlItem = "
        INSERT INTO pedido_items (
            pedido_id,
            producto_id,
            nombre_producto,
            precio,
            cantidad,
            subtotal
        )
        VALUES (
            :pedido_id,
            :producto_id,
            :nombre_producto,
            :precio,
            :cantidad,
            :subtotal
        )
    ";

    $stmtItem = $pdo->prepare($sqlItem);

    $sqlStock = "
        UPDATE productos
        SET stock = stock - :cantidad
        WHERE id = :producto_id
        AND stock >= :cantidad
    ";

    $stmtStock = $pdo->prepare($sqlStock);

    /*
    |--------------------------------------------------------------------------
    | Guardar ítems y descontar stock
    |--------------------------------------------------------------------------
    */

    foreach ($itemsFinales as $item) {
        $stmtItem->bindValue(
            ':pedido_id',
            $pedidoId,
            PDO::PARAM_INT
        );

        $stmtItem->bindValue(
            ':producto_id',
            $item['producto_id'],
            PDO::PARAM_INT
        );

        $stmtItem->bindValue(
            ':nombre_producto',
            $item['nombre_producto'],
            PDO::PARAM_STR
        );

        $stmtItem->bindValue(
            ':precio',
            $item['precio']
        );

        $stmtItem->bindValue(
            ':cantidad',
            $item['cantidad'],
            PDO::PARAM_INT
        );

        $stmtItem->bindValue(
            ':subtotal',
            $item['subtotal']
        );

        $stmtItem->execute();

        /*
        | Descontamos stock solo si continúa siendo suficiente.
        | Aunque la fila ya está bloqueada, esta condición agrega defensa extra.
        */

        $stmtStock->bindValue(
            ':cantidad',
            $item['cantidad'],
            PDO::PARAM_INT
        );

        $stmtStock->bindValue(
            ':producto_id',
            $item['producto_id'],
            PDO::PARAM_INT
        );

        $stmtStock->execute();

        if ($stmtStock->rowCount() !== 1) {
            throw new RuntimeException(
                'No se pudo actualizar el stock del producto #' .
                $item['producto_id']
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Confirmar transacción
    |--------------------------------------------------------------------------
    */

    $pdo->commit();

    /*
    |--------------------------------------------------------------------------
    | Limpiar carrito y tokens
    |--------------------------------------------------------------------------
    |
    | Se hace solamente después del commit. Si ocurre un error, el usuario
    | conserva el carrito para poder volver a intentar.
    |
    */

    unset($_SESSION['carrito']);
    unset($_SESSION['checkout_token']);
    unset($_SESSION['csrf_token']);

    /*
    |--------------------------------------------------------------------------
    | Pedido disponible para gracias.php
    |--------------------------------------------------------------------------
    */

    $_SESSION['ultimo_pedido_id'] = $pedidoId;

    redirect('/proyecto_cava_Noble/pages/gracias.php');

} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        '[Cava Noble] Error en checkout del usuario #' .
        $usuarioId .
        ': ' .
        $exception->getMessage()
    );

    redirect(
        '/proyecto_cava_Noble/pages/checkout.php?error=1'
    );
}