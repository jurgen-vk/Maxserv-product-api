<?php

declare(strict_types=1);

namespace MaxServ\Core\Container;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;

final readonly class ContainerAssembler
{
    public function build(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        ContainerConfigurator::configure(container: $container);

        $loader = new YamlFileLoader(
            container: $container,
            locator: new FileLocator(paths: APPLICATION_ROOT . '/packages'),
        );

        $finder = new Finder();
        $serviceFiles = $finder
            ->files()
            ->in(dirs: APPLICATION_ROOT . '/packages')
            ->name(patterns: 'services.yaml');

        foreach ($serviceFiles as $serviceFile) {
            $loader->load(resource: $serviceFile->getPathname());
        }

        $container->compile();

        return $container;
    }
}
