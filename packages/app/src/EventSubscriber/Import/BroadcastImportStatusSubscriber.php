<?php

declare(strict_types=1);

namespace MaxServ\App\EventSubscriber\Import;

use MaxServ\App\Event\Import\ImportCompletedEvent;
use MaxServ\App\Event\Import\ImportFailedEvent;
use MaxServ\App\Event\Import\ImportProgressEvent;
use MaxServ\Core\Mercure\SafeHub;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mercure\Update;

final readonly class BroadcastImportStatusSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SafeHub $hub,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ImportProgressEvent::class => 'onProgress',
            ImportCompletedEvent::class => 'onCompleted',
            ImportFailedEvent::class => 'onFailed',
        ];
    }

    public function onProgress(ImportProgressEvent $event): void
    {
        $this->broadcast(importId: $event->importId, data: [
            'id' => $event->importId,
            'status' => 'running',
            'processed' => $event->processed,
            'total' => $event->total,
        ]);
    }

    public function onCompleted(ImportCompletedEvent $event): void
    {
        $this->broadcast(importId: $event->importId, data: [
            'id' => $event->importId,
            'status' => 'completed',
            'count' => $event->count,
        ]);
    }

    public function onFailed(ImportFailedEvent $event): void
    {
        $this->broadcast(importId: $event->importId, data: [
            'id' => $event->importId,
            'status' => 'failed',
        ]);
    }

    private function broadcast(int $importId, array $data): void
    {
        $this->hub->publishSafely(
            new Update(topics: "imports/{$importId}", data: json_encode($data)),
        );
    }
}
