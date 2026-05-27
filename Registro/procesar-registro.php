<?php
require_once '../includes/security.php';
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isPostRequest()) {
    redirect('/proyecto_cava_Noble/Registro/registro.php');
}

if (
    !isset($_POST['csrf_token']) ||
    !validateCsrfToken($_POST['csrf_token'])
) {
    die('Token CSRF inválido.');
}

$pdo = conectarDB();

$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (
    $nombre === '' ||
    $apellido === '' ||
    $email === '' ||
    $password === '' ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($password) < 6
) {
    redirect('/proyecto_cava_Noble/Registro/registro.php?error=1');
}

$sqlExiste = "
    SELECT id
    FROM usuarios
    WHERE email = :email
    LIMIT 1
";

$stmtExiste = $pdo->prepare($sqlExiste);
$stmtExiste->bindParam(':email', $email);
$stmtExiste->execute();

$usuarioExistente = $stmtExiste->fetch();

if ($usuarioExistente) {
    redirect('/proyecto_cava_Noble/Registro/registro.php?duplicado=1');
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$sql = "
    INSERT INTO usuarios (
        nombre,
        apellido,
        email,
        password,
        rol
    )
    VALUES (
        :nombre,
        :apellido,
        :email,
        :password,
        'cliente'
    )
";

$stmt = $pdo->prepare($sql);

$stmt->bindParam(':nombre', $nombre);
$stmt->bindParam(':apellido', $apellido);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':password', $passwordHash);

$stmt->execute();

unset($_SESSION['csrf_token']);

redirect('/proyecto_cava_Noble/Login/login.php');