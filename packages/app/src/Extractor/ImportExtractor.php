<?php

declare(strict_types=1);

namespace MaxServ\App\Extractor;

use MaxServ\App\Entity\Import;

class ImportExtractor
{
  public function extract(Import $import): array
  {
    return [
      'id'          => $import->id,
      'type'        => $import->type,
      'status'      => $import->status->value,
      'startedAt'   => $import->startedAt->format(DATE_ATOM),
      'completedAt' => $import->completedAt?->format(DATE_ATOM),
      'count'       => $import->count,
    ];
  }
}
