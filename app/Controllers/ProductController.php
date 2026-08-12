<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTOs\ProductFiltersDTO;
use App\Models\Product;
use App\Services\ProductService;
use InvalidArgumentException;

final readonly class ProductController
{
    public function __construct(
        private ProductService $productService
    ) {
    }

    /**
     * Prepara toda la información necesaria para la vista del catálogo.
     *
     * @param array<string, mixed> $query
     *
     * @return array{
     *     products: array<Product>,
     *     categories: array<int, array{id:int, nombre:string}>,
     *     wineries: array<int, array{id:int, nombre:string}>,
     *     countries: array<string>,
     *     filters: ProductFiltersDTO,
     *     error: string|null
     * }
     */
    public function catalog(array $query): array
    {
        try {
            $filters = ProductFiltersDTO::fromQuery($query);

            $catalogData = $this->productService->getCatalogData(
                $filters
            );

            return [
                ...$catalogData,
                'error' => null,
            ];
        } catch (InvalidArgumentException $exception) {
            return [
                'products' => [],
                'categories' => [],
                'wineries' => [],
                'countries' => [],
                'filters' => new ProductFiltersDTO(),
                'error' => $exception->getMessage(),
            ];
        }
    }

    /**
 * @return array{
 *     product: Product|null,
 *     discount: float,
 *     finalPrice: float,
 *     error: string|null
 * }
 */
public function show(
    mixed $rawId
): array {
    $id = filter_var(
        $rawId,
        FILTER_VALIDATE_INT,
        [
            'options' => [
                'min_range' => 1,
            ],
        ]
    );

    if ($id === false) {
        return [
            'product' => null,
            'discount' => 0.0,
            'finalPrice' => 0.0,
            'error' => 'El producto solicitado no es válido.',
        ];
    }

    try {
        $detail = $this->productService
            ->getProductDetail($id);

        return [
            ...$detail,
            'error' => null,
        ];
    } catch (InvalidArgumentException $exception) {
        return [
            'product' => null,
            'discount' => 0.0,
            'finalPrice' => 0.0,
            'error' => $exception->getMessage(),
        ];
    }
}
}