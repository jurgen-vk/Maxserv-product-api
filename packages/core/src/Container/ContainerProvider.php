<?php

declare(strict_types=1);

namespace MaxServ\Core\Container;

use LogicException;
use MaxServ\Core\Cache\CachableInterface;
use Symfony\Component\DependencyInjection\Container;

final class ContainerProvider implements CachableInterface
{
    public function __construct(
        private readonly ContainerAssembler $assembler,
        private readonly ContainerCacher $cacher,
    ) {
    }

    public function retrieve(): Container
    {
        return $this->cacher->load() ?? $this->cache();
    }

    public function cache(): Container
    {
        $this->cacher->persist($this->assembler->build());

        // Reload from the just-persisted dump rather than returning the ContainerBuilder
        // directly: %env(...)% placeholders only resolve to real values once dumped by
        // PhpDumper and reloaded as the compiled class — a live ContainerBuilder leaves
        // them as internal placeholder tokens, which would otherwise leak into any value
        // built by string-interpolating a parameter (e.g. Connection's DSN string).
        return $this->cacher->load() ?? throw new LogicException('Container cache was just persisted but could not be reloaded.');
    }

    public function clear(): void
    {
        $this->cacher->clear();
    }
}
