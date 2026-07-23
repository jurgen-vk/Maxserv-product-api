<?php

declare(strict_types=1);

namespace MaxServ\App\Repository;

use MaxServ\App\Dto\Pagination\PaginatedResult;
use MaxServ\App\Dto\Pagination\Paginator;
use MaxServ\App\Entity\Media;
use MaxServ\App\Entity\Product;
use MaxServ\App\Enum\MediaType;
use MaxServ\App\Filter\ProductFilter;
use MaxServ\App\Hydrator\ProductHydrator;
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
  ) {
  }

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
      ':id'                 => $product->id,
      ':title'              => $product->title,
      ':description'        => $product->description,
      ':price'              => $product->price,
      ':discountPercentage' => $product->discountPercentage,
      ':rating'             => $product->rating,
      ':stock'              => $product->stock,
      ':categoryId'         => $product->category->id,
      ':brandId'            => $product->brand?->id,
    ]);

    $product->id ??= (int) $this->connection->pdo->lastInsertId();
  }

  public function saveMany(array $products): void
  {
    if (empty($products)) {
      return;
    }

    $placeholders = implode(
      separator: ', ',
      array: array_fill(start_index: 0, count: count($products), value: '(?, ?, ?, ?, ?, ?, ?, ?, ?)')
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

  public function searchPaginated(ProductFilter $filter, Paginator $paginator): PaginatedResult
  {
    $countStmt = $this->connection->pdo->prepare(
      "SELECT COUNT(*) FROM products {$filter->whereClause}"
    );
    $countStmt->execute($filter->bindings);
    $paginator->total = (int) $countStmt->fetchColumn();

    $stmt = $this->connection->pdo->prepare(
      "SELECT products.*,
        (SELECT url FROM media
         WHERE entity_type = 'product' AND entity_id = products.id
         ORDER BY sort_order LIMIT 1) AS thumbnail
       FROM products
       {$filter->whereClause}
       ORDER BY {$filter->orderBy}
       LIMIT :limit OFFSET :offset"
    );

    foreach ($filter->bindings as $key => $value) {
      $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $paginator->perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $paginator->offset, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rows)) {
      return new PaginatedResult(items: [], paginator: $paginator);
    }

    $categoryIds = array_values(array_map('intval', array_unique(array_column($rows, 'category_id'))));
    $brandIds = array_values(array_map('intval', array_filter(array_unique(array_column($rows, 'brand_id')))));

    $categories = $this->categoryRepository->findMany($categoryIds);
    $brands = $this->brandRepository->findMany($brandIds);

    $items = array_map(
      fn(array $row) => $this->hydrator->hydrateFromDatabase(
        row: $row,
        category: $categories[(int) $row['category_id']],
        brand: isset($row['brand_id']) ? ($brands[(int) $row['brand_id']] ?? null) : null,
        media: $row['thumbnail'] !== null ? [new Media(
          id: null,
          entityType: 'product',
          entityId: (int) $row['id'],
          url: $row['thumbnail'],
          mediaType: MediaType::Image,
          sortOrder: 0,
        )] : [],
      ),
      $rows
    );

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

    $categories = $this->categoryRepository->findMany([(int) $row['category_id']]);
    $brands = isset($row['brand_id'])
      ? $this->brandRepository->findMany([(int) $row['brand_id']])
      : [];

    $media = $this->mediaRepository->findByEntity(entityType: 'product', entityId: $id);

    return $this->hydrator->hydrateFromDatabase(
      row: $row,
      category: $categories[(int) $row['category_id']],
      brand: isset($row['brand_id']) ? ($brands[(int) $row['brand_id']] ?? null) : null,
      media: $media,
    );
  }
}
