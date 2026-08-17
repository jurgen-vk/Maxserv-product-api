<?php

declare(strict_types=1);

namespace MaxServ\App\EventSubscriber\Import;

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
        $data = $this->extractor->extract(import: $event->import);

        $this->broadcast(topic: "imports/{$event->import->id}", data: $data);
        $this->broadcast(topic: 'events/' . $event::name(), data: $data);

        $this->notify(
            message: ucfirst($event->import->type) . ' import started',
            type: NotificationType::Info,
        );
    }

    public function onProgress(ImportProgressEvent $event): void
    {
        $this->broadcast(
            topic: "imports/{$event->import->id}",
            data: $this->extractor->extract(import: $event->import),
        );
    }

    public function onCompleted(ImportCompletedEvent $event): void
    {
        $this->broadcast(
            topic: "imports/{$event->import->id}",
            data: $this->extractor->extract(import: $event->import),
        );

        $this->notify(
            message: ucfirst($event->import->type) . " import completed ({$event->import->processed} products)",
            type: NotificationType::Success,
        );
    }

    public function onFailed(ImportFailedEvent $event): void
    {
        $this->broadcast(
            topic: "imports/{$event->import->id}",
            data: $this->extractor->extract(import: $event->import),
        );

        $this->notify(message: ucfirst($event->import->type) . ' import failed', type: NotificationType::Danger);
    }

    private function broadcast(string $topic, array $data): void
    {
        $this->hub->publishSafely(
            new Update(topics: $topic, data: json_encode(value: $data)),
        );
    }

    private function notify(
        string $message,
        NotificationType $type = NotificationType::Default,
        ?string $icon = null,
        ?int $duration = null,
    ): void {
        $this->eventDispatcher->dispatch(
            new NotificationEvent(message: $message, type: $type, icon: $icon, duration: $duration),
        );
    }
}
