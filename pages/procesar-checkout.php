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
| Token único de checkout
|--------------------------------------------------------------------------
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
| Cloudflare Turnstile
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
| Datos recibidos
|--------------------------------------------------------------------------
*/

$usuarioId = (int)($_SESSION['usuario_id'] ?? 0);

$nombreCliente = trim(
    (string)($_POST['nombre_cliente'] ?? '')
);

$emailCliente = strtolower(
    trim((string)($_POST['email_cliente'] ?? ''))
);

$telefono = trim(
    (string)($_POST['telefono'] ?? '')
);

$direccion = trim(
    (string)($_POST['direccion'] ?? '')
);

$ciudad = trim(
    (string)($_POST['ciudad'] ?? '')
);

$provincia = trim(
    (string)($_POST['provincia'] ?? '')
);

$metodoPago = trim(
    (string)($_POST['metodo_pago'] ?? '')
);

$metodosPagoPermitidos = [
    'transferencia',
    'efectivo'
];

/*
|--------------------------------------------------------------------------
| Validación
|--------------------------------------------------------------------------
*/

if (
    $usuarioId <= 0 ||
    $nombreCliente === '' ||
    $emailCliente === '' ||
    $telefono === '' ||
    $direccion === '' ||
    $ciudad === '' ||
    $provincia === '' ||
    $metodoPago === '' ||
    !filter_var($emailCliente, FILTER_VALIDATE_EMAIL) ||
    !in_array($metodoPago, $metodosPagoPermitidos, true)
) {
    redirect('/proyecto_cava_Noble/pages/checkout.php?error=1');
}

/*
|--------------------------------------------------------------------------
| Límites de longitud compatibles con MySQL
|--------------------------------------------------------------------------
*/

if (
    mb_strlen($nombreCliente) > 100 ||
    mb_strlen($emailCliente) > 150 ||
    mb_strlen($telefono) > 50 ||
    mb_strlen($direccion) > 200 ||
    mb_strlen($ciudad) > 100 ||
    mb_strlen($provincia) > 100 ||
    mb_strlen($metodoPago) > 50
) {
    redirect('/proyecto_cava_Noble/pages/checkout.php?error=1');
}

try {
    /*
    |--------------------------------------------------------------------------
    | Iniciar transacción
    |--------------------------------------------------------------------------
    */

    $pdo->beginTransaction();

    $itemsFinales = [];
    $totalGeneral = 0.0;

    /*
    |--------------------------------------------------------------------------
    | Validar productos y bloquear stock
    |--------------------------------------------------------------------------
    */

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

    foreach ($_SESSION['carrito'] as $productoId => $itemCarrito) {
        $productoId = (int)$productoId;
        $cantidad = (int)($itemCarrito['cantidad'] ?? 0);

        if ($productoId <= 0 || $cantidad <= 0) {
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
            throw new RuntimeException(
                'El producto #' . $productoId . ' no existe.'
            );
        }

        $stockDisponible = (int)$producto['stock'];

        if ($stockDisponible < $cantidad) {
            throw new RuntimeException(
                'Stock insuficiente para el producto "' .
                $producto['nombre'] .
                '".'
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
            'precio_unitario' => $precioUnitario,
            'cantidad' => $cantidad,
            'subtotal' => $subtotal
        ];
    }

    if (empty($itemsFinales) || $totalGeneral <= 0) {
        throw new RuntimeException(
            'El carrito no contiene productos válidos.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Crear pedido
    |--------------------------------------------------------------------------
    */

    $sqlPedido = "
        INSERT INTO pedidos (
            usuario_id,
            nombre_cliente,
            email_cliente,
            telefono,
            direccion,
            ciudad,
            provincia,
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
            :ciudad,
            :provincia,
            :metodo_pago,
            :estado,
            :total
        )
    ";

    $stmtPedido = $pdo->prepare($sqlPedido);

    $stmtPedido->execute([
        ':usuario_id' => $usuarioId,
        ':nombre_cliente' => $nombreCliente,
        ':email_cliente' => $emailCliente,
        ':telefono' => $telefono,
        ':direccion' => $direccion,
        ':ciudad' => $ciudad,
        ':provincia' => $provincia,
        ':metodo_pago' => $metodoPago,
        ':estado' => 'procesando',
        ':total' => $totalGeneral
    ]);

    $pedidoId = (int)$pdo->lastInsertId();

    if ($pedidoId <= 0) {
        throw new RuntimeException(
            'No se pudo generar el identificador del pedido.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Preparar inserción de ítems
    |--------------------------------------------------------------------------
    */

    $sqlItem = "
        INSERT INTO pedido_items (
            pedido_id,
            producto_id,
            nombre_producto,
            precio_unitario,
            cantidad,
            subtotal
        )
        VALUES (
            :pedido_id,
            :producto_id,
            :nombre_producto,
            :precio_unitario,
            :cantidad,
            :subtotal
        )
    ";

    $stmtItem = $pdo->prepare($sqlItem);

    /*
    |--------------------------------------------------------------------------
    | Preparar actualización segura de stock
    |--------------------------------------------------------------------------
    |
    | Se usan dos placeholders diferentes porque PDO con consultas
    | preparadas nativas no debe reutilizar el mismo placeholder nominal.
    |
    */

    $sqlStock = "
        UPDATE productos
        SET stock = stock - :cantidad_restar
        WHERE id = :producto_id
        AND stock >= :cantidad_validar
    ";

    $stmtStock = $pdo->prepare($sqlStock);

    /*
    |--------------------------------------------------------------------------
    | Guardar ítems y descontar stock
    |--------------------------------------------------------------------------
    */

    foreach ($itemsFinales as $item) {
        $stmtItem->execute([
            ':pedido_id' => $pedidoId,
            ':producto_id' => $item['producto_id'],
            ':nombre_producto' => $item['nombre_producto'],
            ':precio_unitario' => $item['precio_unitario'],
            ':cantidad' => $item['cantidad'],
            ':subtotal' => $item['subtotal']
        ]);

        $stmtStock->bindValue(
            ':cantidad_restar',
            $item['cantidad'],
            PDO::PARAM_INT
        );

        $stmtStock->bindValue(
            ':producto_id',
            $item['producto_id'],
            PDO::PARAM_INT
        );

        $stmtStock->bindValue(
            ':cantidad_validar',
            $item['cantidad'],
            PDO::PARAM_INT
        );

        $stmtStock->execute();

        if ($stmtStock->rowCount() !== 1) {
            throw new RuntimeException(
                'No se pudo descontar el stock del producto #' .
                $item['producto_id'] .
                '.'
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
    | Limpiar sesión después del commit
    |--------------------------------------------------------------------------
    */

    unset($_SESSION['carrito']);
    unset($_SESSION['checkout_token']);
    unset($_SESSION['csrf_token']);
    unset($_SESSION['checkout_debug_error']);

    /*
    |--------------------------------------------------------------------------
    | Guardar último pedido para gracias.php
    |--------------------------------------------------------------------------
    */

    $_SESSION['ultimo_pedido_id'] = $pedidoId;

    redirect('/proyecto_cava_Noble/pages/gracias.php');

} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        '[Cava Noble] Error checkout usuario #' .
        $usuarioId .
        ': ' .
        $exception->getMessage()
    );

    if (
        defined('APP_DEBUG') &&
        APP_DEBUG === true
    ) {
        $_SESSION['checkout_debug_error'] =
            $exception->getMessage();
    }

    redirect('/proyecto_cava_Noble/pages/checkout.php?error=1');
}