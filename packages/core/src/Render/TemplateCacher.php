<?php

declare(strict_types=1);

namespace MaxServ\Core\Render;

use MaxServ\Core\Cache\CachableInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class TemplateCacher implements CachableInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly FilesystemLoader $loader,
        private readonly Filesystem $filesystem,
    ) {
    }

    public function cache(): array
    {
        $warmed = [];

        foreach ($this->loader->getPaths() as $path) {
            foreach ((new Finder())->files()->in($path)->name('*.twig') as $file) {
                $name = ltrim(str_replace($path, '', $file->getPathname()), '/');
                $this->twig->load($name);
                $warmed[] = $name;
            }
        }

        return $warmed;
    }

    public function clear(): void
    {
        $this->filesystem->remove(APPLICATION_ROOT . '/var/cache/twig');
    }
}
