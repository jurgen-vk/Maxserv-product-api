<?php

declare(strict_types=1);

namespace MaxServ\App\Event\Import;

use Symfony\Contracts\EventDispatcher\Event;

final class ImportProgressEvent extends Event
{
    public function __construct(
        public readonly int $importId,
        public readonly int $processed,
        public readonly int $total,
    ) {}
}
