<?php

declare(strict_types=1);

namespace App\Models;

use InvalidArgumentException;
use LogicException;

final class Product
{
    public function __construct(
        private int $id,
        private string $name,
        private string $description,
        private float $price,
        private int $stock,
        private string $image,
        private bool $featured,
        private ?int $categoryId = null,
        private ?int $wineryId = null,
        private ?string $categoryName = null,
        private ?string $wineryName = null,
        private ?string $country = null,
        private ?string $region = null,
        private ?string $grape = null,
        private ?int $vintage = null,
        private ?string $wineryDescription = null
    ) {
        $this->validateState();
    }

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function price(): float
    {
        return $this->price;
    }

    public function stock(): int
    {
        return $this->stock;
    }

    public function image(): string
    {
        return $this->image;
    }

    public function isFeatured(): bool
    {
        return $this->featured;
    }

    public function categoryId(): ?int
    {
        return $this->categoryId;
    }

    public function wineryId(): ?int
    {
        return $this->wineryId;
    }

    public function categoryName(): ?string
    {
        return $this->categoryName;
    }

    public function wineryName(): ?string
    {
        return $this->wineryName;
    }

    public function country(): ?string
    {
        return $this->country;
    }

    public function region(): ?string
    {
        return $this->region;
    }

    public function grape(): ?string
    {
        return $this->grape;
    }

    public function vintage(): ?int
    {
        return $this->vintage;
    }

    public function isAvailable(): bool
    {
        return $this->stock > 0;
    }

    public function hasStock(int $quantity): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        return $this->stock >= $quantity;
    }

    public function decreaseStock(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException(
                'La cantidad a descontar debe ser mayor que cero.'
            );
        }

        if (!$this->hasStock($quantity)) {
            throw new LogicException(
                'No existe stock suficiente para realizar la operación.'
            );
        }

        $this->stock -= $quantity;
    }

    public function increaseStock(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException(
                'La cantidad a agregar debe ser mayor que cero.'
            );
        }

        $this->stock += $quantity;
    }

    public function changePrice(float $newPrice): void
    {
        if ($newPrice <= 0) {
            throw new InvalidArgumentException(
                'El precio debe ser mayor que cero.'
            );
        }

        $this->price = round($newPrice, 2);
    }

    public function markAsFeatured(): void
    {
        $this->featured = true;
    }

    public function removeFromFeatured(): void
    {
        $this->featured = false;
    }

    public function wineryDescription(): ?string
    {
        return $this->wineryDescription;
    }

    private function validateState(): void
    {
        $this->name = trim($this->name);
        $this->description = trim($this->description);
        $this->image = trim($this->image);

        if ($this->id < 0) {
            throw new InvalidArgumentException(
                'El ID del producto no puede ser negativo.'
            );
        }

        if ($this->name === '') {
            throw new InvalidArgumentException(
                'El nombre del producto es obligatorio.'
            );
        }

        if ($this->description === '') {
            throw new InvalidArgumentException(
                'La descripción del producto es obligatoria.'
            );
        }

        if ($this->price <= 0) {
            throw new InvalidArgumentException(
                'El precio debe ser mayor que cero.'
            );
        }

        if ($this->stock < 0) {
            throw new InvalidArgumentException(
                'El stock no puede ser negativo.'
            );
        }

        if ($this->vintage !== null) {
            $currentYear = (int)date('Y');

            if (
                $this->vintage < 1900 ||
                $this->vintage > $currentYear + 1
            ) {
                throw new InvalidArgumentException(
                    'La añada del producto no es válida.'
                );
            }
        }
    }
}
