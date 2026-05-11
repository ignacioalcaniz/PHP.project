<?php
require_once '../config/database.php';

$pdo = conectarDB();

$nombre = trim($_POST['nombre']);
$apellido = trim($_POST['apellido']);
$email = trim($_POST['email']);
$password = trim($_POST['password']);

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios (nombre, apellido, email, password)
VALUES (:nombre, :apellido, :email, :password)";

$stmt = $pdo->prepare($sql);

$stmt->bindParam(':nombre', $nombre);
$stmt->bindParam(':apellido', $apellido);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':password', $passwordHash);

$stmt->execute();

header("Location: /proyecto_cava_Noble/login/login.php");
exit;