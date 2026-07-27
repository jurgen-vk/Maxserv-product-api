<?php

declare(strict_types=1);

namespace MaxServ\App\Hydrator;

use DateTimeImmutable;
use MaxServ\App\Entity\Import;
use MaxServ\App\Enum\ImportStatus;

final readonly class ImportHydrator
{
    public function hydrateFromDatabase(array $row): Import
    {
        return new Import(
            id: (int)$row['id'],
            type: $row['type'],
            status: ImportStatus::from($row['status']),
            startedAt: new DateTimeImmutable(datetime: $row['started_at']),
            completedAt: $row['completed_at'] !== null
                ? new DateTimeImmutable(datetime: $row['completed_at'])
                : null,
            count: (int)$row['count'],
        );
    }
}
