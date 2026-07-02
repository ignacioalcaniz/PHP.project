<?php
require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../includes/captcha.php';
require_once '../config/database.php';

requireLogin();

if (!isPostRequest()) {
    redirect('/proyecto_cava_Noble/carrito.php');
}

if (
    !isset($_POST['csrf_token']) ||
    !validateCsrfToken($_POST['csrf_token'])
) {
    die('Token CSRF inválido.');
}

if (
    empty($_POST['checkout_token']) ||
    empty($_SESSION['checkout_token']) ||
    !hash_equals($_SESSION['checkout_token'], $_POST['checkout_token'])
) {
    redirect('/proyecto_cava_Noble/carrito.php');
}

if (!verifyTurnstileToken()) {
    redirect('/proyecto_cava_Noble/pages/checkout.php?captcha=1');
}

if (empty($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    redirect('/proyecto_cava_Noble/carrito.php');
}

$pdo = conectarDB();

$usuarioId = (int)$_SESSION['usuario_id'];
$nombreCliente = trim($_POST['nombre_cliente'] ?? '');
$emailCliente = trim($_POST['email_cliente'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$metodoPago = trim($_POST['metodo_pago'] ?? '');

if (
    $nombreCliente === '' ||
    $emailCliente === '' ||
    $telefono === '' ||
    $direccion === '' ||
    $metodoPago === '' ||
    !filter_var($emailCliente, FILTER_VALIDATE_EMAIL)
) {
    redirect('/proyecto_cava_Noble/pages/checkout.php?error=1');
}

if (!in_array($metodoPago, ['transferencia', 'efectivo'], true)) {
    redirect('/proyecto_cava_Noble/pages/checkout.php?error=1');
}

try {
    $pdo->beginTransaction();

    $itemsFinales = [];
    $totalGeneral = 0;

    foreach ($_SESSION['carrito'] as $productoId => $item) {
        $productoId = (int)$productoId;
        $cantidad = (int)($item['cantidad'] ?? 0);

        if ($productoId <= 0 || $cantidad <= 0) {
            continue;
        }

        $sqlProducto = "
            SELECT id, nombre, precio, stock
            FROM productos
            WHERE id = :id
            LIMIT 1
            FOR UPDATE
        ";

        $stmtProducto = $pdo->prepare($sqlProducto);
        $stmtProducto->bindParam(':id', $productoId, PDO::PARAM_INT);
        $stmtProducto->execute();

        $producto = $stmtProducto->fetch();

        if (!$producto || (int)$producto['stock'] < $cantidad) {
            $pdo->rollBack();
            redirect('/proyecto_cava_Noble/pages/checkout.php?stock=1');
        }

        $subtotal = (float)$producto['precio'] * $cantidad;
        $totalGeneral += $subtotal;

        $itemsFinales[] = [
            'id' => (int)$producto['id'],
            'nombre' => $producto['nombre'],
            'precio' => (float)$producto['precio'],
            'cantidad' => $cantidad,
            'subtotal' => $subtotal
        ];
    }

    if (empty($itemsFinales)) {
        $pdo->rollBack();
        redirect('/proyecto_cava_Noble/carrito.php');
    }

    $estado = 'pendiente';

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
    $stmtPedido->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
    $stmtPedido->bindParam(':nombre_cliente', $nombreCliente);
    $stmtPedido->bindParam(':email_cliente', $emailCliente);
    $stmtPedido->bindParam(':telefono', $telefono);
    $stmtPedido->bindParam(':direccion', $direccion);
    $stmtPedido->bindParam(':metodo_pago', $metodoPago);
    $stmtPedido->bindParam(':estado', $estado);
    $stmtPedido->bindParam(':total', $totalGeneral);
    $stmtPedido->execute();

    $pedidoId = (int)$pdo->lastInsertId();

    foreach ($itemsFinales as $item) {
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
        $stmtItem->bindParam(':pedido_id', $pedidoId, PDO::PARAM_INT);
        $stmtItem->bindParam(':producto_id', $item['id'], PDO::PARAM_INT);
        $stmtItem->bindParam(':nombre_producto', $item['nombre']);
        $stmtItem->bindParam(':precio', $item['precio']);
        $stmtItem->bindParam(':cantidad', $item['cantidad'], PDO::PARAM_INT);
        $stmtItem->bindParam(':subtotal', $item['subtotal']);
        $stmtItem->execute();

        $sqlStock = "
            UPDATE productos
            SET stock = stock - :cantidad
            WHERE id = :id
        ";

        $stmtStock = $pdo->prepare($sqlStock);
        $stmtStock->bindParam(':cantidad', $item['cantidad'], PDO::PARAM_INT);
        $stmtStock->bindParam(':id', $item['id'], PDO::PARAM_INT);
        $stmtStock->execute();
    }

    $pdo->commit();

    unset($_SESSION['carrito']);
    unset($_SESSION['checkout_token']);
    unset($_SESSION['csrf_token']);

    $_SESSION['ultimo_pedido_id'] = $pedidoId;

redirect('/proyecto_cava_Noble/pages/gracias.php');

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    redirect('/proyecto_cava_Noble/pages/checkout.php?error=1');
}