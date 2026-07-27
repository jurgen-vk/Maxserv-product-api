<?php

declare(strict_types=1);

namespace MaxServ\App\Service;

use RuntimeException;

final readonly class ImportService
{
    public function __construct(
        private iterable $importers,
    ) {}

    public function run(string $type, int $importId): int
    {
        foreach ($this->importers as $importer) {
            if ($importer->supports(type: $type)) {
                return $importer->import(importId: $importId);
            }
        }

        throw new RuntimeException("No importer supports type: $type");
    }
}
