<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class ProductFiltersDTO
{
    public function __construct(
        public ?int $categoryId = null,
        public ?int $wineryId = null,
        public ?string $country = null,
        public ?string $grape = null,
        public ?float $maxPrice = null,
        public ?string $search = null
    ) {
    }

    /**
     * @param array<string, mixed> $query
     */
    public static function fromQuery(array $query): self
    {
        $categoryId = self::positiveInteger(
            $query['categoria_id']
                ?? $query['categoria']
                ?? null
        );

        $wineryId = self::positiveInteger(
            $query['bodega_id']
                ?? $query['bodega']
                ?? null
        );

        $country = self::normalizeText(
            $query['pais'] ?? null,
            100
        );

        $grape = self::normalizeText(
            $query['cepa'] ?? null,
            100
        );

        $search = self::normalizeText(
            $query['buscar'] ?? null,
            100
        );

        $maxPrice = self::positiveFloat(
            $query['precio_max'] ?? null
        );

        return new self(
            categoryId: $categoryId,
            wineryId: $wineryId,
            country: $country,
            grape: $grape,
            maxPrice: $maxPrice,
            search: $search
        );
    }

    public function hasFilters(): bool
    {
        return $this->categoryId !== null
            || $this->wineryId !== null
            || $this->country !== null
            || $this->grape !== null
            || $this->maxPrice !== null
            || $this->search !== null;
    }

    private static function positiveInteger(
        mixed $value
    ): ?int {
        if ($value === null || $value === '') {
            return null;
        }

        $validated = filter_var(
            $value,
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => 1,
                ],
            ]
        );

        return $validated === false
            ? null
            : $validated;
    }

    private static function positiveFloat(
        mixed $value
    ): ?float {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = str_replace(
            ',',
            '.',
            trim((string)$value)
        );

        if (!is_numeric($normalized)) {
            return null;
        }

        $number = (float)$normalized;

        return $number > 0
            ? $number
            : null;
    }

    private static function normalizeText(
        mixed $value,
        int $maxLength
    ): ?string {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        return mb_substr(
            $value,
            0,
            $maxLength
        );
    }
}