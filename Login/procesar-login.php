<?php
session_start();

require_once '../config/database.php';

$pdo = conectarDB();

$email = trim($_POST['email']);
$password = trim($_POST['password']);

$sql = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':email', $email);
$stmt->execute();

$usuario = $stmt->fetch();

if ($usuario && password_verify($password, $usuario['password'])) {

    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_rol'] = $usuario['rol'];

    header("Location: /proyecto_cava_Noble/index.php");
    exit;

} else {

    header("Location: /proyecto_cava_Noble/login/login.php");
    exit;
}