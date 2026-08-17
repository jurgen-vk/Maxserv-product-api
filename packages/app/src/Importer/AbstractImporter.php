<?php

declare(strict_types=1);

namespace MaxServ\App\Importer;

use MaxServ\App\Entity\Import;
use MaxServ\App\Event\Import\ImportCompletedEvent;
use MaxServ\App\Event\Import\ImportFailedEvent;
use MaxServ\App\Event\Import\ImportProgressEvent;
use MaxServ\App\Event\Import\ImportStartedEvent;
use MaxServ\App\Interface\ImporterInterface;
use MaxServ\App\Repository\ImportRepository;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract readonly class AbstractImporter implements ImporterInterface
{
    public function __construct(
        private ImportRepository $importRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    abstract public function supports(string $type): bool;

    abstract protected function run(Import $import): void;

    final public function import(Import $import): void
    {
        $this->markStarted(import: $import);

        try {
            $this->run(import: $import);
        } catch (\Throwable $exception) {
            $this->markFailed(import: $import);
            throw $exception;
        }

        $this->markCompleted(import: $import);
    }

    final protected function markRunning(Import $import, int $total): void
    {
        $import->markRunning(total: $total);
        $this->importRepository->save(import: $import);
        $this->eventDispatcher->dispatch(event: new ImportProgressEvent(import: $import));
    }

    final protected function updateProgress(Import $import, int $processed): void
    {
        $import->processed = $processed;
        $this->importRepository->save(import: $import);
        $this->eventDispatcher->dispatch(event: new ImportProgressEvent(import: $import));
    }

    private function markStarted(Import $import): void
    {
        $import->markStarted();
        $this->importRepository->save(import: $import);
        $this->eventDispatcher->dispatch(event: new ImportStartedEvent(import: $import));
    }

    private function markFailed(Import $import): void
    {
        $import->markFailed();
        $this->importRepository->save(import: $import);
        $import = $this->importRepository->find(id: $import->id);
        $this->eventDispatcher->dispatch(event: new ImportFailedEvent(import: $import));
    }

    private function markCompleted(Import $import): void
    {
        $import->markCompleted();
        $this->importRepository->save(import: $import);
        $import = $this->importRepository->find(id: $import->id);
        $this->eventDispatcher->dispatch(event: new ImportCompletedEvent(import: $import));
    }
}
