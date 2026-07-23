<?php

declare(strict_types=1);

namespace MaxServ\App\Hydrator;

use DateTimeImmutable;
use MaxServ\App\Entity\Import;
use MaxServ\App\Enum\ImportStatus;

class ImportHydrator
{
  public function hydrateFromDatabase(array $row): Import
  {
    return new Import(
      type: $row['type'],
      startedAt: new DateTimeImmutable($row['started_at']),
      id: (int) $row['id'],
      status: ImportStatus::from($row['status']),
      completedAt: $row['completed_at'] !== null ? new DateTimeImmutable($row['completed_at']) : null,
      count: (int) $row['count'],
    );
  }
}
