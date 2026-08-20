<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\CreateProductDTO;
use App\DTOs\ProductFiltersDTO;
use App\DTOs\UpdateProductDTO;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Validators\ProductValidator;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductValidator $productValidator,
        private readonly ProductImageService $productImageService,
        private readonly AuditService $auditService
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

    public function productExists(
        int $id
    ): bool {
        if ($id <= 0) {
            return false;
        }

        return $this->productRepository
            ->findById($id) instanceof Product;
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
     * Información necesaria para formularios administrativos.
     *
     * @return array{
     *     categories: array<int, array{id:int, nombre:string}>,
     *     wineries: array<int, array{id:int, nombre:string}>
     * }
     */
    public function getAdminFormData(): array
    {
        return [
            'categories' => $this->productRepository
                ->findCategories(),

            'wineries' => $this->productRepository
                ->findWineries(),
        ];
    }

    /**
     * Crea un producto y registra la operación administrativa.
     *
     * Si la imagen se guarda correctamente pero posteriormente
     * falla la persistencia del producto, la imagen subida se
     * elimina para evitar archivos huérfanos.
     *
     * @param array<string, mixed> $imageFile
     */
    public function createProduct(
        CreateProductDTO $data,
        array $imageFile,
        int $adminId
    ): int {
        if ($adminId <= 0) {
            throw new InvalidArgumentException(
                'El administrador no es válido.'
            );
        }

        $this->productValidator
            ->validateCreate($data);

        $winery = $this->productRepository
            ->findWineryById($data->wineryId);

        if ($winery === null) {
            throw new InvalidArgumentException(
                'La bodega seleccionada no existe.'
            );
        }

        $imagePath = null;

        try {
            $imagePath = $this->productImageService
                ->upload($imageFile);

            $productId = $this->productRepository->create(
                data: $data,
                imagePath: $imagePath,
                winery: $winery
            );

            $this->auditService->log(
                adminId: $adminId,
                action: 'CREAR',
                entity: 'PRODUCTO',
                entityId: $productId,
                description: 'Producto creado: ' . $data->name
            );

            return $productId;
        } catch (Throwable $exception) {
            /*
             * Si la imagen llegó a guardarse pero la creación falla
             * posteriormente, intentamos eliminarla para no dejar
             * archivos sin referencia.
             */
            if ($imagePath !== null) {
                try {
                    $this->productImageService
                        ->delete($imagePath);
                } catch (Throwable $cleanupException) {
                    error_log(
                        '[Cava Noble] No se pudo limpiar la imagen ' .
                        'después de fallar la creación del producto: ' .
                        $cleanupException->getMessage()
                    );
                }
            }

            throw $exception;
        }
    }

    /**
     * Actualiza un producto existente.
     *
     * @param array<string, mixed> $imageFile
     */
    public function updateProduct(
        UpdateProductDTO $data,
        array $imageFile,
        int $adminId
    ): void {
        if ($adminId <= 0) {
            throw new InvalidArgumentException(
                'El administrador no es válido.'
            );
        }

        $this->productValidator
            ->validateUpdate($data);

        $existingProduct = $this->productRepository
            ->findById($data->id);

        if (!$existingProduct instanceof Product) {
            throw new InvalidArgumentException(
                'El producto que intentás editar no existe.'
            );
        }

        /*
         * No confiamos en imagen_actual enviada por POST.
         * La fuente de verdad es la base de datos.
         */
        $currentImage = $existingProduct->image();

        $winery = $this->productRepository
            ->findWineryById($data->wineryId);

        if ($winery === null) {
            throw new InvalidArgumentException(
                'La bodega seleccionada no existe.'
            );
        }

        $newImagePath = null;
        $finalImagePath = $currentImage;
        $imageChanged = false;

        try {
            if (
                $this->productImageService
                    ->hasNewUpload($imageFile)
            ) {
                $newImagePath = $this->productImageService
                    ->upload($imageFile);

                $finalImagePath = $newImagePath;
                $imageChanged = true;
            }

            $this->productRepository->update(
                data: $data,
                imagePath: $finalImagePath,
                winery: $winery
            );

            /*
             * La imagen anterior se elimina únicamente después
             * de confirmar que MySQL guardó correctamente
             * la nueva ruta.
             */
            if (
                $imageChanged &&
                $newImagePath !== null &&
                $currentImage !== $newImagePath
            ) {
                try {
                    $this->productImageService
                        ->delete($currentImage);
                } catch (Throwable $cleanupException) {
                    /*
                     * La actualización de DB ya fue exitosa.
                     * Un fallo limpiando el archivo viejo no debe
                     * invalidar toda la operación.
                     */
                    error_log(
                        '[Cava Noble] No se pudo eliminar la imagen ' .
                        'anterior del producto #' .
                        $data->id .
                        ': ' .
                        $cleanupException->getMessage()
                    );
                }
            }

            $description =
                'Producto editado: ' . $data->name;

            if ($imageChanged) {
                $description .= ' | Imagen actualizada';
            }

            $this->auditService->log(
                adminId: $adminId,
                action: 'EDITAR',
                entity: 'PRODUCTO',
                entityId: $data->id,
                description: $description
            );
        } catch (Throwable $exception) {
            /*
             * Si subimos una nueva imagen pero posteriormente
             * falla MySQL, eliminamos únicamente la nueva.
             *
             * La imagen anterior permanece intacta y continúa
             * siendo la referenciada por la base de datos.
             */
            if ($newImagePath !== null) {
                try {
                    $this->productImageService
                        ->delete($newImagePath);
                } catch (Throwable $cleanupException) {
                    error_log(
                        '[Cava Noble] No se pudo limpiar la nueva imagen ' .
                        'después de fallar la edición del producto #' .
                        $data->id .
                        ': ' .
                        $cleanupException->getMessage()
                    );
                }
            }

            throw $exception;
        }
    }

    /**
     * Elimina un producto siempre que no tenga ventas asociadas.
     */
    public function deleteProduct(
        int $productId,
        int $adminId
    ): void {
        if ($productId <= 0) {
            throw new InvalidArgumentException(
                'El identificador del producto no es válido.'
            );
        }

        if ($adminId <= 0) {
            throw new InvalidArgumentException(
                'El administrador no es válido.'
            );
        }

        $product = $this->productRepository
            ->findById($productId);

        if (!$product instanceof Product) {
            throw new InvalidArgumentException(
                'El producto que intentás eliminar no existe.'
            );
        }

        /*
         * Preservamos el historial de ventas.
         * Un producto que forma parte de un pedido no debe
         * eliminarse físicamente.
         */
        if (
            $this->productRepository
                ->hasOrderItems($productId)
        ) {
            throw new RuntimeException(
                'Este producto tiene pedidos asociados y no puede eliminarse.'
            );
        }

        $imagePath = $product->image();

        /*
         * Primero eliminamos el registro de la base de datos.
         * La integridad relacional ya fue comprobada arriba.
         */
        $this->productRepository
            ->delete($productId);

        /*
         * Después intentamos limpiar la imagen.
         *
         * Si falla la eliminación física del archivo, el producto
         * igualmente permanece correctamente eliminado de MySQL.
         */
        try {
            $this->productImageService
                ->delete($imagePath);
        } catch (Throwable $cleanupException) {
            error_log(
                '[Cava Noble] Producto #' .
                $productId .
                ' eliminado, pero no se pudo borrar su imagen: ' .
                $cleanupException->getMessage()
            );
        }

        $this->auditService->log(
            adminId: $adminId,
            action: 'ELIMINAR',
            entity: 'PRODUCTO',
            entityId: $productId,
            description: 'Producto eliminado: ' . $product->name()
        );
    }
}