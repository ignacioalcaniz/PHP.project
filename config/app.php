<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Carga de variables de entorno
|--------------------------------------------------------------------------
*/

$envPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';

if (!is_file($envPath)) {
    throw new RuntimeException(
        'No se encontró el archivo de configuración .env.'
    );
}

$variables = parse_ini_file(
    $envPath,
    false,
    INI_SCANNER_RAW
);

if ($variables === false) {
    throw new RuntimeException(
        'No se pudo leer el archivo .env.'
    );
}

foreach ($variables as $key => $value) {
    $key = trim((string)$key);
    $value = trim((string)$value);

    if ($key === '') {
        continue;
    }

    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;

    putenv($key . '=' . $value);
}

/*
|--------------------------------------------------------------------------
| Helper para obtener variables
|--------------------------------------------------------------------------
*/

function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);

    if ($value === false || $value === null || $value === '') {
        return $default;
    }

    return match (strtolower((string)$value)) {
        'true', '(true)' => true,
        'false', '(false)' => false,
        'null', '(null)' => null,
        'empty', '(empty)' => '',
        default => $value,
    };
}

/*
|--------------------------------------------------------------------------
| Configuración general
|--------------------------------------------------------------------------
*/

define('APP_ENV', (string)env('APP_ENV', 'production'));
define('APP_DEBUG', (bool)env('APP_DEBUG', false));
define('APP_URL', (string)env('APP_URL', ''));

/*
|--------------------------------------------------------------------------
| Cloudflare Turnstile
|--------------------------------------------------------------------------
*/

define(
    'TURNSTILE_SITE_KEY',
    (string)env('TURNSTILE_SITE_KEY', '')
);

define(
    'TURNSTILE_SECRET_KEY',
    (string)env('TURNSTILE_SECRET_KEY', '')
);