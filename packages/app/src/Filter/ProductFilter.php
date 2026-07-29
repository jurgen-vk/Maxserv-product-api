<?php

declare(strict_types=1);

namespace MaxServ\App\Filter;

use Symfony\Component\HttpFoundation\Request;

final readonly class ProductFilter
{
    public function __construct(
        public ?string $category = null,
        public ?string $brand = null,
        public ?string $search = null,
        public string $sortBy = 'title',
        public string $order = 'ASC',
        public ?float $minPrice = null,
        public ?float $maxPrice = null,
        public ?float $minRating = null,
    ) {
        [$this->filters, $this->bindings] = $this->buildFilters();
        $this->sorts = $this->buildSort();
        $this->sortJoins = $this->buildSortJoins();
    }

    private const array SORTABLE_COLUMNS = [
        'title' => 'products.title',
        'price' => 'products.price',
        'rating' => 'products.rating',
        'stock' => 'products.stock',
        'brand' => 'brands.name',
        'category' => 'categories.name',
        'discounted' => 'products.price * (1 - products.discount_percentage / 100)',
    ];

    private const array SORT_JOINS = [
        'brand' => 'LEFT JOIN brands ON brands.id = products.brand_id',
        'category' => 'LEFT JOIN categories ON categories.id = products.category_id',
    ];

    private const array SEARCHABLE_COLUMNS = ['products.title'];

    public string $filters;
    public array $bindings;
    public string $sorts;
    public string $sortJoins;

    public static function fromRequest(Request $request): self
    {
        return new self(
            category: $request->query->getString(key: 'category') ?: null,
            brand: $request->query->getString(key: 'brand') ?: null,
            search: $request->query->getString(key: 'search') ?: null,
            sortBy: $request->query->getString(key: 'sort', default: 'title'),
            order: $request->query->getString(key: 'order', default: 'ASC'),
            minPrice: $request->query->filter(
                key: 'minPrice',
                filter: \FILTER_VALIDATE_FLOAT,
                options: \FILTER_NULL_ON_FAILURE,
            ),
            maxPrice: $request->query->filter(
                key: 'maxPrice',
                filter: \FILTER_VALIDATE_FLOAT,
                options: \FILTER_NULL_ON_FAILURE,
            ),
            minRating: $request->query->filter(
                key: 'minRating',
                filter: \FILTER_VALIDATE_FLOAT,
                options: \FILTER_NULL_ON_FAILURE,
            ),
        );
    }

    private function applyCategory(string $value): array
    {
        return [
            'EXISTS (
                SELECT 1 FROM categories 
                WHERE categories.id = products.category_id 
                AND categories.name = :category
            )',
            [':category' => $value],
        ];
    }

    private function applyBrand(string $value): array
    {
        return [
            'EXISTS (
                SELECT 1 FROM brands 
                WHERE brands.id = products.brand_id 
                AND brands.name = :brand
            )',
            [':brand' => $value],
        ];
    }

    private function applySearch(string $value): array
    {
        $conditions = array_map(
            callback: fn($column) => "$column LIKE :search",
            array: self::SEARCHABLE_COLUMNS,
        );
        return [
            '(' . implode(separator: ' OR ', array: $conditions) . ')',
            [':search' => '%' . $value . '%'],
        ];
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

    private function buildFilters(): array
    {
        $conditions = [];
        $bindings = [];

        foreach (get_object_vars($this) as $property => $value) {
            if ($value === null || in_array(needle: $property, haystack: ['sortBy', 'order'])) {
                continue;
            }

            $method = 'apply' . ucfirst(string: $property);
            [$sql, $binding] = $this->$method($value);
            $conditions[] = $sql;
            $bindings = [...$bindings, ...$binding];
        }

        $filters = $conditions !== [] ? implode(separator: ' AND ', array: $conditions) : 'TRUE';

        return [$filters, $bindings];
    }

    private function buildSort(): string
    {
        $column = self::SORTABLE_COLUMNS[$this->sortBy] ?? 'products.title';
        $direction = strtoupper($this->order) === 'DESC' ? 'DESC' : 'ASC';
        return "$column $direction";
    }

    private function buildSortJoins(): string
    {
        return self::SORT_JOINS[$this->sortBy] ?? '';
    }
}
