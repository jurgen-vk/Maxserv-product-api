<?php

declare(strict_types=1);

namespace MaxServ\App\Service;

use MaxServ\App\Interface\ImportProgressReporterInterface;
use RuntimeException;

class ImportService
{
  public function __construct(
    private readonly iterable $importers,
  ) {}

  public function run(string $type, ImportProgressReporterInterface $reporter): int
  {
    foreach ($this->importers as $importer) {
      if ($importer->supports(type: $type)) {
        return $importer->import(reporter: $reporter);
      }
    }

    throw new RuntimeException("No importer supports type: $type");
  }
}
