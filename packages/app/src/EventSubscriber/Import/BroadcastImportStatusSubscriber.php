<?php

declare(strict_types=1);

namespace MaxServ\App\EventSubscriber\Import;

use MaxServ\App\Entity\Import;
use MaxServ\App\Event\Import\ImportCompletedEvent;
use MaxServ\App\Event\Import\ImportFailedEvent;
use MaxServ\App\Event\Import\ImportProgressEvent;
use MaxServ\App\Event\Import\ImportStartedEvent;
use MaxServ\App\Extractor\ImportExtractor;
use MaxServ\Core\Mercure\SafeHub;
use MaxServ\Core\Notification\Enum\NotificationType;
use MaxServ\Core\Notification\Event\NotificationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class BroadcastImportStatusSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SafeHub $hub,
        private EventDispatcherInterface $eventDispatcher,
        private ImportExtractor $extractor,
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
        $this->broadcast(import: $event->import);

        $this->eventDispatcher->dispatch(
            new NotificationEvent(
                message: ucfirst($event->import->type) . ' import started',
                type: NotificationType::Info,
            ),
        );
    }

    public function onProgress(ImportProgressEvent $event): void
    {
        $this->broadcast(import: $event->import);
    }

    public function onCompleted(ImportCompletedEvent $event): void
    {
        $this->broadcast(import: $event->import);

        $this->eventDispatcher->dispatch(
            new NotificationEvent(
                message: ucfirst($event->import->type) . " import completed ({$event->import->processed} products)",
                type: NotificationType::Success,
            ),
        );
    }

    public function onFailed(ImportFailedEvent $event): void
    {
        $this->broadcast(import: $event->import);

        $this->eventDispatcher->dispatch(
            new NotificationEvent(
                message: ucfirst($event->import->type) . ' import failed',
                type: NotificationType::Danger,
            ),
        );
    }

    private function broadcast(Import $import): void
    {
        $this->hub->publishSafely(
            new Update(
                topics: "imports/{$import->id}",
                data: json_encode($this->extractor->extract(import: $import)),
            ),
        );
    }
}
