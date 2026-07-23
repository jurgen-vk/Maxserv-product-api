<?php

declare(strict_types=1);

namespace MaxServ\Core\DependencyInjection\Compiler;

use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\Finder\Finder;
use Twig\Loader\FilesystemLoader;

class TwigPathsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $paths = [];
        $corePath = null;

        foreach ((new Finder())->directories()->in(APPLICATION_ROOT . '/packages')->name('templates') as $dir) {
            if (str_starts_with($dir->getPathname(), APPLICATION_ROOT . '/packages/core/')) {
                $corePath = $dir->getPathname();
                continue;
            }
            $paths[] = $dir->getPathname();
        }

        if ($corePath !== null) {
            $paths[] = $corePath;
        }

        $container->getDefinition(FilesystemLoader::class)->setArgument(0, $paths);

        $container->addResource(new DirectoryResource(APPLICATION_ROOT . '/packages', '/\.twig$/'));
    }
}
