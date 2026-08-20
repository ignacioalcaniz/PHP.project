<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class UpdateProductDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $description,
        public float $price,
        public int $categoryId,
        public int $wineryId,
        public string $grape,
        public int $vintage,
        public int $stock,
        public bool $featured,
        public string $currentImage
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int)($data['id'] ?? 0),

            name: trim((string)($data['nombre'] ?? '')),

            description: trim(
                (string)($data['descripcion'] ?? '')
            ),

            price: self::toFloat(
                $data['precio'] ?? null
            ),

            categoryId: (int)($data['categoria_id'] ?? 0),

            wineryId: (int)($data['bodega_id'] ?? 0),

            grape: trim((string)($data['cepa'] ?? '')),

            vintage: (int)($data['anada'] ?? 0),

            stock: (int)($data['stock'] ?? -1),

            featured: (int)($data['destacado'] ?? 0) === 1,

            currentImage: trim(
                (string)($data['imagen_actual'] ?? '')
            )
        );
    }

    private static function toFloat(
        mixed $value
    ): float {
        if ($value === null || $value === '') {
            return 0.0;
        }

        $normalized = str_replace(
            ',',
            '.',
            trim((string)$value)
        );

        return is_numeric($normalized)
            ? (float)$normalized
            : 0.0;
    }
}