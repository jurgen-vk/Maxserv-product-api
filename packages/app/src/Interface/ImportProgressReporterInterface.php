<?php

declare(strict_types=1);

namespace MaxServ\App\Interface;

interface ImportProgressReporterInterface
{
  public function report(int $processed, int $total): void;
}
