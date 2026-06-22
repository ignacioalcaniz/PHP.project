<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/remember.php';

startSecureSession();
attemptRememberLogin();

function isLoggedIn(): bool
{
    return isset($_SESSION['usuario_id']);
}

function isAdmin(): bool
{
    return isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin';
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        redirect('/proyecto_cava_Noble/Login/login.php');
    }
}

function requireAdmin(): void
{
    if (!isLoggedIn() || !isAdmin()) {
        redirect('/proyecto_cava_Noble/index.php');
    }
}