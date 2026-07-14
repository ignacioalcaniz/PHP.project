<?php

declare(strict_types=1);

require_once __DIR__ . '/app.php';

function conectarDB(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = (string)env('DB_HOST', '127.0.0.1');
    $port = (int)env('DB_PORT', 3306);
    $database = (string)env('DB_DATABASE', '');
    $username = (string)env('DB_USERNAME', 'root');
    $password = (string)env('DB_PASSWORD', '');

    if ($database === '') {
        throw new RuntimeException(
            'La variable DB_DATABASE no está configurada.'
        );
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $host,
        $port,
        $database
    );

    try {
        $pdo = new PDO(
            $dsn,
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND =>
                    "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]
        );

        return $pdo;
    } catch (PDOException $exception) {
        error_log(
            '[Cava Noble] Error de conexión MySQL: ' .
            $exception->getMessage()
        );

        if (APP_DEBUG) {
            throw new RuntimeException(
                'Error de conexión: ' . $exception->getMessage(),
                0,
                $exception
            );
        }

        throw new RuntimeException(
            'No se pudo conectar con la base de datos.'
        );
    }
}