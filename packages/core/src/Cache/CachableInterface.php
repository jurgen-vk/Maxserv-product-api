<?php

declare(strict_types=1);

namespace MaxServ\Core\Cache;

interface CachableInterface
{
    public function cache(): mixed;

    public function clear(): void;
}
