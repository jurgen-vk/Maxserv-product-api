<?php

declare(strict_types=1);

namespace MaxServ\App\Event\Import;

use MaxServ\App\Entity\Import;
use Symfony\Contracts\EventDispatcher\Event;

final class ImportStartedEvent extends Event
{
    public function __construct(
        public readonly Import $import,
    ) {}
}