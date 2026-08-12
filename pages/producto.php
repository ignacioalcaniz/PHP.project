<?php

declare(strict_types=1);

use App\Models\Product;

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';

startSecureSession();

/*
|--------------------------------------------------------------------------
| Obtener producto mediante la nueva arquitectura
|--------------------------------------------------------------------------
*/

$viewData = $productController->show(
    $_GET['id'] ?? null
);

/** @var Product|null $producto */
$producto = $viewData['product'];

$descuento = (float)$viewData['discount'];
$precioFinal = (float)$viewData['finalPrice'];

if (
    !$producto instanceof Product ||
    $viewData['error'] !== null
) {
    redirect(
        url('pages/catalogo.php')
    );
}

/*
|--------------------------------------------------------------------------
| CSRF
|--------------------------------------------------------------------------
*/

$csrfToken = generateCsrfToken();

include __DIR__ . '/../includes/header.php';

?>

<main class="section">
    <div class="container">

        <div class="product-detail">

            <div class="product-detail-image">

                <img
                    src="<?php echo e($producto->image()); ?>"
                    alt="<?php echo e($producto->name()); ?>"
                >

            </div>

            <div class="product-detail-info">

                <span class="product-category">

                    <?php echo e(
                        $producto->categoryName()
                            ?? 'Sin categoría'
                    ); ?>

                </span>

                <h1>
                    <?php echo e($producto->name()); ?>
                </h1>

                <?php if ($descuento > 0): ?>

                    <p class="detail-price">
                        Precio original:
                        $<?php echo number_format(
                            $producto->price(),
                            0,
                            ',',
                            '.'
                        ); ?>
                    </p>

                    <p class="detail-price">
                        Precio final:
                        $<?php echo number_format(
                            $precioFinal,
                            0,
                            ',',
                            '.'
                        ); ?>
                    </p>

                <?php else: ?>

                    <p class="detail-price">
                        $<?php echo number_format(
                            $producto->price(),
                            0,
                            ',',
                            '.'
                        ); ?>
                    </p>

                <?php endif; ?>

                <p>
                    <?php echo e(
                        $producto->description()
                    ); ?>
                </p>

                <ul class="product-data">

                    <li>
                        <strong>Bodega:</strong>

                        <?php echo e(
                            $producto->wineryName()
                                ?? 'Sin bodega'
                        ); ?>
                    </li>

                    <li>
                        <strong>País:</strong>

                        <?php echo e(
                            $producto->country()
                                ?? 'No informado'
                        ); ?>
                    </li>

                    <li>
                        <strong>Región:</strong>

                        <?php echo e(
                            $producto->region()
                                ?? 'No informada'
                        ); ?>
                    </li>

                    <li>
                        <strong>Cepa:</strong>

                        <?php echo e(
                            $producto->grape()
                                ?? 'No informada'
                        ); ?>
                    </li>

                    <li>
                        <strong>Añada:</strong>

                        <?php echo $producto->vintage() !== null
                            ? (int)$producto->vintage()
                            : 'No informada'; ?>
                    </li>

                    <li>
                        <strong>Categoría:</strong>

                        <?php echo e(
                            $producto->categoryName()
                                ?? 'Sin categoría'
                        ); ?>
                    </li>

                    <li>
                        <strong>Stock:</strong>

                        <?php echo $producto->stock(); ?>
                        unidades
                    </li>

                </ul>

                <?php if (
                    $producto->wineryDescription() !== null
                ): ?>

                    <p>
                        <strong>Sobre la bodega:</strong>

                        <?php echo e(
                            $producto->wineryDescription()
                        ); ?>
                    </p>

                <?php endif; ?>

                <?php if ($producto->isAvailable()): ?>

                    <form
                        action="<?php echo e(
                            url('carrito/agregar.php')
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
                            name="producto_id"
                            value="<?php echo $producto->id(); ?>"
                        >

                        <div class="form-group">

                            <label for="cantidad">
                                Cantidad
                            </label>

                            <input
                                type="number"
                                id="cantidad"
                                name="cantidad"
                                min="1"
                                max="<?php echo $producto->stock(); ?>"
                                value="1"
                                required
                            >

                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Agregar al carrito
                        </button>

                    </form>

                <?php else: ?>

                    <p>
                        <strong>
                            Producto sin stock disponible.
                        </strong>
                    </p>

                    <br>

                    <a
                        href="<?php echo e(
                            url('pages/catalogo.php')
                        ); ?>"
                        class="btn btn-secondary"
                    >
                        Volver al catálogo
                    </a>

                <?php endif; ?>

            </div>
        </div>

    </div>
</main>

<?php

include __DIR__ . '/../includes/footer.php';

?>