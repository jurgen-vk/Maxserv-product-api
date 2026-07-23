<?php

declare(strict_types=1);

namespace MaxServ\Core\Container;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\Filesystem\Filesystem;

final class ContainerCacher
{
    private const string CLASS_NAME = 'CachedContainer';

    public function __construct(
        private readonly bool $debug,
    ) {
    }

    public function load(): ?Container
    {
        $cache = new ConfigCache($this->file(), $this->debug);

        if (!$cache->isFresh()) {
            return null;
        }

        require_once $cache->getPath();

        $class = self::CLASS_NAME;

        return new $class();
    }

    public function persist(ContainerBuilder $container): void
    {
        $dumper = new PhpDumper($container);

        (new ConfigCache($this->file(), $this->debug))->write(
            $dumper->dump(['class' => self::CLASS_NAME]),
            $container->getResources(),
        );
    }

    public function clear(): void
    {
        (new Filesystem())->remove(APPLICATION_ROOT . '/var/cache/container');
    }

    private function file(): string
    {
        return APPLICATION_ROOT . '/var/cache/container/' . self::CLASS_NAME . '.php';
    }
}
