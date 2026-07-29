<?php

declare(strict_types=1);

namespace MaxServ\Core\Notification\Event;

use Symfony\Contracts\EventDispatcher\Event;

final class NotificationEvent extends Event
{
    public function __construct(
        public readonly string $message,
        public readonly string $type = 'default',
        public readonly ?int $duration = null,
    ) {}
}
