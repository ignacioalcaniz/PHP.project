<?php

declare(strict_types=1);

use App\Controllers\ProductController;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\MySQL\MySqlProductRepository;
use App\Services\ProductService;

/*
|--------------------------------------------------------------------------
| Composer Autoload
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Configuración general
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../config/app.php';

/*
|--------------------------------------------------------------------------
| Manejo de errores por entorno
|--------------------------------------------------------------------------
*/

error_reporting(E_ALL);

if (APP_DEBUG) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
}

/*
|--------------------------------------------------------------------------
| Base de datos
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../config/database.php';

/*
|--------------------------------------------------------------------------
| Helpers compartidos
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/helpers.php';

/*
|--------------------------------------------------------------------------
| Conexión PDO
|--------------------------------------------------------------------------
*/

$pdo = conectarDB();

/*
|--------------------------------------------------------------------------
| Módulo Productos
|--------------------------------------------------------------------------
|
| Las dependencias se construyen desde afuera:
|
| Controller
|     → Service
|         → RepositoryInterface
|             → MySqlProductRepository
|                 → PDO
|
*/

$productRepository = new MySqlProductRepository($pdo);

$productService = new ProductService(
    $productRepository
);

$productController = new ProductController(
    $productService
);