<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTOs\CreateProductDTO;
use App\DTOs\ProductFiltersDTO;
use App\DTOs\UpdateProductDTO;
use App\Models\Product;
use App\Services\ProductService;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final readonly class ProductController
{
    public function __construct(
        private ProductService $productService
    ) {
    }

    /**
     * Catálogo público.
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
    public function catalog(
        array $query
    ): array {
        try {
            $filters = ProductFiltersDTO::fromQuery(
                $query
            );

            $catalogData = $this->productService
                ->getCatalogData($filters);

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
     * Detalle público.
     *
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
        $id = $this->positiveInteger(
            $rawId
        );

        if ($id === null) {
            return [
                'product' => null,
                'discount' => 0.0,
                'finalPrice' => 0.0,
                'error' =>
                    'El producto solicitado no es válido.',
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

    /**
     * Listado administrativo.
     *
     * @return array{
     *     products: array<Product>,
     *     error: string|null
     * }
     */
    public function adminIndex(): array
    {
        try {
            $data = $this->productService
                ->getCatalogData(
                    new ProductFiltersDTO()
                );

            return [
                'products' => $data['products'],
                'error' => null,
            ];
        } catch (Throwable $exception) {
            error_log(
                '[Cava Noble] Error listando productos en admin: ' .
                $exception->getMessage()
            );

            return [
                'products' => [],
                'error' =>
                    'No se pudieron cargar los productos.',
            ];
        }
    }

    /**
     * Datos del formulario de creación.
     *
     * @return array{
     *     categories: array<int, array{id:int, nombre:string}>,
     *     wineries: array<int, array{id:int, nombre:string}>,
     *     error: string|null
     * }
     */
    public function createForm(): array
    {
        try {
            $data = $this->productService
                ->getAdminFormData();

            return [
                ...$data,
                'error' => null,
            ];
        } catch (Throwable $exception) {
            error_log(
                '[Cava Noble] Error cargando formulario ' .
                'de creación de producto: ' .
                $exception->getMessage()
            );

            return [
                'categories' => [],
                'wineries' => [],
                'error' =>
                    'No se pudieron cargar los datos del formulario.',
            ];
        }
    }

    /**
     * Crear producto.
     *
     * @param array<string, mixed> $post
     * @param array<string, mixed> $files
     *
     * @return array{
     *     success: bool,
     *     productId: int|null,
     *     error: string|null
     * }
     */
    public function store(
        array $post,
        array $files,
        int $adminId
    ): array {
        try {
            $data = CreateProductDTO::fromArray(
                $post
            );

            $image = $files['imagen'] ?? [];

            if (!is_array($image)) {
                $image = [];
            }

            $productId = $this->productService
                ->createProduct(
                    data: $data,
                    imageFile: $image,
                    adminId: $adminId
                );

            return [
                'success' => true,
                'productId' => $productId,
                'error' => null,
            ];
        } catch (
            InvalidArgumentException |
            RuntimeException $exception
        ) {
            return [
                'success' => false,
                'productId' => null,
                'error' => $exception->getMessage(),
            ];
        } catch (Throwable $exception) {
            error_log(
                '[Cava Noble] Error inesperado creando producto: ' .
                $exception->getMessage()
            );

            return [
                'success' => false,
                'productId' => null,
                'error' =>
                    'No se pudo crear el producto.',
            ];
        }
    }

    /**
     * Datos del formulario de edición.
     *
     * @return array{
     *     product: Product|null,
     *     categories: array<int, array{id:int, nombre:string}>,
     *     wineries: array<int, array{id:int, nombre:string}>,
     *     error: string|null
     * }
     */
    public function editForm(
        mixed $rawId
    ): array {
        $id = $this->positiveInteger(
            $rawId
        );

        if ($id === null) {
            return [
                'product' => null,
                'categories' => [],
                'wineries' => [],
                'error' =>
                    'El identificador del producto no es válido.',
            ];
        }

        try {
            $product = $this->productService
                ->getProductById($id);

            if (!$product instanceof Product) {
                return [
                    'product' => null,
                    'categories' => [],
                    'wineries' => [],
                    'error' =>
                        'El producto solicitado no existe.',
                ];
            }

            $formData = $this->productService
                ->getAdminFormData();

            return [
                'product' => $product,
                'categories' =>
                    $formData['categories'],
                'wineries' =>
                    $formData['wineries'],
                'error' => null,
            ];
        } catch (Throwable $exception) {
            error_log(
                '[Cava Noble] Error cargando edición ' .
                'del producto #' . $id . ': ' .
                $exception->getMessage()
            );

            return [
                'product' => null,
                'categories' => [],
                'wineries' => [],
                'error' =>
                    'No se pudo cargar el producto.',
            ];
        }
    }

    /**
     * Actualizar producto.
     *
     * @param array<string, mixed> $post
     * @param array<string, mixed> $files
     *
     * @return array{
     *     success: bool,
     *     error: string|null
     * }
     */
    public function update(
        array $post,
        array $files,
        int $adminId
    ): array {
        try {
            $data = UpdateProductDTO::fromArray(
                $post
            );

            $image = $files['imagen'] ?? [
                'error' => UPLOAD_ERR_NO_FILE,
            ];

            if (!is_array($image)) {
                $image = [
                    'error' => UPLOAD_ERR_NO_FILE,
                ];
            }

            $this->productService->updateProduct(
                data: $data,
                imageFile: $image,
                adminId: $adminId
            );

            return [
                'success' => true,
                'error' => null,
            ];
        } catch (
            InvalidArgumentException |
            RuntimeException $exception
        ) {
            return [
                'success' => false,
                'error' => $exception->getMessage(),
            ];
        } catch (Throwable $exception) {
            error_log(
                '[Cava Noble] Error inesperado editando producto: ' .
                $exception->getMessage()
            );

            return [
                'success' => false,
                'error' =>
                    'No se pudo actualizar el producto.',
            ];
        }
    }

    /**
     * Confirmación de eliminación.
     *
     * @return array{
     *     product: Product|null,
     *     error: string|null
     * }
     */
    public function deleteConfirmation(
        mixed $rawId
    ): array {
        $id = $this->positiveInteger(
            $rawId
        );

        if ($id === null) {
            return [
                'product' => null,
                'error' =>
                    'El identificador del producto no es válido.',
            ];
        }

        try {
            $product = $this->productService
                ->getProductById($id);

            if (!$product instanceof Product) {
                return [
                    'product' => null,
                    'error' =>
                        'El producto solicitado no existe.',
                ];
            }

            return [
                'product' => $product,
                'error' => null,
            ];
        } catch (Throwable $exception) {
            error_log(
                '[Cava Noble] Error cargando eliminación ' .
                'del producto #' . $id . ': ' .
                $exception->getMessage()
            );

            return [
                'product' => null,
                'error' =>
                    'No se pudo cargar el producto.',
            ];
        }
    }

    /**
     * Eliminar producto.
     *
     * @return array{
     *     success: bool,
     *     error: string|null
     * }
     */
    public function destroy(
        mixed $rawId,
        int $adminId
    ): array {
        $id = $this->positiveInteger(
            $rawId
        );

        if ($id === null) {
            return [
                'success' => false,
                'error' =>
                    'El identificador del producto no es válido.',
            ];
        }

        try {
            $this->productService
                ->deleteProduct(
                    productId: $id,
                    adminId: $adminId
                );

            return [
                'success' => true,
                'error' => null,
            ];
        } catch (
            InvalidArgumentException |
            RuntimeException $exception
        ) {
            return [
                'success' => false,
                'error' => $exception->getMessage(),
            ];
        } catch (Throwable $exception) {
            error_log(
                '[Cava Noble] Error inesperado eliminando ' .
                'producto #' . $id . ': ' .
                $exception->getMessage()
            );

            return [
                'success' => false,
                'error' =>
                    'No se pudo eliminar el producto.',
            ];
        }
    }

    private function positiveInteger(
        mixed $value
    ): ?int {
        $validated = filter_var(
            $value,
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => 1,
                ],
            ]
        );

        return $validated === false ||
            $validated === null
                ? null
                : (int)$validated;
    }
}