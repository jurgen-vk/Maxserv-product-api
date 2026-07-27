<?php

declare(strict_types=1);

namespace MaxServ\Core\Routing;

use MaxServ\Core\Cache\CacheableInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Router as SymfonyRouter;

final readonly class RouteCacher implements CacheableInterface
{
    public function __construct(
        private SymfonyRouter $router,
        private Filesystem $filesystem,
    ) {}

    public function cache(): mixed
    {
        $this->router->getMatcher();
        $this->router->getGenerator();

        return null;
    }

    public function clear(): void
    {
        $this->filesystem->remove(files: APPLICATION_ROOT . '/var/cache/routing');
    }
}
