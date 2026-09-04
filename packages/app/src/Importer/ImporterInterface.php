<?php

declare(strict_types=1);

namespace MaxServ\App\Importer;

use MaxServ\App\Entity\Import;

interface ImporterInterface
{
    public function supports(string $type): bool;

    public function import(Import $import): void;
}
