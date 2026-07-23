<?php

declare(strict_types=1);

namespace MaxServ\App\Dto\Pagination;

class PaginatedResult
{
  public function __construct(
    public readonly array $items,
    public readonly Paginator $paginator,
  ) {}
}
