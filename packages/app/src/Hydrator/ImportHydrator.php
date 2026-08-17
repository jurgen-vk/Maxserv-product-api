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
            status: ImportStatus::from(value: $row['status']),
            startedAt: new DateTimeImmutable(datetime: $row['started_at']),
            endedAt: $row['ended_at'] !== null
                ? new DateTimeImmutable(datetime: $row['ended_at'])
                : null,
            processed: (int)$row['processed'],
            total: (int)$row['total'],
            durationSeconds: $row['duration_seconds'] !== null ? (int)$row['duration_seconds'] : null,
        );
    }
}
