<?php

declare(strict_types=1);

namespace MaxServ\Core\Notification\EventSubscriber;

use MaxServ\Core\Mercure\SafeHub;
use MaxServ\Core\Notification\Event\NotificationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mercure\Update;

final readonly class BroadcastNotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SafeHub $hub,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            NotificationEvent::class => 'onNotification',
        ];
    }

    public function onNotification(NotificationEvent $event): void
    {
        $this->hub->publishSafely(
            new Update(
                topics: 'notifications',
                data: json_encode(
                    value: [
                        'message' => $event->message,
                        'type' => $event->type,
                        'duration' => $event->duration,
                    ],
                ),
            ),
        );
    }
}
