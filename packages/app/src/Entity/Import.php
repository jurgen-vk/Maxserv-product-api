<?php

declare(strict_types=1);

namespace MaxServ\App\Entity;

use DateTimeImmutable;
use MaxServ\App\Enum\ImportStatus;

final class Import
{
    public function __construct(
        public string $type,
        public ?int $id = null,
        private(set) ImportStatus $status = ImportStatus::Pending,
        public readonly DateTimeImmutable $startedAt = new DateTimeImmutable(),
        private(set) ?DateTimeImmutable $completedAt = null,
        private(set) int $count = 0,
    ) {}

    public function markRunning(): void
    {
        $this->status = ImportStatus::Running;
    }

    public function markCompleted(int $count): void
    {
        $this->status = ImportStatus::Completed;
        $this->completedAt = new DateTimeImmutable();
        $this->count = $count;
    }

    public function markFailed(): void
    {
        $this->status = ImportStatus::Failed;
    }
}
