<?php

declare(strict_types=1);

namespace MaxServ\App\Dto\Pagination;

use Symfony\Component\HttpFoundation\Request;

class Paginator
{
  public int $totalPages {
    get => $this->total !== null && $this->perPage > 0
      ? (int) ceil($this->total / $this->perPage)
      : 1;
  }

  public bool $hasPreviousPage {
    get => $this->page > 1;
  }

  public bool $hasNextPage {
    get => $this->total !== null && $this->page < $this->totalPages;
  }

  public int $offset {
    get => ($this->page - 1) * $this->perPage;
  }

  public string $pageParam {
    get => $this->prefix . 'page';
  }

  public string $countParam {
    get => $this->prefix . 'count';
  }

  public ?int $total {
    set {
      $this->total = $value;
      if ($value !== null && $value > 0 && $this->perPage > 0) {
        $totalPages = (int) ceil($value / $this->perPage);
        if ($this->page > $totalPages) {
          $this->page = max(1, $totalPages);
        }
      }
    }
  }

  public function __construct(
    public int $page = 1,
    public int $perPage = 25,
    public string $prefix = '',
  ) {}

  public static function fromRequest(Request $request, string $prefix = ''): self
  {
    $page = max(1, $request->query->getInt($prefix . 'page', 1));
    $perPage = min(100, max(10, $request->query->getInt($prefix . 'count', 25)));

    return new self(page: $page, perPage: $perPage, prefix: $prefix);
  }
}
