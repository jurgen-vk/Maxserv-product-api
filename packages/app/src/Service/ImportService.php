<?php

declare(strict_types=1);

namespace MaxServ\App\Service;

use MaxServ\App\Entity\Import;
use RuntimeException;

final readonly class ImportService
{
    public function __construct(
        private iterable $importers,
    ) {}

    public function run(string $type, Import $import): void
    {
        foreach ($this->importers as $importer) {
            if ($importer->supports(type: $type)) {
                $importer->import(import: $import);
                return;
            }
        }

        throw new RuntimeException("No importer supports type: $type");
    }
}
