<?php

declare(strict_types=1);

namespace MaxServ\Core\Routing;

use MaxServ\Core\Cache\CachableInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Router as SymfonyRouter;

final class RouteCacher implements CachableInterface
{
    public function __construct(
        private readonly SymfonyRouter $router,
        private readonly Filesystem $filesystem,
    ) {
    }

    public function cache(): mixed
    {
        $this->router->getMatcher();
        $this->router->getGenerator();

        return null;
    }

    public function clear(): void
    {
        $this->filesystem->remove(APPLICATION_ROOT . '/var/cache/routing');
    }
}
