<?php

declare(strict_types=1);

namespace MaxServ\App\MessageHandler;

use MaxServ\App\Message\RunImportMessage;
use MaxServ\App\Service\ImportService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'messenger.bus.default')]
final readonly class RunImportHandler
{
    public function __construct(
        private ImportService $importService,
    ) {}

    public function __invoke(RunImportMessage $message): void
    {
        $this->importService->run(import: $message->import);
    }
}
