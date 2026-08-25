<?php

declare(strict_types=1);

use App\Models\Product;

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/security.php';

requireAdmin();

require_once __DIR__ . '/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| POST: eliminar producto
|--------------------------------------------------------------------------
*/

if (isPostRequest()) {
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (
        !is_string($csrfToken) ||
        $csrfToken === '' ||
        !validateCsrfToken($csrfToken)
    ) {
        http_response_code(403);

        die('Token CSRF inválido.');
    }

    $adminId = (int)(
        $_SESSION['usuario_id']
        ?? 0
    );

    if ($adminId <= 0) {
        redirect(
            url('Login/login.php')
        );
    }

    $result = $productController->destroy(
        $_POST['id'] ?? null,
        $adminId
    );

    if ($result['success'] === true) {
        unset($_SESSION['admin_product_error']);

        redirect(
            url('admin/productos.php?deleted=1')
        );
    }

    $_SESSION['admin_product_error'] =
        $result['error']
        ?? 'No se pudo eliminar el producto.';

    redirect(
        url(
            'admin/eliminar-producto.php?id=' .
            (int)($_POST['id'] ?? 0) .
            '&error=1'
        )
    );
}

/*
|--------------------------------------------------------------------------
| GET: mostrar confirmación
|--------------------------------------------------------------------------
*/

$result = $productController->deleteConfirmation(
    $_GET['id'] ?? null
);

/** @var Product|null $producto */
$producto = $result['product'];
$error = $result['error'];

if (
    !$producto instanceof Product ||
    $error !== null
) {
    $_SESSION['admin_product_error'] =
        $error
        ?? 'No se pudo cargar el producto.';

    redirect(
        url('admin/productos.php?error=1')
    );
}

/*
|--------------------------------------------------------------------------
| Error de intento de eliminación
|--------------------------------------------------------------------------
*/

$operationError = null;

if (isset($_GET['error'])) {
    $sessionError =
        $_SESSION['admin_product_error']
        ?? 'No se pudo eliminar el producto.';

    $operationError = is_string($sessionError)
        ? $sessionError
        : 'No se pudo eliminar el producto.';

    unset($_SESSION['admin_product_error']);
}

/*
|--------------------------------------------------------------------------
| CSRF
|--------------------------------------------------------------------------
*/

$csrfToken = generateCsrfToken();

/*
|--------------------------------------------------------------------------
| Vista
|--------------------------------------------------------------------------
*/

include __DIR__ . '/../includes/header.php';

?>

<main class="section">
    <div class="container">

        <div class="section-header">
            <h2>Eliminar producto</h2>

            <p>
                Confirmá la eliminación del producto seleccionado.
            </p>
        </div>

        <div class="cart-box">

            <?php if ($operationError !== null): ?>

                <div
                    role="alert"
                    style="
                        margin-bottom: 20px;
                        padding: 15px;
                        border-radius: 8px;
                        background: #f8d7da;
                        color: #842029;
                    "
                >
                    <strong>
                        No se puede eliminar el producto.
                    </strong>

                    <p>
                        <?php echo e($operationError); ?>
                    </p>
                </div>

            <?php endif; ?>

            <div class="cart-item">

                <div>
                    <h3>
                        <?php echo e(
                            $producto->name()
                        ); ?>
                    </h3>

                    <p>
                        <?php echo e(
                            $producto->wineryName()
                                ?? 'Sin bodega'
                        ); ?>

                        ·

                        <?php echo e(
                            $producto->categoryName()
                                ?? 'Sin categoría'
                        ); ?>
                    </p>

                    <p>
                        <strong>Precio:</strong>

                        $<?php echo number_format(
                            $producto->price(),
                            0,
                            ',',
                            '.'
                        ); ?>
                    </p>

                    <p>
                        <strong>Stock:</strong>

                        <?php echo $producto->stock(); ?>
                    </p>
                </div>

                <img
                    src="<?php echo e(
                        $producto->image()
                    ); ?>"
                    alt="<?php echo e(
                        $producto->name()
                    ); ?>"
                    style="
                        width: 100px;
                        height: 120px;
                        object-fit: cover;
                    "
                >

            </div>

            <br>

            <p>
                Esta acción eliminará el producto del catálogo.
            </p>

            <p>
                Los productos que tengan pedidos asociados
                no pueden eliminarse para preservar el
                historial de ventas.
            </p>

            <br>

            <form
                action="<?php echo e(
                    url('admin/eliminar-producto.php')
                ); ?>"
                method="POST"
                class="auth-form"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo e($csrfToken); ?>"
                >

                <input
                    type="hidden"
                    name="id"
                    value="<?php echo $producto->id(); ?>"
                >

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Confirmar eliminación
                </button>

                <a
                    href="<?php echo e(
                        url('admin/productos.php')
                    ); ?>"
                    class="btn btn-secondary"
                >
                    Cancelar
                </a>

            </form>

        </div>
    </div>
</main>

<?php

include __DIR__ . '/../includes/footer.php';

?>