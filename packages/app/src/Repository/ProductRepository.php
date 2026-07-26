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

class ProductRepository
{
    public function __construct(
        private readonly Connection $connection,
        private readonly MediaRepository $mediaRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly BrandRepository $brandRepository,
        private readonly ProductHydrator $hydrator,
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

        $this->connection->pdo->prepare($sql)->execute([
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

        $placeholders = QueryUtility::buildPositionalPlaceholders(count($products), 9);
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

        $this->connection->pdo->prepare($sql)->execute($values);
    }

    public function saveManyMedia(array $products): void
    {
        if (empty($products)) {
            return;
        }

        $entityIds = array_map(callback: fn(Product $p) => $p->id, array: $products);
        $media = array_merge(...array_map(callback: fn(Product $p) => $p->media, array: $products));

        $this->mediaRepository->deleteByEntities(entityType: 'product', entityIds: $entityIds);
        $this->mediaRepository->saveMany(media: $media);
    }

    public function findManyMedia(array $productIds): array
    {
        return $this->mediaRepository->findFirstByEntities('product', $productIds);
    }

    public function searchPaginated(ProductFilter $filter, int|string|null $page, int|string|null $perPage): PaginatedResult
    {
        $countStmt = $this->connection->pdo->prepare("SELECT COUNT(*) FROM products WHERE {$filter->filters}");
        $countStmt->execute($filter->bindings);
        $paginator = new Paginator(page: $page, perPage: $perPage, total: (int) $countStmt->fetchColumn());

        $stmt = $this->connection->pdo->prepare(
            "SELECT products.* FROM products
             WHERE {$filter->filters}
             ORDER BY {$filter->sorts}
             LIMIT {$paginator->perPage} OFFSET {$paginator->offset}"
        );
        $stmt->execute($filter->bindings);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            return new PaginatedResult(items: [], paginator: $paginator);
        }

        $productIds = [];
        $categoryIds = [];
        $brandIds = [];

        foreach ($rows as $row) {
            $productIds[] = (int) $row['id'];

            $categoryId = (int) $row['category_id'];
            if (!in_array($categoryId, $categoryIds)) {
                $categoryIds[] = $categoryId;
            }

            if ($row['brand_id'] !== null) {
                $brandId = (int) $row['brand_id'];
                if (!in_array($brandId, $brandIds)) {
                    $brandIds[] = $brandId;
                }
            }
        }

        $categoriesById = array_column($this->categoryRepository->findMany($categoryIds), null, 'id');
        $brandsById = array_column($this->brandRepository->findMany($brandIds), null, 'id');
        $mediaByProductId = array_column($this->findManyMedia($productIds), null, 'entityId');

        $items = [];
        foreach ($rows as $row) {
            $items[] = $this->hydrator->hydrateFromDatabase(
                row: $row,
                category: $categoriesById[(int) $row['category_id']],
                brand: isset($row['brand_id']) ? ($brandsById[(int) $row['brand_id']] ?? null) : null,
                media: isset($mediaByProductId[(int) $row['id']]) ? [$mediaByProductId[(int) $row['id']]] : [],
            );
        }

        return new PaginatedResult(items: $items, paginator: $paginator);
    }

    public function find(int $id): ?Product
    {
        $stmt = $this->connection->pdo->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        $category = $this->categoryRepository->findMany([(int)$row['category_id']])[0];
        $brand = isset($row['brand_id'])
            ? ($this->brandRepository->findMany([(int)$row['brand_id']])[0] ?? null)
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
