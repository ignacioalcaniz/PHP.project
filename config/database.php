<?php

function conectarDB() {

    $host = 'sql305.infinityfree.com';
    $dbname = 'if0_41893077_Cava_Noble';
    $usuario = 'if0_41893077';
    $password = 'Secundaria2015';

    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $usuario,
            $password
        );

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;

    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}