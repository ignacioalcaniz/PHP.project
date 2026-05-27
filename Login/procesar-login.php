<?php
require_once '../includes/security.php';
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

if ($email === '' || $password === '') {
    redirect('/proyecto_cava_Noble/Login/login.php?error=1');
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

if (!$usuario || !password_verify($password, $usuario['password'])) {
    redirect('/proyecto_cava_Noble/Login/login.php?error=1');
}

session_regenerate_id(true);

$_SESSION['usuario_id'] = $usuario['id'];
$_SESSION['usuario_nombre'] = $usuario['nombre'];
$_SESSION['usuario_email'] = $usuario['email'];
$_SESSION['usuario_rol'] = $usuario['rol'] ?? 'cliente';

unset($_SESSION['csrf_token']);

redirect('/proyecto_cava_Noble/index.php');