<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

require_once __DIR__ . '/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Productos - Administración
|--------------------------------------------------------------------------
|
| Este archivo funciona únicamente como punto de entrada HTTP.
| La obtención y procesamiento de productos pertenece al módulo
| de dominio y no a esta vista.
|
*/

$result = $productController->adminIndex();

$productos = $result['products'];
$error = $result['error'];

include __DIR__ . '/../includes/header.php';
?>

<main class="section">
    <div class="container">

        <div class="section-header">
            <h2>Administrar productos</h2>
            <p>Gestión completa del catálogo de vinos.</p>
        </div>

        <div style="margin-bottom: 30px;">
            <a
                href="/proyecto_cava_Noble/admin/crear-producto.php"
                class="btn btn-primary"
            >
                Agregar producto
            </a>
        </div>

        <?php if ($error !== null): ?>
            <div class="form-container">
                <p>
                    <?php echo e($error); ?>
                </p>
            </div>

        <?php elseif ($productos === []): ?>
            <div class="form-container">
                <p>No hay productos cargados.</p>
            </div>

        <?php else: ?>

            <div
                class="cart-box"
                style="max-width: 100%;"
            >
                <?php foreach ($productos as $producto): ?>

                    <div class="cart-item">

                        <div
                            style="
                                display: flex;
                                gap: 20px;
                                align-items: center;
                            "
                        >
                            <img
                                src="<?php echo e($producto->image()); ?>"
                                alt="<?php echo e($producto->name()); ?>"
                                style="
                                    width: 100px;
                                    height: 120px;
                                    object-fit: cover;
                                "
                            >

                            <div>
                                <h3>
                                    <?php echo e($producto->name()); ?>
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

                                <?php if ($producto->isFeatured()): ?>
                                    <span class="admin-badge">
                                        Destacado
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div
                            style="
                                display: flex;
                                gap: 10px;
                                flex-wrap: wrap;
                            "
                        >
                            <a
                                href="/proyecto_cava_Noble/admin/editar-producto.php?id=<?php echo $producto->id(); ?>"
                                class="btn btn-secondary"
                            >
                                Editar
                            </a>

                            <a
                                href="/proyecto_cava_Noble/admin/eliminar-producto.php?id=<?php echo $producto->id(); ?>"
                                class="btn btn-primary"
                            >
                                Eliminar
                            </a>
                        </div>

                    </div>

                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>