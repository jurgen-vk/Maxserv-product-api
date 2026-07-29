<?php

declare(strict_types=1);

namespace MaxServ\App\Dto\Pagination;

final readonly class PaginatedResult
{
    public function __construct(
        public array $items,
        public Paginator $paginator,
        public array $meta = [],
    ) {}
}
