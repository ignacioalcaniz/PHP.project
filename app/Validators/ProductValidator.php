<?php

declare(strict_types=1);

namespace App\Validators;

use App\DTOs\CreateProductDTO;
use App\DTOs\UpdateProductDTO;
use InvalidArgumentException;

final class ProductValidator
{
    public function validateCreate(
        CreateProductDTO $data
    ): void {
        $errors = $this->validateCommon(
            name: $data->name,
            description: $data->description,
            price: $data->price,
            categoryId: $data->categoryId,
            wineryId: $data->wineryId,
            grape: $data->grape,
            vintage: $data->vintage,
            stock: $data->stock
        );

        $this->throwIfInvalid($errors);
    }

    public function validateUpdate(
        UpdateProductDTO $data
    ): void {
        $errors = [];

        if ($data->id <= 0) {
            $errors[] =
                'El identificador del producto no es válido.';
        }

        $errors = [
            ...$errors,
            ...$this->validateCommon(
                name: $data->name,
                description: $data->description,
                price: $data->price,
                categoryId: $data->categoryId,
                wineryId: $data->wineryId,
                grape: $data->grape,
                vintage: $data->vintage,
                stock: $data->stock
            ),
        ];

        if ($data->currentImage === '') {
            $errors[] =
                'La imagen actual del producto no es válida.';
        }

        $this->throwIfInvalid($errors);
    }

    /**
     * @return array<string>
     */
    private function validateCommon(
        string $name,
        string $description,
        float $price,
        int $categoryId,
        int $wineryId,
        string $grape,
        int $vintage,
        int $stock
    ): array {
        $errors = [];

        if ($name === '') {
            $errors[] =
                'El nombre del producto es obligatorio.';
        }

        if (mb_strlen($name) > 150) {
            $errors[] =
                'El nombre no puede superar los 150 caracteres.';
        }

        if ($description === '') {
            $errors[] =
                'La descripción es obligatoria.';
        }

        if ($price <= 0) {
            $errors[] =
                'El precio debe ser mayor que cero.';
        }

        if ($categoryId <= 0) {
            $errors[] =
                'La categoría es obligatoria.';
        }

        if ($wineryId <= 0) {
            $errors[] =
                'La bodega es obligatoria.';
        }

        if ($grape === '') {
            $errors[] =
                'La cepa es obligatoria.';
        }

        if (mb_strlen($grape) > 100) {
            $errors[] =
                'La cepa no puede superar los 100 caracteres.';
        }

        $currentYear = (int)date('Y');

        if (
            $vintage < 1900 ||
            $vintage > $currentYear + 1
        ) {
            $errors[] =
                'La añada no es válida.';
        }

        if ($stock < 0) {
            $errors[] =
                'El stock no puede ser negativo.';
        }

        return $errors;
    }

    /**
     * @param array<string> $errors
     */
    private function throwIfInvalid(
        array $errors
    ): void {
        if ($errors === []) {
            return;
        }

        throw new InvalidArgumentException(
            implode(' ', $errors)
        );
    }
}