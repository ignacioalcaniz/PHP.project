<?php

require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../includes/admin-log.php';
require_once '../config/database.php';

requireAdmin();

if (!isPostRequest()) {
    redirect('/proyecto_cava_Noble/admin/pedidos.php');
}

if (
    !isset($_POST['csrf_token']) ||
    !validateCsrfToken($_POST['csrf_token'])
) {
    http_response_code(403);
    die('Token CSRF inválido.');
}

$pdo = conectarDB();

$pedidoId = (int)($_POST['pedido_id'] ?? 0);
$nuevoEstado = trim($_POST['estado'] ?? '');

$estadosPermitidos = [
    'pendiente',
    'procesando',
    'confirmado',
    'preparado',
    'despachado',
    'entregado',
    'cancelado'
];

if (
    $pedidoId <= 0 ||
    !in_array($nuevoEstado, $estadosPermitidos, true)
) {
    redirect('/proyecto_cava_Noble/admin/pedidos.php');
}

try {
    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | Bloqueo del pedido
    |--------------------------------------------------------------------------
    */

    $sqlPedido = "
        SELECT
            id,
            estado
        FROM pedidos
        WHERE id = :id
        LIMIT 1
        FOR UPDATE
    ";

    $stmtPedido = $pdo->prepare($sqlPedido);

    $stmtPedido->bindParam(
        ':id',
        $pedidoId,
        PDO::PARAM_INT
    );

    $stmtPedido->execute();

    $pedido = $stmtPedido->fetch();

    if (!$pedido) {
        $pdo->rollBack();

        redirect('/proyecto_cava_Noble/admin/pedidos.php');
    }

    $estadoAnterior = $pedido['estado'];

    if ($estadoAnterior === $nuevoEstado) {
        $pdo->commit();

        redirect(
            '/proyecto_cava_Noble/admin/detalle-pedido.php?id=' .
            $pedidoId .
            '&actualizado=1'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Items del pedido
    |--------------------------------------------------------------------------
    */

    $sqlItems = "
        SELECT
            producto_id,
            cantidad
        FROM pedido_items
        WHERE pedido_id = :pedido_id
    ";

    $stmtItems = $pdo->prepare($sqlItems);

    $stmtItems->bindParam(
        ':pedido_id',
        $pedidoId,
        PDO::PARAM_INT
    );

    $stmtItems->execute();

    $items = $stmtItems->fetchAll();

    /*
    |--------------------------------------------------------------------------
    | Cancelar pedido: restaurar stock
    |--------------------------------------------------------------------------
    |
    | El checkout ya descontó el stock al confirmar la compra.
    | Si el pedido se cancela por primera vez, las unidades regresan al catálogo.
    |
    */

    if (
        $nuevoEstado === 'cancelado' &&
        $estadoAnterior !== 'cancelado'
    ) {
        foreach ($items as $item) {
            $productoId = (int)$item['producto_id'];
            $cantidad = (int)$item['cantidad'];

            if ($productoId <= 0 || $cantidad <= 0) {
                continue;
            }

            $sqlRestaurarStock = "
                UPDATE productos
                SET stock = stock + :cantidad
                WHERE id = :producto_id
            ";

            $stmtRestaurarStock = $pdo->prepare(
                $sqlRestaurarStock
            );

            $stmtRestaurarStock->bindParam(
                ':cantidad',
                $cantidad,
                PDO::PARAM_INT
            );

            $stmtRestaurarStock->bindParam(
                ':producto_id',
                $productoId,
                PDO::PARAM_INT
            );

            $stmtRestaurarStock->execute();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Reactivar pedido cancelado: volver a descontar stock
    |--------------------------------------------------------------------------
    */

    if (
        $estadoAnterior === 'cancelado' &&
        $nuevoEstado !== 'cancelado'
    ) {
        foreach ($items as $item) {
            $productoId = (int)$item['producto_id'];
            $cantidad = (int)$item['cantidad'];

            if ($productoId <= 0 || $cantidad <= 0) {
                continue;
            }

            $sqlProducto = "
                SELECT stock
                FROM productos
                WHERE id = :producto_id
                LIMIT 1
                FOR UPDATE
            ";

            $stmtProducto = $pdo->prepare($sqlProducto);

            $stmtProducto->bindParam(
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
                    '/proyecto_cava_Noble/admin/detalle-pedido.php?id=' .
                    $pedidoId .
                    '&stock_error=1'
                );
            }
        }

        foreach ($items as $item) {
            $productoId = (int)$item['producto_id'];
            $cantidad = (int)$item['cantidad'];

            if ($productoId <= 0 || $cantidad <= 0) {
                continue;
            }

            $sqlDescontarStock = "
                UPDATE productos
                SET stock = stock - :cantidad
                WHERE id = :producto_id
                AND stock >= :cantidad
            ";

            $stmtDescontarStock = $pdo->prepare(
                $sqlDescontarStock
            );

            $stmtDescontarStock->bindParam(
                ':cantidad',
                $cantidad,
                PDO::PARAM_INT
            );

            $stmtDescontarStock->bindParam(
                ':producto_id',
                $productoId,
                PDO::PARAM_INT
            );

            $stmtDescontarStock->execute();

            if ($stmtDescontarStock->rowCount() !== 1) {
                $pdo->rollBack();

                redirect(
                    '/proyecto_cava_Noble/admin/detalle-pedido.php?id=' .
                    $pedidoId .
                    '&stock_error=1'
                );
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Actualizar estado
    |--------------------------------------------------------------------------
    */

    $sqlActualizar = "
        UPDATE pedidos
        SET estado = :estado
        WHERE id = :id
    ";

    $stmtActualizar = $pdo->prepare($sqlActualizar);

    $stmtActualizar->bindParam(
        ':estado',
        $nuevoEstado
    );

    $stmtActualizar->bindParam(
        ':id',
        $pedidoId,
        PDO::PARAM_INT
    );

    $stmtActualizar->execute();

    /*
    |--------------------------------------------------------------------------
    | Auditoría
    |--------------------------------------------------------------------------
    */

    createAdminLog(
        (int)$_SESSION['usuario_id'],
        'CAMBIAR_ESTADO',
        'PEDIDO',
        $pedidoId,
        'Estado del pedido modificado de "' .
        $estadoAnterior .
        '" a "' .
        $nuevoEstado .
        '"'
    );

    $pdo->commit();

    redirect(
        '/proyecto_cava_Noble/admin/detalle-pedido.php?id=' .
        $pedidoId .
        '&actualizado=1'
    );

} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'Error al actualizar pedido #' .
        $pedidoId .
        ': ' .
        $exception->getMessage()
    );

    redirect(
        '/proyecto_cava_Noble/admin/detalle-pedido.php?id=' .
        $pedidoId .
        '&error=1'
    );
}