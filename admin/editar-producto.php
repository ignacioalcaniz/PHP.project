<?php

declare(strict_types=1);

use App\Models\Product;

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/security.php';

requireAdmin();

require_once __DIR__ . '/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Obtener producto y datos del formulario
|--------------------------------------------------------------------------
*/

$result = $productController->editForm(
    $_GET['id'] ?? null
);

/** @var Product|null $producto */
$producto = $result['product'];

$categorias = $result['categories'];
$bodegas = $result['wineries'];
$error = $result['error'];

/*
|--------------------------------------------------------------------------
| Producto inválido o inexistente
|--------------------------------------------------------------------------
*/

if (
    !$producto instanceof Product ||
    $error !== null
) {
    $_SESSION['admin_product_error'] =
        $error ?? 'No se pudo cargar el producto.';

    redirect(
        url('admin/productos.php?error=1')
    );
}

/*
|--------------------------------------------------------------------------
| Error de una edición anterior
|--------------------------------------------------------------------------
*/

$operationError = null;

if (isset($_GET['error'])) {
    $sessionError =
        $_SESSION['admin_product_error']
        ?? 'No se pudo actualizar el producto.';

    $operationError = is_string($sessionError)
        ? $sessionError
        : 'No se pudo actualizar el producto.';

    unset($_SESSION['admin_product_error']);
}

/*
|--------------------------------------------------------------------------
| Recuperar datos enviados si la edición anterior falló
|--------------------------------------------------------------------------
|
| Esto evita que el administrador tenga que volver a completar
| todo el formulario después de un error de validación.
|
*/

$oldInput = $_SESSION['admin_product_old_input']
    ?? [];

unset($_SESSION['admin_product_old_input']);

if (!is_array($oldInput)) {
    $oldInput = [];
}

/*
|--------------------------------------------------------------------------
| Valores del formulario
|--------------------------------------------------------------------------
*/

$nombre = isset($oldInput['nombre'])
    ? trim((string)$oldInput['nombre'])
    : $producto->name();

$descripcion = isset($oldInput['descripcion'])
    ? trim((string)$oldInput['descripcion'])
    : $producto->description();

$precio = isset($oldInput['precio'])
    ? (string)$oldInput['precio']
    : (string)$producto->price();

$categoriaId = isset($oldInput['categoria_id'])
    ? (int)$oldInput['categoria_id']
    : $producto->categoryId();

$bodegaId = isset($oldInput['bodega_id'])
    ? (int)$oldInput['bodega_id']
    : $producto->wineryId();

$cepa = isset($oldInput['cepa'])
    ? trim((string)$oldInput['cepa'])
    : ($producto->grape() ?? '');

$anada = isset($oldInput['anada'])
    ? (int)$oldInput['anada']
    : $producto->vintage();

$stock = isset($oldInput['stock'])
    ? (int)$oldInput['stock']
    : $producto->stock();

$destacado = isset($oldInput['destacado'])
    ? (int)$oldInput['destacado']
    : ($producto->isFeatured() ? 1 : 0);

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
            <h2>Editar producto</h2>

            <p>
                Modificá la información del vino seleccionado.
            </p>
        </div>

        <div
            class="form-container"
            style="max-width: 800px;"
        >

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
                        No se pudo actualizar el producto.
                    </strong>

                    <p>
                        <?php echo e($operationError); ?>
                    </p>
                </div>

            <?php endif; ?>

            <form
                action="<?php echo e(
                    url('admin/procesar-editar-producto.php')
                ); ?>"
                method="POST"
                enctype="multipart/form-data"
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

                <!--
                    Se conserva por compatibilidad con UpdateProductDTO.
                    ProductService NO confía en este valor como fuente
                    de verdad: consulta nuevamente la imagen desde MySQL.
                -->
                <input
                    type="hidden"
                    name="imagen_actual"
                    value="<?php echo e(
                        $producto->image()
                    ); ?>"
                >

                <div class="form-group">
                    <label for="nombre">
                        Nombre
                    </label>

                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        value="<?php echo e($nombre); ?>"
                        maxlength="150"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="descripcion">
                        Descripción
                    </label>

                    <textarea
                        id="descripcion"
                        name="descripcion"
                        rows="5"
                        required
                    ><?php echo e($descripcion); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="precio">
                        Precio
                    </label>

                    <input
                        type="number"
                        id="precio"
                        name="precio"
                        min="0.01"
                        step="0.01"
                        inputmode="decimal"
                        value="<?php echo e($precio); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="categoria_id">
                        Categoría
                    </label>

                    <select
                        id="categoria_id"
                        name="categoria_id"
                        required
                    >
                        <option value="">
                            Seleccionar
                        </option>

                        <?php foreach ($categorias as $categoria): ?>

                            <option
                                value="<?php
                                    echo (int)$categoria['id'];
                                ?>"
                                <?php
                                    echo $categoriaId ===
                                        (int)$categoria['id']
                                            ? 'selected'
                                            : '';
                                ?>
                            >
                                <?php
                                    echo e($categoria['nombre']);
                                ?>
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
                        required
                    >
                        <option value="">
                            Seleccionar
                        </option>

                        <?php foreach ($bodegas as $bodega): ?>

                            <option
                                value="<?php
                                    echo (int)$bodega['id'];
                                ?>"
                                <?php
                                    echo $bodegaId ===
                                        (int)$bodega['id']
                                            ? 'selected'
                                            : '';
                                ?>
                            >
                                <?php
                                    echo e($bodega['nombre']);
                                ?>
                            </option>

                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="cepa">
                        Cepa
                    </label>

                    <input
                        type="text"
                        id="cepa"
                        name="cepa"
                        value="<?php echo e($cepa); ?>"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="anada">
                        Añada
                    </label>

                    <input
                        type="number"
                        id="anada"
                        name="anada"
                        min="1900"
                        max="<?php echo (int)date('Y') + 1; ?>"
                        value="<?php echo (int)$anada; ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="stock">
                        Stock
                    </label>

                    <input
                        type="number"
                        id="stock"
                        name="stock"
                        min="0"
                        step="1"
                        value="<?php echo (int)$stock; ?>"
                        required
                    >
                </div>

                <div class="form-group">

                    <label>
                        Imagen actual
                    </label>

                    <img
                        src="<?php echo e(
                            $producto->image()
                        ); ?>"
                        alt="<?php echo e(
                            $producto->name()
                        ); ?>"
                        style="
                            max-width: 180px;
                            border-radius: 16px;
                            background: #fff;
                            padding: 10px;
                        "
                    >

                </div>

                <div class="form-group">
                    <label for="imagen">
                        Nueva imagen
                    </label>

                    <input
                        type="file"
                        id="imagen"
                        name="imagen"
                        accept="image/jpeg,image/png,image/webp"
                    >

                    <small>
                        Opcional. Si no seleccionás una nueva
                        imagen, se mantiene la actual.
                        Formatos permitidos: JPG, PNG y WEBP.
                        Tamaño máximo: 5 MB.
                    </small>
                </div>

                <div class="form-group">
                    <label for="destacado">
                        Producto destacado
                    </label>

                    <select
                        id="destacado"
                        name="destacado"
                        required
                    >
                        <option
                            value="0"
                            <?php
                                echo $destacado === 0
                                    ? 'selected'
                                    : '';
                            ?>
                        >
                            No
                        </option>

                        <option
                            value="1"
                            <?php
                                echo $destacado === 1
                                    ? 'selected'
                                    : '';
                            ?>
                        >
                            Sí
                        </option>
                    </select>
                </div>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Guardar cambios
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