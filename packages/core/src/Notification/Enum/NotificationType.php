<?php

declare(strict_types=1);

namespace MaxServ\Core\Notification\Enum;

enum NotificationType: string
{
    case Success = 'success';
    case Warning = 'warning';
    case Danger = 'danger';
    case Info = 'info';
    case Default = 'default';

    public function defaultIcon(): string
    {
        return match ($this) {
            self::Success => 'lucide:message-square-check',
            self::Warning => 'lucide:message-square-warning',
            self::Danger => 'lucide:message-square-x',
            self::Info => 'lucide:message-square-dot',
            self::Default => 'lucide:message-square',
        };
    }
}
