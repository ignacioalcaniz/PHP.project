<?php

require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../includes/admin-log.php';
require_once '../config/database.php';

requireAdmin();

/*
|--------------------------------------------------------------------------
| Solo solicitudes POST
|--------------------------------------------------------------------------
*/

if (!isPostRequest()) {
    redirect('/proyecto_cava_Noble/admin/pedidos.php?vista=activos');
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
| Datos recibidos
|--------------------------------------------------------------------------
*/

$pedidoId = (int)($_POST['pedido_id'] ?? 0);
$nuevoEstado = trim((string)($_POST['estado'] ?? ''));
$accion = trim((string)($_POST['accion'] ?? 'actualizar'));

$estadosPermitidos = [
    'pendiente',
    'procesando',
    'confirmado',
    'preparado',
    'despachado',
    'entregado',
    'cancelado'
];

$accionesPermitidas = [
    'actualizar',
    'finalizar'
];

if (
    $pedidoId <= 0 ||
    !in_array($nuevoEstado, $estadosPermitidos, true) ||
    !in_array($accion, $accionesPermitidas, true)
) {
    redirect('/proyecto_cava_Noble/admin/pedidos.php?vista=activos&error=1');
}

/*
|--------------------------------------------------------------------------
| Validación de la finalización rápida
|--------------------------------------------------------------------------
|
| Si la acción llegó desde el botón "Finalizar pedido", el único estado
| permitido es "entregado". Esto impide manipular el campo oculto.
|
*/

if (
    $accion === 'finalizar' &&
    $nuevoEstado !== 'entregado'
) {
    redirect('/proyecto_cava_Noble/admin/pedidos.php?vista=activos&error=1');
}

$pdo = conectarDB();

try {
    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | Obtener y bloquear el pedido
    |--------------------------------------------------------------------------
    */

    $sqlPedido = "
        SELECT
            id,
            estado,
            nombre_cliente
        FROM pedidos
        WHERE id = :id
        LIMIT 1
        FOR UPDATE
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
        $pdo->rollBack();

        redirect(
            '/proyecto_cava_Noble/admin/pedidos.php?vista=activos&error=1'
        );
    }

    $estadoAnterior = (string)$pedido['estado'];
    $nombreCliente = (string)($pedido['nombre_cliente'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | Evitar operaciones innecesarias
    |--------------------------------------------------------------------------
    */

    if ($estadoAnterior === $nuevoEstado) {
        $pdo->commit();

        if ($accion === 'finalizar') {
            redirect(
                '/proyecto_cava_Noble/admin/pedidos.php' .
                '?vista=finalizados&finalizado=1'
            );
        }

        redirect(
            '/proyecto_cava_Noble/admin/detalle-pedido.php' .
            '?id=' . $pedidoId .
            '&actualizado=1'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Reglas de negocio
    |--------------------------------------------------------------------------
    */

    if (
        $estadoAnterior === 'entregado' &&
        $accion === 'finalizar'
    ) {
        $pdo->commit();

        redirect(
            '/proyecto_cava_Noble/admin/pedidos.php' .
            '?vista=finalizados&finalizado=1'
        );
    }

    if (
        $estadoAnterior === 'cancelado' &&
        $accion === 'finalizar'
    ) {
        $pdo->rollBack();

        redirect(
            '/proyecto_cava_Noble/admin/pedidos.php' .
            '?vista=cancelados&error=1'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Obtener los productos del pedido
    |--------------------------------------------------------------------------
    */

    $sqlItems = "
        SELECT
            producto_id,
            cantidad
        FROM pedido_items
        WHERE pedido_id = :pedido_id
        ORDER BY id ASC
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
    | Cancelación: restaurar stock
    |--------------------------------------------------------------------------
    |
    | El checkout descuenta el stock cuando crea el pedido.
    | Si el pedido se cancela por primera vez, se reintegran las unidades.
    |
    */

    if (
        $nuevoEstado === 'cancelado' &&
        $estadoAnterior !== 'cancelado'
    ) {
        $sqlRestaurarStock = "
            UPDATE productos
            SET stock = stock + :cantidad
            WHERE id = :producto_id
        ";

        $stmtRestaurarStock = $pdo->prepare($sqlRestaurarStock);

        foreach ($items as $item) {
            $productoId = (int)($item['producto_id'] ?? 0);
            $cantidad = (int)($item['cantidad'] ?? 0);

            if ($productoId <= 0 || $cantidad <= 0) {
                continue;
            }

            $stmtRestaurarStock->bindValue(
                ':cantidad',
                $cantidad,
                PDO::PARAM_INT
            );

            $stmtRestaurarStock->bindValue(
                ':producto_id',
                $productoId,
                PDO::PARAM_INT
            );

            $stmtRestaurarStock->execute();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Reactivación: comprobar y volver a descontar stock
    |--------------------------------------------------------------------------
    |
    | Si un pedido cancelado vuelve a un estado activo, primero comprobamos
    | que todas las unidades continúen disponibles.
    |
    */

    if (
        $estadoAnterior === 'cancelado' &&
        $nuevoEstado !== 'cancelado'
    ) {
        foreach ($items as $item) {
            $productoId = (int)($item['producto_id'] ?? 0);
            $cantidad = (int)($item['cantidad'] ?? 0);

            if ($productoId <= 0 || $cantidad <= 0) {
                continue;
            }

            $sqlProducto = "
                SELECT
                    id,
                    stock
                FROM productos
                WHERE id = :producto_id
                LIMIT 1
                FOR UPDATE
            ";

            $stmtProducto = $pdo->prepare($sqlProducto);

            $stmtProducto->bindValue(
                ':producto_id',
                $productoId,
                PDO::PARAM_INT
            );

            $stmtProducto->execute();

            $producto = $stmtProducto->fetch();

            if (
                !$producto ||
                (int)$producto['stock'] < $cantidad
            ) {
                $pdo->rollBack();

                redirect(
                    '/proyecto_cava_Noble/admin/detalle-pedido.php' .
                    '?id=' . $pedidoId .
                    '&stock_error=1'
                );
            }
        }

        $sqlDescontarStock = "
            UPDATE productos
            SET stock = stock - :cantidad
            WHERE id = :producto_id
            AND stock >= :cantidad
        ";

        $stmtDescontarStock = $pdo->prepare($sqlDescontarStock);

        foreach ($items as $item) {
            $productoId = (int)($item['producto_id'] ?? 0);
            $cantidad = (int)($item['cantidad'] ?? 0);

            if ($productoId <= 0 || $cantidad <= 0) {
                continue;
            }

            $stmtDescontarStock->bindValue(
                ':cantidad',
                $cantidad,
                PDO::PARAM_INT
            );

            $stmtDescontarStock->bindValue(
                ':producto_id',
                $productoId,
                PDO::PARAM_INT
            );

            $stmtDescontarStock->execute();

            if ($stmtDescontarStock->rowCount() !== 1) {
                throw new RuntimeException(
                    'No se pudo descontar nuevamente el stock del producto #' .
                    $productoId
                );
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Actualizar el estado
    |--------------------------------------------------------------------------
    */

    $sqlActualizar = "
        UPDATE pedidos
        SET estado = :estado
        WHERE id = :id
    ";

    $stmtActualizar = $pdo->prepare($sqlActualizar);

    $stmtActualizar->bindValue(
        ':estado',
        $nuevoEstado,
        PDO::PARAM_STR
    );

    $stmtActualizar->bindValue(
        ':id',
        $pedidoId,
        PDO::PARAM_INT
    );

    $stmtActualizar->execute();

    if ($stmtActualizar->rowCount() !== 1) {
        throw new RuntimeException(
            'No se pudo actualizar el estado del pedido.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Auditoría administrativa
    |--------------------------------------------------------------------------
    */

    if ($accion === 'finalizar') {
        $accionLog = 'FINALIZAR_PEDIDO';

        $descripcionLog =
            'Pedido #' . $pedidoId .
            ' del cliente "' . $nombreCliente .
            '" finalizado. Estado modificado de "' .
            $estadoAnterior .
            '" a "entregado".';
    } else {
        $accionLog = 'CAMBIAR_ESTADO';

        $descripcionLog =
            'Estado del pedido #' . $pedidoId .
            ' modificado de "' .
            $estadoAnterior .
            '" a "' .
            $nuevoEstado .
            '".';
    }

    createAdminLog(
        (int)$_SESSION['usuario_id'],
        $accionLog,
        'PEDIDO',
        $pedidoId,
        $descripcionLog
    );

    /*
    |--------------------------------------------------------------------------
    | Confirmar transacción
    |--------------------------------------------------------------------------
    */

    $pdo->commit();

    /*
    |--------------------------------------------------------------------------
    | Redirección según la acción
    |--------------------------------------------------------------------------
    */

    if ($accion === 'finalizar') {
        redirect(
            '/proyecto_cava_Noble/admin/pedidos.php' .
            '?vista=finalizados&finalizado=1'
        );
    }

    redirect(
        '/proyecto_cava_Noble/admin/detalle-pedido.php' .
        '?id=' . $pedidoId .
        '&actualizado=1'
    );

} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        '[Cava Noble] Error al actualizar pedido #' .
        $pedidoId .
        ': ' .
        $exception->getMessage()
    );

    if ($accion === 'finalizar') {
        redirect(
            '/proyecto_cava_Noble/admin/pedidos.php' .
            '?vista=activos&error=1'
        );
    }

    redirect(
        '/proyecto_cava_Noble/admin/detalle-pedido.php' .
        '?id=' . $pedidoId .
        '&error=1'
    );
}