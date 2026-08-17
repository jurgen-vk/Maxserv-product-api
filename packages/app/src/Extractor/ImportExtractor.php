<?php

declare(strict_types=1);

namespace MaxServ\App\Extractor;

use MaxServ\App\Entity\Import;

final class ImportExtractor
{
    public function extract(Import $import): array
    {
        return [
            'id' => $import->id,
            'type' => $import->type,
            'status' => $import->status->value,
            'startedAt' => $import->startedAt->format(format: DATE_ATOM),
            'endedAt' => $import->endedAt?->format(format: DATE_ATOM),
            'processed' => $import->processed,
            'total' => $import->total,
            'durationSeconds' => $import->durationSeconds,
        ];
    }
}
