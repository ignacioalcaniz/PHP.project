<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/security.php';

requireAdmin();

require_once __DIR__ . '/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Solo solicitudes POST
|--------------------------------------------------------------------------
*/

if (!isPostRequest()) {
    redirect(
        url('admin/productos.php')
    );
}

/*
|--------------------------------------------------------------------------
| CSRF
|--------------------------------------------------------------------------
*/

$csrfToken = $_POST['csrf_token'] ?? '';

if (
    !is_string($csrfToken) ||
    $csrfToken === '' ||
    !validateCsrfToken($csrfToken)
) {
    http_response_code(403);

    die('Token CSRF inválido.');
}

/*
|--------------------------------------------------------------------------
| Administrador autenticado
|--------------------------------------------------------------------------
*/

$adminId = (int)(
    $_SESSION['usuario_id']
    ?? 0
);

if ($adminId <= 0) {
    redirect(
        url('Login/login.php')
    );
}

/*
|--------------------------------------------------------------------------
| Producto
|--------------------------------------------------------------------------
*/

$productId = filter_var(
    $_POST['id'] ?? null,
    FILTER_VALIDATE_INT,
    [
        'options' => [
            'min_range' => 1,
        ],
    ]
);

if (
    $productId === false ||
    $productId === null
) {
    $_SESSION['admin_product_error'] =
        'El producto indicado no es válido.';

    redirect(
        url('admin/productos.php?error=1')
    );
}

/*
|--------------------------------------------------------------------------
| Actualizar
|--------------------------------------------------------------------------
*/

$result = $productController->update(
    $_POST,
    $_FILES,
    $adminId
);

/*
|--------------------------------------------------------------------------
| Operación exitosa
|--------------------------------------------------------------------------
*/

if ($result['success'] === true) {
    unset(
        $_SESSION['admin_product_error'],
        $_SESSION['admin_product_old_input']
    );

    redirect(
        url('admin/productos.php?updated=1')
    );
}

/*
|--------------------------------------------------------------------------
| Error controlado
|--------------------------------------------------------------------------
|
| Guardamos el error y los datos escritos para que el administrador
| pueda corregir únicamente el campo problemático.
|
*/

$error = $result['error']
    ?? 'No se pudo actualizar el producto.';

$_SESSION['admin_product_error'] = $error;

/*
|--------------------------------------------------------------------------
| Old input
|--------------------------------------------------------------------------
|
| No guardamos CSRF ni otros datos innecesarios en la sesión.
|
*/

$oldInput = $_POST;

unset(
    $oldInput['csrf_token'],
    $oldInput['imagen_actual']
);

$_SESSION['admin_product_old_input'] =
    $oldInput;

/*
|--------------------------------------------------------------------------
| Volver al formulario
|--------------------------------------------------------------------------
*/

redirect(
    url(
        'admin/editar-producto.php?id=' .
        (int)$productId .
        '&error=1'
    )
);