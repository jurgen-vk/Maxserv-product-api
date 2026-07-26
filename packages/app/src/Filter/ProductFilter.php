<?php

declare(strict_types=1);

namespace MaxServ\App\Filter;

use Symfony\Component\HttpFoundation\Request;

final class ProductFilter
{
    public function __construct(
        public readonly ?string $category = null,
        public readonly ?string $brand = null,
        public readonly ?string $search = null,
        public readonly string $sortBy = 'title',
        public readonly string $order = 'ASC',
        public readonly ?float $minPrice = null,
        public readonly ?float $maxPrice = null,
        public readonly ?float $minRating = null,
    ) {
        $conditions = [];
        $bindings = [];

        foreach (get_object_vars($this) as $property => $value) {
            if ($value === null || in_array($property, ['sortBy', 'order'], true)) {
                continue;
            }

            $method = 'apply' . ucfirst($property);
            [$sql, $binding] = $this->$method($value);
            $conditions[] = $sql;
            $bindings = [...$bindings, ...$binding];
        }

        $this->filters = $conditions !== [] ? implode(' AND ', $conditions) : 'TRUE';
        $this->bindings = $bindings;
        $this->sorts = $this->buildSort();
    }

    private const SORTABLE_COLUMNS = [
        'title' => 'products.title',
        'price' => 'products.price',
        'rating' => 'products.rating',
        'stock' => 'products.stock',
    ];

    private const SEARCHABLE_COLUMNS = ['products.title'];

    public readonly string $filters;
    public readonly array $bindings;
    public readonly string $sorts;

    public static function fromRequest(Request $request): self
    {
        return new self(
            category: $request->query->getString('category') ?: null,
            brand: $request->query->getString('brand') ?: null,
            search: $request->query->getString('search') ?: null,
            sortBy: $request->query->getString('sort', 'title'),
            order: $request->query->getString('order', 'ASC'),
            minPrice: $request->query->filter('minPrice', null, \FILTER_VALIDATE_FLOAT, \FILTER_NULL_ON_FAILURE),
            maxPrice: $request->query->filter('maxPrice', null, \FILTER_VALIDATE_FLOAT, \FILTER_NULL_ON_FAILURE),
            minRating: $request->query->filter('minRating', null, \FILTER_VALIDATE_FLOAT, \FILTER_NULL_ON_FAILURE),
        );
    }

    private function applyCategory(string $value): array
    {
        return [
            'EXISTS (SELECT 1 FROM categories WHERE categories.id = products.category_id AND categories.name = :category)',
            [':category' => $value],
        ];
    }

    private function applyBrand(string $value): array
    {
        return [
            'EXISTS (SELECT 1 FROM brands WHERE brands.id = products.brand_id AND brands.name = :brand)',
            [':brand' => $value],
        ];
    }

    private function applySearch(string $value): array
    {
        $conditions = array_map(fn($column) => "$column LIKE :search", self::SEARCHABLE_COLUMNS);
        return ['(' . implode(' OR ', $conditions) . ')', [':search' => '%' . $value . '%']];
    }

    private function applyMinPrice(float $value): array
    {
        return ['products.price >= :minPrice', [':minPrice' => $value]];
    }

    private function applyMaxPrice(float $value): array
    {
        return ['products.price <= :maxPrice', [':maxPrice' => $value]];
    }

    private function applyMinRating(float $value): array
    {
        return ['products.rating >= :minRating', [':minRating' => $value]];
    }

    private function buildSort(): string
    {
        $column = self::SORTABLE_COLUMNS[$this->sortBy] ?? 'products.title';
        $direction = strtoupper($this->order) === 'DESC' ? 'DESC' : 'ASC';
        return "$column $direction";
    }
}
