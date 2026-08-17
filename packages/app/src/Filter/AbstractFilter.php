<?php

declare(strict_types=1);

namespace MaxServ\App\Filter;

abstract class AbstractFilter
{
    public function __construct()
    {
        $this->filter();

        $this->filters = $this->buildFilters();
        $this->bindings = $this->conditionBindings;
        $this->hasActiveFilter = $this->conditions !== [];

        $this->sorts = $this->buildSort();
        $this->joins = $this->buildJoins();
    }

    protected const array SORTABLE_COLUMNS = [];
    protected const array SEARCHABLE_COLUMNS = [];
    protected const array RELATION_JOINS = [];

    public readonly string $filters;
    public readonly array $bindings;
    public readonly bool $hasActiveFilter;
    private(set) ?string $sortByColumn = null;
    private(set) ?string $sortInDirection = null;
    private(set) bool $hasActiveSearch = false;
    private(set) bool $hasActiveSort = false;
    public readonly string $sorts;
    public readonly string $joins;

    private array $conditions = [];
    private array $conditionBindings = [];

    abstract protected function filter(): void;

    protected function sort(?string $column, ?string $direction): static
    {
        if ($column === null || !isset(static::SORTABLE_COLUMNS[$column])) {
            return $this;
        }

        $this->sortByColumn = $column;
        $this->sortInDirection = strtoupper(string: $direction ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
        $this->hasActiveSort = true;

        return $this;
    }

    protected function where(string $sql, array $bindings = []): static
    {
        $this->conditions[] = $sql;
        return $this->bindMany(bindings: $bindings);
    }

    protected function bind(string $placeholder, mixed $value): static
    {
        $this->conditionBindings[$placeholder] = $value;
        return $this;
    }

    protected function bindMany(array $bindings): static
    {
        $this->conditionBindings = [...$this->conditionBindings, ...$bindings];
        return $this;
    }

    protected function search(?string $value): static
    {
        if ($value === null || static::SEARCHABLE_COLUMNS === []) {
            return $this;
        }

        $this->hasActiveSearch = true;

        $orConditions = array_map(
            callback: fn(string $column): string => "$column LIKE :search",
            array: static::SEARCHABLE_COLUMNS,
        );

        return $this->where(
            sql: '(' . implode(separator: ' OR ', array: $orConditions) . ')',
            bindings: [':search' => '%' . $value . '%'],
        );
    }

    private function buildFilters(): string
    {
        return $this->conditions !== []
            ? 'WHERE ' . implode(separator: ' AND ', array: $this->conditions)
            : '';
    }

    private function buildSort(): string
    {
        return $this->hasActiveSort
            ? 'ORDER BY ' . static::SORTABLE_COLUMNS[$this->sortByColumn] . " {$this->sortInDirection}"
            : '';
    }

    private function buildJoins(): string
    {
        $columns = $this->hasActiveSort ? [static::SORTABLE_COLUMNS[$this->sortByColumn]] : [];

        if ($this->hasActiveSearch) {
            $columns = [...$columns, ...static::SEARCHABLE_COLUMNS];
        }

        $joins = [];
        foreach ($columns as $column) {
            $table = explode(separator: '.', string: $column, limit: 2)[0];
            $joins[$table] ??= static::RELATION_JOINS[$table] ?? null;
        }

        return implode(separator: ' ', array: array_filter($joins));
    }
}
