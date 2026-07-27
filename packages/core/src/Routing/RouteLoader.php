<?php

declare(strict_types=1);

namespace MaxServ\Core\Routing;

use LogicException;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\Loader\AttributeDirectoryLoader;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RouteCollection;

final readonly class RouteLoader implements LoaderInterface
{
    public function __construct(
        private AttributeDirectoryLoader $attributeLoader,
        private YamlFileLoader $yamlLoader,
    ) {}

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        $collection = new RouteCollection();

        $controllerDirs = (new Finder())
            ->directories()
            ->in(dirs: APPLICATION_ROOT . '/packages')
            ->name(patterns: 'Controller');

        foreach ($controllerDirs as $dir) {
            $collection->addCollection(
                collection: $this->attributeLoader->load(path: $dir->getPathname()),
            );
        }

        $routeDirs = (new Finder())
            ->files()
            ->in(dirs: APPLICATION_ROOT . '/packages')
            ->name(patterns: 'routes.yaml');

        foreach ($routeDirs as $file) {
            $collection->addCollection(
                collection: $this->yamlLoader->load(file: $file->getPathname()),
            );
        }

        $collection->addResource(
            resource: new DirectoryResource(
                resource: APPLICATION_ROOT . '/packages',
                pattern: '/routes\.yaml$/',
            ),
        );

        return $collection;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return true;
    }

    public function setResolver(LoaderResolverInterface $resolver): void {}

    public function getResolver(): LoaderResolverInterface
    {
        throw new LogicException(message: 'Not needed.');
    }
}
