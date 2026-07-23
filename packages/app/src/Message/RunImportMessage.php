<?php

declare(strict_types=1);

namespace MaxServ\App\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(transport: 'imports')]
readonly class RunImportMessage
{
  public function __construct(
    public int $importRunId,
    public string $type,
  ) {}
}
