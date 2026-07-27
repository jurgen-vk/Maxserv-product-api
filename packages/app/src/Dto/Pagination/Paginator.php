<?php

declare(strict_types=1);

namespace MaxServ\App\Dto\Pagination;

final class Paginator
{
    public function __construct(
        int|string|null $page,
        int|string|null $perPage,
        public readonly int $total,
        public readonly string $prefix = '',
    ) {
        $this->perPage = $perPage;
        $this->page = $page;
    }

    private const int DEFAULT_PAGE = 1;
    private const int DEFAULT_PER_PAGE = 25;

    public private(set) int $perPage {
        set(int|string|null $value) {
            $parsed = filter_var($value, \FILTER_VALIDATE_INT);
            $parsed = $parsed !== false ? $parsed : self::DEFAULT_PER_PAGE;
            $this->perPage = min(100, max(10, $parsed));
        }
    }

    public private(set) int $page {
        set(int|string|null $value) {
            $parsed = filter_var($value, \FILTER_VALIDATE_INT);
            $parsed = $parsed !== false ? $parsed : self::DEFAULT_PAGE;
            $this->page = max(1, min($parsed, $this->totalPages));
        }
    }

    public int $totalPages {
        get => $this->perPage > 0 ? max(1, (int)ceil($this->total / $this->perPage)) : 1;
    }

    public bool $hasPreviousPage {
        get => $this->page > 1;
    }

    public bool $hasNextPage {
        get => $this->page < $this->totalPages;
    }

    public int $offset {
        get => ($this->page - 1) * $this->perPage;
    }

    public string $pageParam {
        get => $this->prefix . 'page';
    }

    public string $perPageParam {
        get => $this->prefix . 'perPage';
    }
}
