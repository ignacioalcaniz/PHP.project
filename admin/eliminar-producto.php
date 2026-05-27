<?php
require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../config/database.php';

requireAdmin();

$pdo = conectarDB();

if (isPostRequest()) {
    if (
        !isset($_POST['csrf_token']) ||
        !validateCsrfToken($_POST['csrf_token'])
    ) {
        die('Token CSRF inválido.');
    }

    $id = (int)($_POST['id'] ?? 0);

    if ($id <= 0) {
        redirect('/proyecto_cava_Noble/admin/productos.php');
    }

    $sqlCheck = "
        SELECT COUNT(*) AS total
        FROM pedido_items
        WHERE producto_id = :id
    ";

    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->bindParam(':id', $id, PDO::PARAM_INT);
    $stmtCheck->execute();

    $relacionado = $stmtCheck->fetch();

    if ((int)$relacionado['total'] > 0) {
        include '../includes/header.php';
        ?>

        <main class="section">
            <div class="container">
                <div class="form-container">
                    <h2>No se puede eliminar</h2>
                    <p>
                        Este producto ya tiene pedidos asociados.
                        Para mantener el historial de ventas, no se permite eliminarlo.
                    </p>

                    <br>

                    <a href="/proyecto_cava_Noble/admin/productos.php" class="btn btn-primary">
                        Volver a productos
                    </a>
                </div>
            </div>
        </main>

        <?php
        include '../includes/footer.php';
        exit;
    }

    $sqlDelete = "
        DELETE FROM productos
        WHERE id = :id
    ";

    $stmtDelete = $pdo->prepare($sqlDelete);
    $stmtDelete->bindParam(':id', $id, PDO::PARAM_INT);
    $stmtDelete->execute();

    redirect('/proyecto_cava_Noble/admin/productos.php');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    redirect('/proyecto_cava_Noble/admin/productos.php');
}

$sqlProducto = "
    SELECT
        p.*,
        c.nombre AS categoria,
        b.nombre AS bodega_nombre
    FROM productos p
    LEFT JOIN categorias c ON p.categoria_id = c.id
    LEFT JOIN bodegas b ON p.bodega_id = b.id
    WHERE p.id = :id
    LIMIT 1
";

$stmtProducto = $pdo->prepare($sqlProducto);
$stmtProducto->bindParam(':id', $id, PDO::PARAM_INT);
$stmtProducto->execute();

$producto = $stmtProducto->fetch();

if (!$producto) {
    redirect('/proyecto_cava_Noble/admin/productos.php');
}

$csrfToken = generateCsrfToken();

include '../includes/header.php';
?>

<main class="section">
    <div class="container">

        <div class="section-header">
            <h2>Eliminar producto</h2>
            <p>Confirmá la eliminación del producto seleccionado.</p>
        </div>

        <div class="cart-box">
            <div class="cart-item">
                <div>
                    <h3><?php echo e($producto['nombre']); ?></h3>
                    <p>
                        <?php echo e($producto['bodega_nombre'] ?? 'Sin bodega'); ?>
                        ·
                        <?php echo e($producto['categoria'] ?? 'Sin categoría'); ?>
                    </p>
                    <p><strong>Precio:</strong> $<?php echo number_format($producto['precio'], 0, ',', '.'); ?></p>
                    <p><strong>Stock:</strong> <?php echo (int)$producto['stock']; ?></p>
                </div>

                <img
                    src="<?php echo e($producto['imagen']); ?>"
                    alt="<?php echo e($producto['nombre']); ?>"
                    style="width:100px; height:120px; object-fit:cover;"
                >
            </div>

            <br>

            <p>
                Esta acción eliminará el producto del catálogo.
                No se recomienda eliminar productos que ya tengan ventas asociadas.
            </p>

            <br>

            <form action="/proyecto_cava_Noble/admin/eliminar-producto.php" method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
                <input type="hidden" name="id" value="<?php echo (int)$producto['id']; ?>">

                <button type="submit" class="btn btn-primary">
                    Confirmar eliminación
                </button>

                <a href="/proyecto_cava_Noble/admin/productos.php" class="btn btn-secondary">
                    Cancelar
                </a>
            </form>
        </div>

    </div>
</main>

<?php include '../includes/footer.php'; ?>