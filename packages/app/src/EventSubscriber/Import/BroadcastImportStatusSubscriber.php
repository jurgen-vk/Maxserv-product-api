<?php

declare(strict_types=1);

namespace MaxServ\App\EventSubscriber\Import;

use MaxServ\App\Event\Import\ImportCompletedEvent;
use MaxServ\App\Event\Import\ImportFailedEvent;
use MaxServ\App\Event\Import\ImportProgressEvent;
use MaxServ\App\Event\Import\ImportStartedEvent;
use MaxServ\Core\Mercure\SafeHub;
use MaxServ\Core\Notification\Event\NotificationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class BroadcastImportStatusSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SafeHub $hub,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ImportStartedEvent::class => 'onStarted',
            ImportProgressEvent::class => 'onProgress',
            ImportCompletedEvent::class => 'onCompleted',
            ImportFailedEvent::class => 'onFailed',
        ];
    }

    public function onStarted(ImportStartedEvent $event): void
    {
        $this->broadcast(
            importId: $event->import->id,
            data: [
                'id' => $event->import->id,
                'status' => 'running',
            ],
        );

        $this->eventDispatcher->dispatch(
            new NotificationEvent(
                message: ucfirst($event->import->type) . ' import started',
            ),
        );
    }

    public function onProgress(ImportProgressEvent $event): void
    {
        $this->broadcast(
            importId: $event->import->id,
            data: [
                'id' => $event->import->id,
                'status' => 'running',
                'processed' => $event->import->processed,
                'total' => $event->import->total,
            ],
        );
    }

    public function onCompleted(ImportCompletedEvent $event): void
    {
        $this->broadcast(
            importId: $event->import->id,
            data: [
                'id' => $event->import->id,
                'status' => 'completed',
                'processed' => $event->import->processed,
            ],
        );

        $this->eventDispatcher->dispatch(
            new NotificationEvent(
                message: ucfirst($event->import->type) . " import completed ({$event->import->processed} products)",
                type: 'success',
            ),
        );
    }

    public function onFailed(ImportFailedEvent $event): void
    {
        $this->broadcast(
            importId: $event->import->id,
            data: [
                'id' => $event->import->id,
                'status' => 'failed',
            ],
        );

        $this->eventDispatcher->dispatch(
            new NotificationEvent(
                message: ucfirst($event->import->type) . ' import failed',
                type: 'error',
            ),
        );
    }

    private function broadcast(int $importId, array $data): void
    {
        $this->hub->publishSafely(
            new Update(topics: "imports/{$importId}", data: json_encode($data)),
        );
    }
}
