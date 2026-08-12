<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Composer Autoload
|--------------------------------------------------------------------------
|
| Algunas páginas antiguas todavía cargan config/app.php directamente
| (por ejemplo Login, Registro, Captcha, etc.).
| Si Composer todavía no fue cargado, lo cargamos aquí.
|
*/

$autoloadPath = dirname(__DIR__) . DIRECTORY_SEPARATOR
    . 'vendor'
    . DIRECTORY_SEPARATOR
    . 'autoload.php';

if (!class_exists(\Dotenv\Dotenv::class)) {

    if (!is_file($autoloadPath)) {
        throw new RuntimeException(
            'No se encontró vendor/autoload.php. Ejecutá composer install.'
        );
    }

    require_once $autoloadPath;
}

use Dotenv\Dotenv;

/*
|--------------------------------------------------------------------------
| Carga de variables de entorno
|--------------------------------------------------------------------------
*/

$rootPath = dirname(__DIR__);
$envPath = $rootPath . DIRECTORY_SEPARATOR . '.env';

if (!is_file($envPath)) {
    throw new RuntimeException(
        'No se encontró el archivo de configuración .env.'
    );
}

/*
|--------------------------------------------------------------------------
| Dotenv
|--------------------------------------------------------------------------
*/

$dotenv = Dotenv::createImmutable($rootPath);

$dotenv->safeLoad();

/*
|--------------------------------------------------------------------------
| Variables obligatorias
|--------------------------------------------------------------------------
*/

$dotenv->required([
    'APP_ENV',
    'APP_DEBUG',
    'APP_URL',
    'DB_HOST',
    'DB_PORT',
    'DB_DATABASE',
    'DB_USERNAME',
]);

/*
|--------------------------------------------------------------------------
| Helper env()
|--------------------------------------------------------------------------
*/

if (!function_exists('env')) {

    function env(
        string $key,
        mixed $default = null
    ): mixed {

        $value =
            $_ENV[$key]
            ?? $_SERVER[$key]
            ?? getenv($key);

        if (
            $value === false ||
            $value === null ||
            $value === ''
        ) {
            return $default;
        }

        return match (
            strtolower(trim((string)$value))
        ) {
            'true',
            '(true)' => true,

            'false',
            '(false)' => false,

            'null',
            '(null)' => null,

            'empty',
            '(empty)' => '',

            default => $value,
        };
    }
}

/*
|--------------------------------------------------------------------------
| Configuración general
|--------------------------------------------------------------------------
*/

if (!defined('APP_ENV')) {

    define(
        'APP_ENV',
        (string) env(
            'APP_ENV',
            'production'
        )
    );
}

if (!defined('APP_DEBUG')) {

    define(
        'APP_DEBUG',
        (bool) env(
            'APP_DEBUG',
            false
        )
    );
}

if (!defined('APP_URL')) {

    define(
        'APP_URL',
        rtrim(
            (string) env('APP_URL', ''),
            '/'
        )
    );
}

/*
|--------------------------------------------------------------------------
| Cloudflare Turnstile
|--------------------------------------------------------------------------
*/

if (!defined('TURNSTILE_SITE_KEY')) {

    define(
        'TURNSTILE_SITE_KEY',
        (string) env(
            'TURNSTILE_SITE_KEY',
            ''
        )
    );
}

if (!defined('TURNSTILE_SECRET_KEY')) {

    define(
        'TURNSTILE_SECRET_KEY',
        (string) env(
            'TURNSTILE_SECRET_KEY',
            ''
        )
    );
}