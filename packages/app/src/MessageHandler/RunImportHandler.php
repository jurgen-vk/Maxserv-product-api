<?php

declare(strict_types=1);

namespace MaxServ\App\MessageHandler;

use MaxServ\App\Event\Import\ImportCompletedEvent;
use MaxServ\App\Event\Import\ImportFailedEvent;
use MaxServ\App\Event\Import\ImportStartedEvent;
use MaxServ\App\Message\RunImportMessage;
use MaxServ\App\Repository\ImportRepository;
use MaxServ\App\Service\ImportService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsMessageHandler(bus: 'messenger.bus.default')]
final readonly class RunImportHandler
{
    public function __construct(
        private ImportRepository $importRepository,
        private ImportService $importService,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function __invoke(RunImportMessage $message): void
    {
        $import = $this->importRepository->find(id: $message->importRunId);
        $import->markRunning();
        $this->importRepository->save(import: $import);

        $this->eventDispatcher->dispatch(event: new ImportStartedEvent(import: $import));

        try {
            $this->importService->run(type: $message->type, import: $import);
        } catch (\Throwable $exception) {
            $import->markFailed();
            $this->importRepository->save(import: $import);

            $this->eventDispatcher->dispatch(event: new ImportFailedEvent(import: $import));

            throw $exception;
        }

        $import->markCompleted();
        $this->importRepository->save(import: $import);

        $this->eventDispatcher->dispatch(event: new ImportCompletedEvent(import: $import));
    }
}
