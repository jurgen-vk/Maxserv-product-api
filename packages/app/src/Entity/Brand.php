<?php

declare(strict_types=1);

namespace MaxServ\App\Entity;

class Brand
{
  public function __construct(
    public int $id,
    public string $name,
  ) {}
}
