<?php

require_once '../includes/session.php';
require_once '../includes/security.php';
require_once '../includes/remember.php';
require_once '../includes/rate-limit.php';
require_once '../includes/captcha.php';
require_once '../config/database.php';

startSecureSession();

if (!isPostRequest()) {
    redirect('/proyecto_cava_Noble/Login/login.php');
}

if (
    !isset($_POST['csrf_token']) ||
    !validateCsrfToken($_POST['csrf_token'])
) {
    die('Token CSRF inválido.');
}

$pdo = conectarDB();

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

$rememberMe =
    isset($_POST['remember_me']) &&
    $_POST['remember_me'] === '1';

if ($email === '' || $password === '') {
    redirect('/proyecto_cava_Noble/Login/login.php?error=1');
}

clearOldLoginAttempts();

if (isLoginBlocked($email)) {
    redirect('/proyecto_cava_Noble/Login/login.php?blocked=1');
}

if (!verifyTurnstileToken()) {
    registerLoginAttempt($email, false);
    redirect('/proyecto_cava_Noble/Login/login.php?captcha=1');
}

$sql = "
    SELECT *
    FROM usuarios
    WHERE email = :email
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':email', $email);
$stmt->execute();

$usuario = $stmt->fetch();

if (
    !$usuario ||
    !password_verify($password, $usuario['password'])
) {
    registerLoginAttempt($email, false);
    redirect('/proyecto_cava_Noble/Login/login.php?error=1');
}

session_regenerate_id(true);

$_SESSION['usuario_id'] = (int)$usuario['id'];
$_SESSION['usuario_nombre'] = $usuario['nombre'];
$_SESSION['usuario_email'] = $usuario['email'];
$_SESSION['usuario_rol'] = $usuario['rol'] ?? 'cliente';
$_SESSION['login_time'] = time();
$_SESSION['last_activity'] = time();

unset($_SESSION['csrf_token']);

registerLoginAttempt($email, true);

if ($rememberMe) {
    createRememberToken((int)$usuario['id']);
} else {
    clearRememberCookie();
}

redirect('/proyecto_cava_Noble/index.php');