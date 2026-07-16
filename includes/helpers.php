<?php

function e(mixed $value): string
{
    return htmlspecialchars(
        (string)($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

function formatPrice(float|int|string $price): string
{
    return '$' . number_format((float)$price, 0, ',', '.');
}

function stockLabel(float|int|string $stock): string
{
    return (int)$stock > 0 ? 'Disponible' : 'Agotado';
}
function url(string $path = ''): string
{
    $baseUrl = defined('APP_URL')
        ? APP_URL
        : ($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '');

    $baseUrl = rtrim($baseUrl, '/');
    $path = ltrim($path, '/');

    if ($path === '') {
        return $baseUrl;
    }

    return $baseUrl . '/' . $path;
}