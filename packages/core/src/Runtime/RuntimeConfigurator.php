<?php

declare(strict_types=1);

namespace MaxServ\Core\Runtime;

final class RuntimeConfigurator
{
    public static function configure(): void
    {
        $debug = getenv('APP_DEBUG') === 'true';
        $isDev = getenv('APP_ENV') !== 'prod';
        // $logErrors = getenv('APP_LOG_ERRORS') !== 'false'; // fail-safe: on unless explicitly disabled, matching APP_ENV's own fail-closed pattern

        ini_set('display_errors', $debug ? '1' : '0');
        // ini_set('log_errors', $logErrors ? '1' : '0'); // paused — not a priority right now
        ini_set('opcache.validate_timestamps', $isDev ? '1' : '0');
    }
}
