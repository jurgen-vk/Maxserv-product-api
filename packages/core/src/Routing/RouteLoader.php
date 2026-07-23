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

class RouteLoader implements LoaderInterface
{
    public function __construct(
        private readonly AttributeDirectoryLoader $attributeLoader,
        private readonly YamlFileLoader $yamlLoader,
    ) {
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        $collection = new RouteCollection();

        foreach ((new Finder())->directories()->in(APPLICATION_ROOT . '/packages')->name('Controller') as $dir) {
            $collection->addCollection($this->attributeLoader->load($dir->getPathname()));
        }

        foreach ((new Finder())->files()->in(APPLICATION_ROOT . '/packages')->name('routes.yaml') as $file) {
            $collection->addCollection($this->yamlLoader->load($file->getPathname()));
        }

        $collection->addResource(new DirectoryResource(APPLICATION_ROOT . '/packages', '/routes\.yaml$/'));

        return $collection;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return true;
    }

    public function setResolver(LoaderResolverInterface $resolver): void
    {
    }

    public function getResolver(): LoaderResolverInterface
    {
        throw new LogicException('Not needed.');
    }
}
