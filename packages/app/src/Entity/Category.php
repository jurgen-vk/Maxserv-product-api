<?php

declare(strict_types=1);

namespace MaxServ\App\Entity;

class Category
{
  public function __construct(
    public int $id,
    public string $name,
  ) {}
}
