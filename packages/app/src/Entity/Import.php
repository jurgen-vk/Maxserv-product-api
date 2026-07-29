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
        private(set) int $processed = 0,
        private(set) int $total = 0,
    ) {}

    public function markRunning(): void
    {
        $this->status = ImportStatus::Running;
    }

    public function markProgress(int $processed, int $total): void
    {
        $this->processed = $processed;
        $this->total = $total;
    }

    public function markCompleted(): void
    {
        $this->status = ImportStatus::Completed;
        $this->completedAt = new DateTimeImmutable();
    }

    public function markFailed(): void
    {
        $this->status = ImportStatus::Failed;
    }
}
