<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/security.php';

requireAdmin();

require_once __DIR__ . '/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Datos del formulario
|--------------------------------------------------------------------------
|
| El controlador obtiene las categorías y bodegas necesarias para
| construir el formulario. La vista no accede directamente a MySQL.
|
*/

$result = $productController->createForm();

$categorias = $result['categories'];
$bodegas = $result['wineries'];
$error = $result['error'];

/*
|--------------------------------------------------------------------------
| Error de una operación anterior
|--------------------------------------------------------------------------
|
| Si procesar-crear-producto.php no pudo crear el producto, guarda
| temporalmente el mensaje en la sesión y redirige nuevamente aquí.
|
*/

$operationError = null;

if (isset($_GET['error'])) {
    $sessionError =
        $_SESSION['admin_product_error']
        ?? 'No se pudo crear el producto.';

    $operationError = is_string($sessionError)
        ? $sessionError
        : 'No se pudo crear el producto.';

    unset($_SESSION['admin_product_error']);
}

/*
|--------------------------------------------------------------------------
| Token CSRF
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
            <h2>Crear producto</h2>

            <p>
                Agregar un nuevo vino al catálogo.
            </p>
        </div>

        <div
            class="form-container"
            style="max-width: 800px;"
        >

            <?php if ($error !== null): ?>
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
                        No se pudo cargar el formulario.
                    </strong>

                    <p>
                        <?php echo e($error); ?>
                    </p>
                </div>
            <?php endif; ?>

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
                        No se pudo crear el producto.
                    </strong>

                    <p>
                        <?php echo e($operationError); ?>
                    </p>
                </div>
            <?php endif; ?>

            <form
                action="/proyecto_cava_Noble/admin/procesar-crear-producto.php"
                method="POST"
                enctype="multipart/form-data"
                class="auth-form"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo e($csrfToken); ?>"
                >

                <div class="form-group">
                    <label for="nombre">
                        Nombre
                    </label>

                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        maxlength="150"
                        autocomplete="off"
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
                    ></textarea>
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
                        maxlength="100"
                        autocomplete="off"
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
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="imagen">
                        Imagen
                    </label>

                    <input
                        type="file"
                        id="imagen"
                        name="imagen"
                        accept="image/jpeg,image/png,image/webp"
                        required
                    >

                    <small>
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
                        <option value="0">
                            No
                        </option>

                        <option value="1">
                            Sí
                        </option>
                    </select>
                </div>

                <button
                    type="submit"
                    class="btn btn-primary"
                    <?php
                        echo $error !== null
                            ? 'disabled'
                            : '';
                    ?>
                >
                    Crear producto
                </button>

                <a
                    href="/proyecto_cava_Noble/admin/productos.php"
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