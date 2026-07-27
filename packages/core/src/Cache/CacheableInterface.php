<?php

declare(strict_types=1);

namespace MaxServ\Core\Cache;

interface CacheableInterface
{
    public function cache(): mixed;

    public function clear(): void;
}
