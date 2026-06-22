<?php

function isLocalEnvironment(): bool
{
    $host = $_SERVER['HTTP_HOST'] ?? '';

    return str_contains($host, 'localhost') ||
           str_contains($host, '127.0.0.1');
}

function isHttpsRequest(): bool
{
    return (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (($_SERVER['SERVER_PORT'] ?? '') === '443')
    );
}

function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        validateSessionActivity();
        return;
    }

    $secure = !isLocalEnvironment() && isHttpsRequest();

    session_name('CAVA_NOBLE_SESSION');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/proyecto_cava_Noble/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();

    if (!isset($_SESSION['created_at'])) {
        $_SESSION['created_at'] = time();
    }

    validateSessionActivity();
}

function validateSessionActivity(): void
{
    $timeout = 60 * 20;

    if (!isset($_SESSION['usuario_id'])) {
        return;
    }

    if (
        isset($_SESSION['last_activity']) &&
        (time() - $_SESSION['last_activity']) > $timeout
    ) {
        destroySecureSession();
        header('Location: /proyecto_cava_Noble/Login/login.php?expired=1');
        exit;
    }

    $_SESSION['last_activity'] = time();
}

function destroySecureSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}