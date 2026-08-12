<?php

declare(strict_types=1);

namespace App\Repositories\MySQL;

use App\DTOs\ProductFiltersDTO;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use PDO;
use PDOStatement;

final readonly class MySqlProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private PDO $pdo
    ) {}

    /**
     * @return array<Product>
     */
    public function findCatalog(
        ProductFiltersDTO $filters
    ): array {
        $sql = "
            SELECT
                p.id,
                p.nombre,
                p.descripcion,
                p.precio,
                p.stock,
                p.imagen,
                p.destacado,
                p.categoria_id,
                p.bodega_id,
                p.pais,
                p.region,
                p.cepa,
                p.anada,
                c.nombre AS categoria_nombre,
                b.nombre AS bodega_nombre,
                b.pais AS bodega_pais,
                b.region AS bodega_region
            FROM productos p
            LEFT JOIN categorias c
                ON c.id = p.categoria_id
            LEFT JOIN bodegas b
                ON b.id = p.bodega_id
            WHERE 1 = 1
        ";

        $parameters = [];

        if ($filters->country !== null) {
            $sql .= " AND b.pais = :country";
            $parameters[':country'] = $filters->country;
        }

        if ($filters->categoryId !== null) {
            $sql .= " AND p.categoria_id = :category_id";
            $parameters[':category_id'] = $filters->categoryId;
        }

        if ($filters->wineryId !== null) {
            $sql .= " AND p.bodega_id = :winery_id";
            $parameters[':winery_id'] = $filters->wineryId;
        }

        if ($filters->grape !== null) {
            $sql .= " AND p.cepa LIKE :grape";
            $parameters[':grape'] =
                '%' . $filters->grape . '%';
        }

        if ($filters->maxPrice !== null) {
            $sql .= " AND p.precio <= :max_price";
            $parameters[':max_price'] = $filters->maxPrice;
        }

        if ($filters->search !== null) {
            $sql .= "
                AND (
                    p.nombre LIKE :search_name
                    OR p.descripcion LIKE :search_description
                    OR p.cepa LIKE :search_grape
                    OR p.bodega LIKE :search_legacy_winery
                    OR b.nombre LIKE :search_winery
                )
            ";

            $searchValue =
                '%' . $filters->search . '%';

            /*
             * Se utilizan placeholders distintos porque PDO,
             * con prepared statements nativos, no debe reutilizar
             * el mismo placeholder varias veces.
             */
            $parameters[':search_name'] =
                $searchValue;

            $parameters[':search_description'] =
                $searchValue;

            $parameters[':search_grape'] =
                $searchValue;

            $parameters[':search_legacy_winery'] =
                $searchValue;

            $parameters[':search_winery'] =
                $searchValue;
        }

        $sql .= "
            ORDER BY
                p.destacado DESC,
                p.nombre ASC
        ";

        $statement = $this->pdo->prepare($sql);
        $statement->execute($parameters);

        return $this->hydrateMany(
            $statement->fetchAll()
        );
    }

    public function findById(
        int $id
    ): ?Product {
        $sql = "
            SELECT
                p.id,
                p.nombre,
                p.descripcion,
                p.precio,
                p.stock,
                p.imagen,
                p.destacado,
                p.categoria_id,
                p.bodega_id,
                p.pais,
                p.region,
                p.cepa,
                p.anada,
                c.nombre AS categoria_nombre,
                b.nombre AS bodega_nombre,
                b.pais AS bodega_pais,
                b.region AS bodega_region,
                b.descripcion AS bodega_descripcion
            FROM productos p
            LEFT JOIN categorias c
                ON c.id = p.categoria_id
            LEFT JOIN bodegas b
                ON b.id = p.bodega_id
            WHERE p.id = :id
            LIMIT 1
        ";

        $statement = $this->pdo->prepare($sql);

        $statement->bindValue(
            ':id',
            $id,
            PDO::PARAM_INT
        );

        $statement->execute();

        $row = $statement->fetch();

        if (!is_array($row)) {
            return null;
        }

        return $this->hydrate($row);
    }

    /**
     * @return array<Product>
     */
    public function findFeatured(
        int $limit = 3
    ): array {
        $safeLimit = max(
            1,
            min($limit, 50)
        );

        $sql = "
            SELECT
                p.id,
                p.nombre,
                p.descripcion,
                p.precio,
                p.stock,
                p.imagen,
                p.destacado,
                p.categoria_id,
                p.bodega_id,
                p.pais,
                p.region,
                p.cepa,
                p.anada,
                c.nombre AS categoria_nombre,
                b.nombre AS bodega_nombre,
                b.pais AS bodega_pais,
                b.region AS bodega_region
            FROM productos p
            LEFT JOIN categorias c
                ON c.id = p.categoria_id
            LEFT JOIN bodegas b
                ON b.id = p.bodega_id
            WHERE p.destacado = 1
            ORDER BY p.id DESC
            LIMIT :limit
        ";

        $statement = $this->pdo->prepare($sql);

        $statement->bindValue(
            ':limit',
            $safeLimit,
            PDO::PARAM_INT
        );

        $statement->execute();

        return $this->hydrateMany(
            $statement->fetchAll()
        );
    }

    /**
     * @return array<string>
     */
    public function findAvailableCountries(): array
    {
        $sql = "
            SELECT DISTINCT pais
            FROM bodegas
            WHERE pais IS NOT NULL
              AND TRIM(pais) <> ''
            ORDER BY pais ASC
        ";

        $statement = $this->pdo->prepare($sql);
        $statement->execute();

        $countries = $statement->fetchAll(
            PDO::FETCH_COLUMN
        );

        return array_values(
            array_map(
                static fn(mixed $country): string =>
                (string)$country,
                $countries
            )
        );
    }

    /**
     * @return array<int, array{id:int, nombre:string}>
     */
    public function findCategories(): array
    {
        $sql = "
            SELECT
                id,
                nombre
            FROM categorias
            ORDER BY nombre ASC
        ";

        $statement = $this->pdo->prepare($sql);
        $statement->execute();

        return $this->normalizeReferenceList(
            $statement
        );
    }

    /**
     * @return array<int, array{id:int, nombre:string}>
     */
    public function findWineries(): array
    {
        $sql = "
            SELECT
                id,
                nombre
            FROM bodegas
            ORDER BY nombre ASC
        ";

        $statement = $this->pdo->prepare($sql);
        $statement->execute();

        return $this->normalizeReferenceList(
            $statement
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(
        array $row
    ): Product {
        return new Product(
            id: (int)$row['id'],
            name: (string)$row['nombre'],
            description: (string)$row['descripcion'],
            price: (float)$row['precio'],
            stock: (int)$row['stock'],
            image: (string)$row['imagen'],
            featured: (bool)$row['destacado'],

            categoryId: $this->nullableInt(
                $row['categoria_id'] ?? null
            ),

            wineryId: $this->nullableInt(
                $row['bodega_id'] ?? null
            ),

            categoryName: $this->nullableString(
                $row['categoria_nombre'] ?? null
            ),

            wineryName: $this->nullableString(
                $row['bodega_nombre'] ?? null
            ),

            country: $this->nullableString(
                $row['bodega_pais']
                    ?? $row['pais']
                    ?? null
            ),

            region: $this->nullableString(
                $row['bodega_region']
                    ?? $row['region']
                    ?? null
            ),

            grape: $this->nullableString(
                $row['cepa'] ?? null
            ),

            vintage: $this->nullableInt(
                $row['anada'] ?? null
            ),
            wineryDescription: $this->nullableString(
                $row['bodega_descripcion'] ?? null
            )
        );
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     *
     * @return array<Product>
     */
    private function hydrateMany(
        array $rows
    ): array {
        return array_map(
            fn(array $row): Product =>
            $this->hydrate($row),
            $rows
        );
    }

    /**
     * @return array<int, array{id:int, nombre:string}>
     */
    private function normalizeReferenceList(
        PDOStatement $statement
    ): array {
        $rows = $statement->fetchAll();

        return array_map(
            static fn(array $row): array => [
                'id' => (int)$row['id'],
                'nombre' => (string)$row['nombre'],
            ],
            $rows
        );
    }

    private function nullableInt(
        mixed $value
    ): ?int {
        if (
            $value === null ||
            $value === ''
        ) {
            return null;
        }

        return (int)$value;
    }

    private function nullableString(
        mixed $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value = trim((string)$value);

        return $value === ''
            ? null
            : $value;
    }
}
