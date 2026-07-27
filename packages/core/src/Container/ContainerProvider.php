<?php

declare(strict_types=1);

namespace MaxServ\Core\Container;

use LogicException;
use MaxServ\Core\Cache\CacheableInterface;
use Symfony\Component\DependencyInjection\Container;

final readonly class ContainerProvider implements CacheableInterface
{
    public function __construct(
        private ContainerAssembler $assembler,
        private ContainerCacher $cacher,
    ) {}

    public function retrieve(): Container
    {
        return $this->cacher->load() ?? $this->cache();
    }

    public function cache(): Container
    {
        $this->cacher->persist(container: $this->assembler->build());

        return $this->cacher->load() ?? throw new LogicException(
            message: 'Container cache was just persisted but could not be reloaded.',
        );
    }

    public function clear(): void
    {
        $this->cacher->clear();
    }
}
