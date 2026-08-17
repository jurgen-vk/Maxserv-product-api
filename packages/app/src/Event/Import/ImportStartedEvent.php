<?php

declare(strict_types=1);

namespace MaxServ\App\Event\Import;

use MaxServ\App\Entity\Import;
use MaxServ\Core\Event\AppEvent;

final class ImportStartedEvent extends AppEvent
{
    public function __construct(
        public readonly Import $import,
    ) {}
}