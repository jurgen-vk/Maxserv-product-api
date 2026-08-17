<?php

declare(strict_types=1);

namespace MaxServ\Core\Event;

use Symfony\Contracts\EventDispatcher\Event;

abstract class AppEvent extends Event
{
    public static function name(): string
    {
        return (new \ReflectionClass(static::class))->getShortName();
    }
}
