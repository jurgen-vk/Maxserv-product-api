<?php

declare(strict_types=1);

namespace MaxServ\App\Message;

use MaxServ\App\Entity\Import;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(transport: 'imports')]
final readonly class RunImportMessage
{
    public function __construct(
        public Import $import,
    ) {}
}
