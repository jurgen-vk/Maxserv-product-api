<?php

declare(strict_types=1);

namespace MaxServ\Core;

use MaxServ\Core\Container\ContainerAssembler;
use MaxServ\Core\Container\ContainerCacher;
use MaxServ\Core\Container\ContainerProvider;
use MaxServ\Core\Runtime\RuntimeConfigurator;
use Symfony\Component\DependencyInjection\Container;

final class Bootstrap
{
    public readonly Container $container;

    public function boot(): void
    {
        RuntimeConfigurator::configure();

        $isDev = getenv('APP_ENV') !== 'prod';
        $this->container = (new ContainerProvider(
            new ContainerAssembler(),
            new ContainerCacher($isDev),
        ))->retrieve();
    }
}
