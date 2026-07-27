<?php

declare(strict_types=1);

namespace MaxServ\Core\DependencyInjection\Compiler;

use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;
use Twig\Loader\FilesystemLoader;

final readonly class TwigPathsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $corePrefix = APPLICATION_ROOT . '/packages/core/';
        $paths = [];
        $corePaths = [];

        $dirs = (new Finder())
            ->directories()
            ->in(dirs: APPLICATION_ROOT . '/packages')
            ->name(patterns: 'templates');

        foreach ($dirs as $dir) {
            $path = $dir->getPathname();
            if (str_starts_with($path, $corePrefix)) {
                $corePaths[] = $path;
            } else {
                $paths[] = $path;
            }
        }

        $paths = [...$paths, ...$corePaths];

        $container
            ->getDefinition(id: FilesystemLoader::class)
            ->setArgument(key: 0, value: $paths);

        $container->addResource(
            resource: new DirectoryResource(
                resource: APPLICATION_ROOT . '/packages',
                pattern: '/\.twig$/',
            ),
        );
    }
}
