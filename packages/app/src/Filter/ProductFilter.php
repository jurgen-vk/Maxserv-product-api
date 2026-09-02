<?php

declare(strict_types=1);

namespace MaxServ\App\Filter;

use Symfony\Component\HttpFoundation\Request;

final class ProductFilter extends AbstractFilter
{
    public function __construct(
        public readonly ?string $category = null,
        public readonly ?string $brand = null,
        public readonly ?string $search = null,
        public readonly ?float $minPrice = null,
        public readonly ?float $maxPrice = null,
        public readonly ?float $minRating = null,
        public readonly ?string $sortBy = 'title',
        public readonly ?string $sortDir = 'ASC',
    ) {
        parent::__construct();
    }

    protected const array SORTABLE_COLUMNS = [
        'title' => 'products.title',
        'price' => 'products.price',
        'rating' => 'products.rating',
        'stock' => 'products.stock',
        'brand' => 'brands.name',
        'category' => 'categories.name',
        'discounted' => 'products.discounted_price',
    ];

    protected const array SEARCHABLE_COLUMNS = ['products.title', 'brands.name', 'categories.name'];

    protected const array RELATION_JOINS = [
        'brands' => 'LEFT JOIN brands ON brands.id = products.brand_id',
        'categories' => 'LEFT JOIN categories ON categories.id = products.category_id',
    ];

    public static function fromRequest(Request $request): self
    {
        return new self(
            category: $request->query->get(key: 'category'),
            brand: $request->query->get(key: 'brand'),
            search: $request->query->get(key: 'search'),
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
            sortBy: $request->query->getString(key: 'sortBy', default: 'title'),
            sortDir: $request->query->getString(key: 'sortDir', default: 'ASC'),
        );
    }

    protected function filter(): void
    {
        $this->search(value: $this->search);

        if ($this->category !== null) {
            $this->where(
                sql: 'EXISTS (
                    SELECT 1 FROM categories
                    WHERE categories.id = products.category_id
                    AND categories.name = :category
                )',
                bindings: [':category' => $this->category],
            );
        }

        if ($this->brand !== null) {
            $this->where(
                sql: 'EXISTS (
                    SELECT 1 FROM brands
                    WHERE brands.id = products.brand_id
                    AND brands.name = :brand
                )',
                bindings: [':brand' => $this->brand],
            );
        }

        if ($this->minPrice !== null) {
            $this->where(sql: 'products.price >= :minPrice', bindings: [':minPrice' => $this->minPrice]);
        }

        if ($this->maxPrice !== null) {
            $this->where(sql: 'products.price <= :maxPrice', bindings: [':maxPrice' => $this->maxPrice]);
        }

        if ($this->minRating !== null) {
            $this->where(sql: 'ROUND(products.rating, 1) >= :minRating', bindings: [':minRating' => $this->minRating]);
        }

        $this->sort(column: $this->sortBy, direction: $this->sortDir);
    }
}
