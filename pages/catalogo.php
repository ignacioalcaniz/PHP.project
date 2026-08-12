<?php

declare(strict_types=1);

use App\DTOs\ProductFiltersDTO;
use App\Models\Product;

require_once __DIR__ . '/../bootstrap/app.php';

if (!defined('MONEDA')) {
    define('MONEDA', '$');
}

/*
|--------------------------------------------------------------------------
| Controlador
|--------------------------------------------------------------------------
*/

$viewData = $productController->catalog($_GET);

/** @var array<Product> $productos */
$productos = $viewData['products'];

/** @var array<int, array{id:int, nombre:string}> $categorias */
$categorias = $viewData['categories'];

/** @var array<int, array{id:int, nombre:string}> $bodegas */
$bodegas = $viewData['wineries'];

/** @var array<string> $paises */
$paises = $viewData['countries'];

/** @var ProductFiltersDTO $filtros */
$filtros = $viewData['filters'];

$errorCatalogo = $viewData['error'];

/*
|--------------------------------------------------------------------------
| Valores seleccionados para la vista
|--------------------------------------------------------------------------
*/

$pais = $filtros->country ?? '';
$categoriaId = $filtros->categoryId;
$bodegaId = $filtros->wineryId;
$cepa = $filtros->grape ?? '';
$precioMax = $filtros->maxPrice;

$categoriaSeleccionada = 'Todas';

foreach ($categorias as $categoria) {
    if ($categoriaId === (int)$categoria['id']) {
        $categoriaSeleccionada =
            $categoria['nombre'];

        break;
    }
}

$bodegaSeleccionada = 'Todas';

foreach ($bodegas as $bodega) {
    if ($bodegaId === (int)$bodega['id']) {
        $bodegaSeleccionada =
            $bodega['nombre'];

        break;
    }
}

include __DIR__ . '/../includes/header.php';

?>

<main class="section">
    <div class="container">

        <div class="section-header">
            <h2>Catálogo</h2>

            <p>
                Explorá vinos argentinos e internacionales
                con filtros dinámicos.
            </p>
        </div>

        <div
            class="form-container"
            style="max-width: 1100px; margin-bottom: 40px;"
        >
            <h2>Buscar vinos</h2>

            <p>
                Filtrá por país, categoría, bodega,
                cepa o presupuesto máximo.
            </p>

            <?php if ($errorCatalogo !== null): ?>
                <div class="delete-warning">
                    <?php echo e($errorCatalogo); ?>
                </div>

                <br>
            <?php endif; ?>

            <form
                method="GET"
                action="<?php echo e(
                    url('pages/catalogo.php')
                ); ?>"
                class="auth-form"
            >
                <div class="form-group">
                    <label for="pais">País</label>

                    <select
                        id="pais"
                        name="pais"
                    >
                        <option value="">
                            Todos
                        </option>

                        <?php foreach ($paises as $item): ?>
                            <option
                                value="<?php echo e($item); ?>"
                                <?php echo $pais === $item
                                    ? 'selected'
                                    : ''; ?>
                            >
                                <?php echo e($item); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="categoria_id">
                        Categoría
                    </label>

                    <select
                        id="categoria_id"
                        name="categoria_id"
                    >
                        <option value="">
                            Todas
                        </option>

                        <?php foreach ($categorias as $categoria): ?>
                            <option
                                value="<?php echo (int)$categoria['id']; ?>"
                                <?php echo $categoriaId === (int)$categoria['id']
                                    ? 'selected'
                                    : ''; ?>
                            >
                                <?php echo e($categoria['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="bodega_id">
                        Bodega
                    </label>

                    <select
                        id="bodega_id"
                        name="bodega_id"
                    >
                        <option value="">
                            Todas
                        </option>

                        <?php foreach ($bodegas as $bodega): ?>
                            <option
                                value="<?php echo (int)$bodega['id']; ?>"
                                <?php echo $bodegaId === (int)$bodega['id']
                                    ? 'selected'
                                    : ''; ?>
                            >
                                <?php echo e($bodega['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="cepa">Cepa</label>

                    <input
                        type="text"
                        id="cepa"
                        name="cepa"
                        value="<?php echo e($cepa); ?>"
                        maxlength="100"
                        placeholder="Malbec, Chardonnay, Blend..."
                    >
                </div>

                <div class="form-group">
                    <label for="precio_max">
                        Precio máximo
                    </label>

                    <input
                        type="number"
                        id="precio_max"
                        name="precio_max"
                        value="<?php echo $precioMax !== null
                            ? e((string)$precioMax)
                            : ''; ?>"
                        min="1"
                        step="0.01"
                        placeholder="Ej: 25000"
                    >
                </div>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Aplicar filtros
                </button>

                <a
                    href="<?php echo e(
                        url('pages/catalogo.php')
                    ); ?>"
                    class="btn btn-secondary"
                >
                    Limpiar filtros
                </a>
            </form>

            <?php if ($filtros->hasFilters()): ?>
                <br>

                <div class="product-data">
                    <p>
                        <strong>Filtros aplicados:</strong>
                    </p>

                    <p>
                        <strong>País:</strong>

                        <?php echo $pais !== ''
                            ? e($pais)
                            : 'Todos'; ?>
                    </p>

                    <p>
                        <strong>Categoría:</strong>

                        <?php echo e(
                            $categoriaSeleccionada
                        ); ?>
                    </p>

                    <p>
                        <strong>Bodega:</strong>

                        <?php echo e(
                            $bodegaSeleccionada
                        ); ?>
                    </p>

                    <p>
                        <strong>Cepa:</strong>

                        <?php echo $cepa !== ''
                            ? e($cepa)
                            : 'Todas'; ?>
                    </p>

                    <p>
                        <strong>Precio máximo:</strong>

                        <?php
                        echo $precioMax !== null
                            ? MONEDA . number_format(
                                $precioMax,
                                0,
                                ',',
                                '.'
                            )
                            : 'Sin límite';
                        ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <div class="section-header">
            <p>
                Productos encontrados:

                <strong>
                    <?php echo count($productos); ?>
                </strong>
            </p>
        </div>

        <div class="products-grid">

            <?php if (empty($productos)): ?>

                <p>
                    No se encontraron vinos con esos filtros.
                </p>

            <?php else: ?>

                <?php foreach ($productos as $producto): ?>

                    <article class="product-card">

                        <img
                            src="<?php echo e(
                                $producto->image()
                            ); ?>"
                            alt="<?php echo e(
                                $producto->name()
                            ); ?>"
                            loading="lazy"
                        >

                        <div class="product-info">

                            <span class="product-category">
                                <?php echo e(
                                    $producto->categoryName()
                                        ?? 'Sin categoría'
                                ); ?>
                            </span>

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
                                    $producto->region()
                                        ?? 'Región no informada'
                                ); ?>
                            </p>

                            <p>
                                <strong>País:</strong>

                                <?php echo e(
                                    $producto->country()
                                        ?? 'No informado'
                                ); ?>
                            </p>

                            <p>
                                <strong>Cepa:</strong>

                                <?php echo e(
                                    $producto->grape()
                                        ?? 'No informada'
                                ); ?>
                            </p>

                            <p>
                                <strong>Stock:</strong>

                                <?php echo $producto->isAvailable()
                                    ? 'disponible'
                                    : 'agotado'; ?>
                            </p>

                            <div class="product-footer">

                                <span class="price">
                                    <?php
                                    echo MONEDA . number_format(
                                        $producto->price(),
                                        0,
                                        ',',
                                        '.'
                                    );
                                    ?>
                                </span>

                                <a
                                    href="<?php echo e(
                                        url('pages/producto.php')
                                    ); ?>?id=<?php echo $producto->id(); ?>"
                                    class="btn-card"
                                >
                                    Ver más
                                </a>

                            </div>
                        </div>

                    </article>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>
    </div>
</main>

<?php

include __DIR__ . '/../includes/footer.php';

?>