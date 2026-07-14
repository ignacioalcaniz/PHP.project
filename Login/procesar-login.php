<?php

require_once '../includes/session.php';
require_once '../includes/security.php';
require_once '../includes/remember.php';
require_once '../includes/rate-limit.php';
require_once '../includes/captcha.php';
require_once '../config/database.php';

startSecureSession();

/*
|--------------------------------------------------------------------------
| Solo POST
|--------------------------------------------------------------------------
*/

if (!isPostRequest()) {
    redirect('/proyecto_cava_Noble/Login/login.php');
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
| Datos del formulario
|--------------------------------------------------------------------------
*/

$identificador = trim(
    (string)($_POST['identificador'] ?? '')
);

$password = (string)($_POST['password'] ?? '');

$rememberMe =
    isset($_POST['remember_me']) &&
    $_POST['remember_me'] === '1';

if (
    $identificador === '' ||
    $password === '' ||
    mb_strlen($identificador) > 190
) {
    redirect('/proyecto_cava_Noble/Login/login.php?error=1');
}

/*
|--------------------------------------------------------------------------
| Normalización
|--------------------------------------------------------------------------
|
| Los emails se almacenan normalmente en minúsculas. Para username y DNI
| la consulta seguirá funcionando bajo la collation habitual de MySQL.
|
*/

$identificadorNormalizado = filter_var(
    $identificador,
    FILTER_VALIDATE_EMAIL
)
    ? strtolower($identificador)
    : $identificador;

/*
|--------------------------------------------------------------------------
| Rate limit y bloqueo temporal
|--------------------------------------------------------------------------
*/

clearOldLoginAttempts();

if (isLoginBlocked($identificadorNormalizado)) {
    redirect('/proyecto_cava_Noble/Login/login.php?blocked=1');
}

/*
|--------------------------------------------------------------------------
| Cloudflare Turnstile
|--------------------------------------------------------------------------
*/

if (!verifyTurnstileToken()) {
    registerLoginAttempt(
        $identificadorNormalizado,
        false
    );

    redirect('/proyecto_cava_Noble/Login/login.php?captcha=1');
}

$pdo = conectarDB();

/*
|--------------------------------------------------------------------------
| Buscar por email, username o DNI
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        nombre,
        apellido,
        email,
        username,
        dni,
        password,
        rol
    FROM usuarios
    WHERE
        email = :email
        OR username = :username
        OR dni = :dni
    LIMIT 1
";

$stmt = $pdo->prepare($sql);

$stmt->bindValue(
    ':email',
    $identificadorNormalizado,
    PDO::PARAM_STR
);

$stmt->bindValue(
    ':username',
    $identificadorNormalizado,
    PDO::PARAM_STR
);

$stmt->bindValue(
    ':dni',
    $identificadorNormalizado,
    PDO::PARAM_STR
);

$stmt->execute();

$usuario = $stmt->fetch();

/*
|--------------------------------------------------------------------------
| Comprobación de contraseña
|--------------------------------------------------------------------------
|
| Se usa un mensaje genérico para no revelar si la cuenta existe.
|
*/

if (
    !$usuario ||
    !password_verify($password, $usuario['password'])
) {
    registerLoginAttempt(
        $identificadorNormalizado,
        false
    );

    redirect('/proyecto_cava_Noble/Login/login.php?error=1');
}

/*
|--------------------------------------------------------------------------
| Protección frente a session fixation
|--------------------------------------------------------------------------
*/

session_regenerate_id(true);

/*
|--------------------------------------------------------------------------
| Crear sesión autenticada
|--------------------------------------------------------------------------
*/

$_SESSION['usuario_id'] = (int)$usuario['id'];
$_SESSION['usuario_nombre'] = $usuario['nombre'];
$_SESSION['usuario_apellido'] = $usuario['apellido'];
$_SESSION['usuario_email'] = $usuario['email'];
$_SESSION['usuario_rol'] = $usuario['rol'] ?? 'cliente';
$_SESSION['login_time'] = time();
$_SESSION['last_activity'] = time();

/*
|--------------------------------------------------------------------------
| Renovar token CSRF después del login
|--------------------------------------------------------------------------
*/

unset($_SESSION['csrf_token']);

generateCsrfToken();

/*
|--------------------------------------------------------------------------
| Registrar acceso exitoso
|--------------------------------------------------------------------------
*/

registerLoginAttempt(
    $usuario['email'],
    true
);

/*
|--------------------------------------------------------------------------
| Recordarme
|--------------------------------------------------------------------------
*/

if ($rememberMe) {
    createRememberToken((int)$usuario['id']);
} else {
    clearRememberCookie();
}

redirect('/proyecto_cava_Noble/index.php');