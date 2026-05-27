<?php
require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isPostRequest()) {
    redirect('/proyecto_cava_Noble/carrito.php');
}

if (
    !isset($_POST['csrf_token']) ||
    !validateCsrfToken($_POST['csrf_token'])
) {
    die('Token CSRF inválido.');
}

if (empty($_SESSION['carrito'])) {
    redirect('/proyecto_cava_Noble/carrito.php');
}

$pdo = conectarDB();

$nombreCliente = trim($_POST['nombre_cliente'] ?? '');
$emailCliente = trim($_POST['email_cliente'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$ciudad = trim($_POST['ciudad'] ?? '');
$provincia = trim($_POST['provincia'] ?? '');
$metodoPago = trim($_POST['metodo_pago'] ?? '');

$errores = [];

if ($nombreCliente === '') $errores[] = 'El nombre es obligatorio.';
if ($emailCliente === '') $errores[] = 'El email es obligatorio.';
if (!filter_var($emailCliente, FILTER_VALIDATE_EMAIL)) $errores[] = 'El email no es válido.';
if ($telefono === '') $errores[] = 'El teléfono es obligatorio.';
if ($direccion === '') $errores[] = 'La dirección es obligatoria.';
if ($ciudad === '') $errores[] = 'La ciudad es obligatoria.';
if ($provincia === '') $errores[] = 'La provincia es obligatoria.';
if ($metodoPago === '') $errores[] = 'El método de pago es obligatorio.';

if (!empty($errores)) {
    include '../includes/header.php';
    ?>

    <main class="section">
        <div class="container">
            <div class="form-container">
                <h2>Error al procesar el pedido</h2>

                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; ?>
                </ul>

                <br>

                <a href="/proyecto_cava_Noble/pages/checkout.php" class="btn btn-primary">
                    Volver al checkout
                </a>
            </div>
        </div>
    </main>

    <?php
    include '../includes/footer.php';
    exit;
}

try {
    $pdo->beginTransaction();

    $itemsPedido = [];
    $totalGeneral = 0;

    foreach ($_SESSION['carrito'] as $productoId => $item) {
        $sqlProducto = "
            SELECT id, nombre, precio, stock
            FROM productos
            WHERE id = :id
            LIMIT 1
        ";

        $stmtProducto = $pdo->prepare($sqlProducto);
        $stmtProducto->bindParam(':id', $productoId, PDO::PARAM_INT);
        $stmtProducto->execute();

        $producto = $stmtProducto->fetch();

        if (!$producto) {
            continue;
        }

        $cantidad = min((int)$item['cantidad'], (int)$producto['stock']);

        if ($cantidad <= 0) {
            continue;
        }

        $subtotal = $producto['precio'] * $cantidad;
        $totalGeneral += $subtotal;

        $itemsPedido[] = [
            'producto_id' => $producto['id'],
            'nombre_producto' => $producto['nombre'],
            'precio_unitario' => $producto['precio'],
            'cantidad' => $cantidad,
            'subtotal' => $subtotal
        ];
    }

    if (empty($itemsPedido)) {
        throw new Exception('No hay productos disponibles para procesar el pedido.');
    }

    $usuarioId = $_SESSION['usuario_id'] ?? null;

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
            total,
            fecha_pedido
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
            'pendiente',
            :total,
            NOW()
        )
    ";

    $stmtPedido = $pdo->prepare($sqlPedido);

    if ($usuarioId) {
        $stmtPedido->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
    } else {
        $stmtPedido->bindValue(':usuario_id', null, PDO::PARAM_NULL);
    }

    $stmtPedido->bindParam(':nombre_cliente', $nombreCliente);
    $stmtPedido->bindParam(':email_cliente', $emailCliente);
    $stmtPedido->bindParam(':telefono', $telefono);
    $stmtPedido->bindParam(':direccion', $direccion);
    $stmtPedido->bindParam(':ciudad', $ciudad);
    $stmtPedido->bindParam(':provincia', $provincia);
    $stmtPedido->bindParam(':metodo_pago', $metodoPago);
    $stmtPedido->bindParam(':total', $totalGeneral);

    $stmtPedido->execute();

    $pedidoId = $pdo->lastInsertId();

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

    $sqlStock = "
        UPDATE productos
        SET stock = stock - :cantidad
        WHERE id = :producto_id
        AND stock >= :cantidad
    ";

    $stmtStock = $pdo->prepare($sqlStock);

    foreach ($itemsPedido as $item) {
        $stmtItem->bindParam(':pedido_id', $pedidoId, PDO::PARAM_INT);
        $stmtItem->bindParam(':producto_id', $item['producto_id'], PDO::PARAM_INT);
        $stmtItem->bindParam(':nombre_producto', $item['nombre_producto']);
        $stmtItem->bindParam(':precio_unitario', $item['precio_unitario']);
        $stmtItem->bindParam(':cantidad', $item['cantidad'], PDO::PARAM_INT);
        $stmtItem->bindParam(':subtotal', $item['subtotal']);
        $stmtItem->execute();

        $stmtStock->bindParam(':cantidad', $item['cantidad'], PDO::PARAM_INT);
        $stmtStock->bindParam(':producto_id', $item['producto_id'], PDO::PARAM_INT);
        $stmtStock->execute();
    }

    $pdo->commit();

    unset($_SESSION['carrito']);
    unset($_SESSION['csrf_token']);

    $_SESSION['ultimo_pedido_id'] = $pedidoId;

    redirect('/proyecto_cava_Noble/pages/gracias.php');

} catch (Exception $e) {
    $pdo->rollBack();

    include '../includes/header.php';
    ?>

    <main class="section">
        <div class="container">
            <div class="form-container">
                <h2>No se pudo completar el pedido</h2>
                <p><?php echo e($e->getMessage()); ?></p>

                <br>

                <a href="/proyecto_cava_Noble/carrito.php" class="btn btn-primary">
                    Volver al carrito
                </a>
            </div>
        </div>
    </main>

    <?php
    include '../includes/footer.php';
    exit;
}