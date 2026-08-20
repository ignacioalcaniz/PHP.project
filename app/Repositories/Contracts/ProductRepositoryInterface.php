<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTOs\CreateProductDTO;
use App\DTOs\ProductFiltersDTO;
use App\DTOs\UpdateProductDTO;
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

    /**
     * @return array{
     *     id:int,
     *     nombre:string,
     *     pais:string,
     *     region:string
     * }|null
     */
    public function findWineryById(
        int $id
    ): ?array;

    public function create(
        CreateProductDTO $data,
        string $imagePath,
        array $winery
    ): int;

    public function update(
        UpdateProductDTO $data,
        string $imagePath,
        array $winery
    ): void;

    public function hasOrderItems(
        int $productId
    ): bool;

    public function delete(
        int $productId
    ): void;
}