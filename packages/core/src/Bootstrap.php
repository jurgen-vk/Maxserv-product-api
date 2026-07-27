<?php

declare(strict_types=1);

namespace MaxServ\Core;

use MaxServ\Core\Container\ContainerAssembler;
use MaxServ\Core\Container\ContainerCacher;
use MaxServ\Core\Container\ContainerProvider;
use MaxServ\Core\Runtime\RuntimeConfigurator;
use Symfony\Component\DependencyInjection\Container;

final readonly class Bootstrap
{
    public Container $container;

    public function boot(): void
    {
        RuntimeConfigurator::configure();

        $isDev = getenv(name: 'APP_ENV') !== 'prod';
        $this->container = (new ContainerProvider(
            assembler: new ContainerAssembler(),
            cacher: new ContainerCacher($isDev),
        ))->retrieve();
    }
}
