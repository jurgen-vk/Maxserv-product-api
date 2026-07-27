<?php

declare(strict_types=1);

namespace MaxServ\App\Interface;

interface ImporterInterface
{
  public function supports(string $type): bool;

  public function import(int $importId): int;
}
