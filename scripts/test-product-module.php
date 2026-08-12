<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';

$viewData = $productController->catalog([]);

echo 'Productos: ' . count($viewData['products']) . PHP_EOL;
echo 'Categorías: ' . count($viewData['categories']) . PHP_EOL;
echo 'Bodegas: ' . count($viewData['wineries']) . PHP_EOL;
echo 'Países: ' . count($viewData['countries']) . PHP_EOL;

if ($viewData['error'] !== null) {
    echo 'Error: ' . $viewData['error'] . PHP_EOL;
}