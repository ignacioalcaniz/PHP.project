<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ProductFiltersDTO;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use InvalidArgumentException;

final readonly class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {
    }

    /**
     * Devuelve la información completa necesaria para mostrar el catálogo.
     *
     * @return array{
     *     products: array<Product>,
     *     categories: array<int, array{id:int, nombre:string}>,
     *     wineries: array<int, array{id:int, nombre:string}>,
     *     countries: array<string>,
     *     filters: ProductFiltersDTO
     * }
     */
    public function getCatalogData(
        ProductFiltersDTO $filters
    ): array {
        return [
            'products' => $this->productRepository->findCatalog(
                $filters
            ),

            'categories' => $this->productRepository
                ->findCategories(),

            'wineries' => $this->productRepository
                ->findWineries(),

            'countries' => $this->productRepository
                ->findAvailableCountries(),

            'filters' => $filters,
        ];
    }

    public function getProductById(
        int $id
    ): ?Product {
        if ($id <= 0) {
            throw new InvalidArgumentException(
                'El identificador del producto no es válido.'
            );
        }

        return $this->productRepository->findById($id);
    }

    /**
     * @return array<Product>
     */
    public function getFeaturedProducts(
        int $limit = 3
    ): array {
        if ($limit <= 0) {
            throw new InvalidArgumentException(
                'El límite debe ser mayor que cero.'
            );
        }

        return $this->productRepository->findFeatured(
            min($limit, 50)
        );
    }

    public function productExists(
        int $id
    ): bool {
        if ($id <= 0) {
            return false;
        }

        return $this->productRepository->findById($id)
            instanceof Product;
    }

    public function canPurchase(
        Product $product,
        int $quantity
    ): bool {
        if ($quantity <= 0) {
            return false;
        }

        return $product->isAvailable()
            && $product->hasStock($quantity);
    }

   /**
 * @return array{
 *     product: Product,
 *     discount: float,
 *     finalPrice: float
 * }
 */
public function getProductDetail(
    int $id
): array {
    $product = $this->getProductById($id);

    if (!$product instanceof Product) {
        throw new InvalidArgumentException(
            'El producto solicitado no existe.'
        );
    }

    /*
     * Regla comercial actual de Cava Noble:
     * los productos destacados reciben un descuento fijo.
     *
     * Más adelante esta regla podrá migrarse a un sistema
     * profesional de promociones y cupones.
     */
    $discount = $product->isFeatured()
        ? 1500.0
        : 0.0;

    $finalPrice = max(
        0.0,
        $product->price() - $discount
    );

    return [
        'product' => $product,
        'discount' => $discount,
        'finalPrice' => $finalPrice,
    ];
} 
}