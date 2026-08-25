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
        '/proyecto_cava_Noble/admin/productos.php'
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

$adminId = (int)($_SESSION['usuario_id'] ?? 0);

if ($adminId <= 0) {
    redirect(
        '/proyecto_cava_Noble/Login/login.php'
    );
}

/*
|--------------------------------------------------------------------------
| Crear producto
|--------------------------------------------------------------------------
*/

$result = $productController->store(
    $_POST,
    $_FILES,
    $adminId
);

if ($result['success'] === true) {
    redirect(
        '/proyecto_cava_Noble/admin/productos.php?created=1'
    );
}

/*
|--------------------------------------------------------------------------
| Error controlado
|--------------------------------------------------------------------------
*/

$error = $result['error']
    ?? 'No se pudo crear el producto.';

$_SESSION['admin_product_error'] = $error;

redirect(
    '/proyecto_cava_Noble/admin/crear-producto.php?error=1'
);