<?php

declare(strict_types=1);

namespace MaxServ\Core\Notification\Event;

use MaxServ\Core\Event\AppEvent;
use MaxServ\Core\Notification\Enum\NotificationType;

final class NotificationEvent extends AppEvent
{
    public readonly string $icon;

    public function __construct(
        public readonly string $message,
        public readonly NotificationType $type = NotificationType::Default,
        ?string $icon = null,
        public readonly ?int $duration = null,
    ) {
        $this->icon = $icon ?? $this->type->defaultIcon();
    }
}
