<?php

declare(strict_types=1);

namespace MaxServ\App\Repository;

use MaxServ\App\Dto\Pagination\PaginatedResult;
use MaxServ\App\Dto\Pagination\Paginator;
use MaxServ\App\Entity\Product;
use MaxServ\App\Filter\ProductFilter;
use MaxServ\App\Hydrator\ProductHydrator;
use MaxServ\App\Utility\QueryUtility;
use MaxServ\Core\Database\Connection;
use PDO;

final readonly class ProductRepository
{
    public function __construct(
        private Connection $connection,
        private MediaRepository $mediaRepository,
        private CategoryRepository $categoryRepository,
        private BrandRepository $brandRepository,
        private ProductHydrator $hydrator,
    ) {}

    public function save(Product $product): void
    {
        $sql = '
            INSERT INTO products
                (id, title, description, price, discount_percentage, rating, stock, category_id, brand_id)
            VALUES
                (:id, :title, :description, :price, :discountPercentage, :rating, :stock, :categoryId, :brandId)
            AS new_row
            ON DUPLICATE KEY UPDATE
                title               = new_row.title,
                description         = new_row.description,
                price               = new_row.price,
                discount_percentage = new_row.discount_percentage,
                rating              = new_row.rating,
                stock               = new_row.stock,
                category_id         = new_row.category_id,
                brand_id            = new_row.brand_id
        ';

        $this->connection->pdo->prepare(query: $sql)->execute(params: [
            ':id' => $product->id,
            ':title' => $product->title,
            ':description' => $product->description,
            ':price' => $product->price,
            ':discountPercentage' => $product->discountPercentage,
            ':rating' => $product->rating,
            ':stock' => $product->stock,
            ':categoryId' => $product->category->id,
            ':brandId' => $product->brand?->id,
        ]);

        $product->id ??= (int)$this->connection->pdo->lastInsertId();
    }

    public function saveMany(array $products): void
    {
        if (empty($products)) {
            return;
        }

        $placeholders = QueryUtility::buildPositionalPlaceholders(
            rowCount: count($products),
            columnCount: 9,
        );

        $sql = "
            INSERT INTO products
                (id, title, description, price, discount_percentage, rating, stock, category_id, brand_id)
            VALUES
                $placeholders
            AS new_row
            ON DUPLICATE KEY UPDATE
                title               = new_row.title,
                description         = new_row.description,
                price               = new_row.price,
                discount_percentage = new_row.discount_percentage,
                rating              = new_row.rating,
                stock               = new_row.stock,
                category_id         = new_row.category_id,
                brand_id            = new_row.brand_id
        ";

        $values = [];
        foreach ($products as $product) {
            array_push(
                $values,
                $product->id,
                $product->title,
                $product->description,
                $product->price,
                $product->discountPercentage,
                $product->rating,
                $product->stock,
                $product->category->id,
                $product->brand?->id,
            );
        }

        $this->connection->pdo->prepare(query: $sql)->execute(params: $values);
    }

    public function saveManyMedia(array $products): void
    {
        if (empty($products)) {
            return;
        }

        $entityIds = array_map(callback: fn(Product $p) => $p->id, array: $products);
        $media = array_merge(
            ...array_map(
            callback: fn(Product $p) => $p->media,
            array: $products,
        ),
        );

        $this->mediaRepository->deleteByEntities(entityType: 'product', entityIds: $entityIds);
        $this->mediaRepository->saveMany(media: $media);
    }

    public function findManyMedia(array $productIds): array
    {
        return $this->mediaRepository->findFirstByEntities(
            entityType: 'product',
            entityIds: $productIds,
        );
    }

    public function searchPaginated(
        ProductFilter $filter,
        int|string|null $page,
        int|string|null $perPage,
    ): PaginatedResult {
        $countStmt = $this->connection->pdo->prepare(
            query: "SELECT COUNT(*) FROM products WHERE {$filter->filters}",
        );
        $countStmt->execute(params: $filter->bindings);

        $paginator = new Paginator(
            page: $page,
            perPage: $perPage,
            total: (int)$countStmt->fetchColumn(),
        );

        $stmt = $this->connection->pdo->prepare(
            query: "
              SELECT products.* FROM products
              {$filter->sortJoins}
              WHERE {$filter->filters}
              ORDER BY {$filter->sorts}
              LIMIT {$paginator->perPage} OFFSET {$paginator->offset}
            ",
        );
        $stmt->execute(params: $filter->bindings);

        $rows = $stmt->fetchAll(mode: PDO::FETCH_ASSOC);

        if (empty($rows)) {
            return new PaginatedResult(items: [], paginator: $paginator);
        }

        $productIds = [];
        $categoryIds = [];
        $brandIds = [];

        foreach ($rows as $row) {
            $productIds[] = (int)$row['id'];

            $categoryId = (int)$row['category_id'];
            if (!in_array($categoryId, $categoryIds)) {
                $categoryIds[] = $categoryId;
            }

            if ($row['brand_id'] !== null) {
                $brandId = (int)$row['brand_id'];
                if (!in_array($brandId, $brandIds)) {
                    $brandIds[] = $brandId;
                }
            }
        }

        $categoriesById = array_column(
            array: $this->categoryRepository->findMany($categoryIds),
            column_key: null,
            index_key: 'id',
        );
        $brandsById = array_column(
            array: $this->brandRepository->findMany($brandIds),
            column_key: null,
            index_key: 'id',
        );
        $mediaByProductId = array_column(
            array: $this->findManyMedia($productIds),
            column_key: null,
            index_key: 'entityId',
        );

        $items = [];
        foreach ($rows as $row) {
            $items[] = $this->hydrator->hydrateFromDatabase(
                row: $row,
                category: $categoriesById[(int)$row['category_id']],
                brand: isset($row['brand_id'])
                    ? ($brandsById[(int)$row['brand_id']] ?? null)
                    : null,
                media: isset($mediaByProductId[(int)$row['id']])
                    ? [$mediaByProductId[(int)$row['id']]]
                    : [],
            );
        }

        return new PaginatedResult(items: $items, paginator: $paginator);
    }

    public function find(int $id): ?Product
    {
        $stmt = $this->connection->pdo->prepare(
            query: 'SELECT * FROM products WHERE id = :id',
        );
        $stmt->execute(params: [':id' => $id]);
        $row = $stmt->fetch(mode: PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        $category = $this->categoryRepository->find(id: (int)$row['category_id']);
        $brand = isset($row['brand_id'])
            ? $this->brandRepository->find(id: (int)$row['brand_id'])
            : null;

        $media = $this->mediaRepository->findByEntity(entityType: 'product', entityId: $id);

        return $this->hydrator->hydrateFromDatabase(
            row: $row,
            category: $category,
            brand: $brand,
            media: $media,
        );
    }
}