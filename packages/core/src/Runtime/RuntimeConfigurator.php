<?php

declare(strict_types=1);

namespace MaxServ\Core\Runtime;

final readonly class RuntimeConfigurator
{
    public static function configure(): void
    {
        $debug = getenv(name: 'APP_DEBUG') === 'true';
        $isDev = getenv(name: 'APP_ENV') !== 'prod';
        $logErrors = getenv('APP_LOG_ERRORS') !== 'false';

        ini_set(option: 'display_errors', value: $debug ? '1' : '0');
        ini_set(option: 'log_errors', value: $logErrors ? '1' : '0');
        ini_set(option: 'opcache.validate_timestamps', value: $isDev ? '1' : '0');
    }
}
