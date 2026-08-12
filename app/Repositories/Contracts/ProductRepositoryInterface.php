<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTOs\ProductFiltersDTO;
use App\Models\Product;

interface ProductRepositoryInterface
{
    /**
     * @return array<Product>
     */
    public function findCatalog(
        ProductFiltersDTO $filters
    ): array;

    public function findById(
        int $id
    ): ?Product;

    /**
     * @return array<Product>
     */
    public function findFeatured(
        int $limit = 3
    ): array;

    /**
     * @return array<string>
     */
    public function findAvailableCountries(): array;

    /**
     * @return array<int, array{id:int, nombre:string}>
     */
    public function findCategories(): array;

    /**
     * @return array<int, array{id:int, nombre:string}>
     */
    public function findWineries(): array;
}