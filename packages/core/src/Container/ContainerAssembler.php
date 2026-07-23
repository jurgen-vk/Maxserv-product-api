<?php

declare(strict_types=1);

namespace MaxServ\Core\Container;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;

final class ContainerAssembler
{
    public function build(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        ContainerConfigurator::configure($container);

        $loader = new YamlFileLoader($container, new FileLocator(APPLICATION_ROOT . '/packages'));

        $finder = new Finder();
        $serviceFiles = $finder->files()->in(APPLICATION_ROOT . '/packages')->name('services.yaml');

        foreach ($serviceFiles as $serviceFile) {
            $loader->load($serviceFile->getPathname());
        }

        $container->compile();

        return $container;
    }
}
