<?php

declare(strict_types=1);

namespace MaxServ\App\Filter;

use Symfony\Component\HttpFoundation\Request;

class ProductFilter
{
  private const SORT_COLUMNS = [
    'title'  => 'products.title',
    'price'  => 'products.price',
    'rating' => 'products.rating',
    'stock'  => 'products.stock',
  ];

  private array $conditions = [];
  private array $params = [];
  private string $resolvedOrderBy = 'products.title ASC';

  public string $whereClause {
    get => $this->conditions ? 'WHERE ' . implode(' AND ', $this->conditions) : '';
  }

  public array $bindings {
    get => $this->params;
  }

  public string $orderBy {
    get => $this->resolvedOrderBy;
  }

  private function __construct(
    public readonly ?string $category = null,
    public readonly ?string $brand = null,
    public readonly ?string $search = null,
    public readonly string $sortBy = 'title',
    public readonly string $order = 'ASC',
    public readonly ?float $minPrice = null,
    public readonly ?float $maxPrice = null,
    public readonly ?float $minRating = null,
  ) {
    $this->applyFilters()
      ->applyRanges()
      ->applySearch()
      ->applySort();
  }

  public static function fromRequest(Request $request): self
  {
    return new self(
      category: $request->query->getString('category') ?: null,
      brand: $request->query->getString('brand') ?: null,
      search: $request->query->getString('search') ?: null,
      sortBy: $request->query->getString('sort', 'title'),
      order: $request->query->getString('order', 'ASC'),
      minPrice: $request->query->getString('minPrice') !== ''
        ? $request->query->getFloat('minPrice')
        : null,
      maxPrice: $request->query->getString('maxPrice') !== ''
        ? $request->query->getFloat('maxPrice')
        : null,
      minRating: $request->query->getString('minRating') !== ''
        ? $request->query->getFloat('minRating')
        : null,
    );
  }

  private function applyFilters(): static
  {
    if ($this->category !== null) {
      $this->conditions[] = 'EXISTS (
        SELECT 1 FROM categories
        WHERE categories.id = products.category_id
        AND categories.name = :category
      )';
      $this->params[':category'] = $this->category;
    }

    if ($this->brand !== null) {
      $this->conditions[] = 'EXISTS (
        SELECT 1 FROM brands
        WHERE brands.id = products.brand_id
        AND brands.name = :brand
      )';
      $this->params[':brand'] = $this->brand;
    }

    return $this;
  }

  private function applyRanges(): static
  {
    if ($this->minPrice !== null) {
      $this->conditions[] = 'products.price >= :minPrice';
      $this->params[':minPrice'] = $this->minPrice;
    }

    if ($this->maxPrice !== null) {
      $this->conditions[] = 'products.price <= :maxPrice';
      $this->params[':maxPrice'] = $this->maxPrice;
    }

    if ($this->minRating !== null) {
      $this->conditions[] = 'products.rating >= :minRating';
      $this->params[':minRating'] = $this->minRating;
    }

    return $this;
  }

  private function applySearch(): static
  {
    if ($this->search !== null) {
      $this->conditions[] = 'products.title LIKE :search';
      $this->params[':search'] = '%' . $this->search . '%';
    }

    return $this;
  }

  private function applySort(): void
  {
    $column = self::SORT_COLUMNS[$this->sortBy] ?? 'products.title';
    $direction = strtoupper($this->order) === 'DESC' ? 'DESC' : 'ASC';
    $this->resolvedOrderBy = "$column $direction";
  }
}
