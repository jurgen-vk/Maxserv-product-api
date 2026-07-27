<?php

declare(strict_types=1);

namespace MaxServ\Core\Container;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\Filesystem\Filesystem;

final readonly class ContainerCacher
{
    private const string CLASS_NAME = 'CachedContainer';

    public function __construct(
        private bool $debug,
    ) {}

    public function load(): ?Container
    {
        $cache = new ConfigCache(file: $this->file(), debug: $this->debug);

        if (!$cache->isFresh()) {
            return null;
        }

        require_once $cache->getPath();

        return new (self::CLASS_NAME)();
    }

    public function persist(ContainerBuilder $container): void
    {
        $dumper = new PhpDumper(container: $container);

        (new ConfigCache(file: $this->file(), debug: $this->debug))->write(
            content: $dumper->dump(options: ['class' => self::CLASS_NAME]),
            metadata: $container->getResources(),
        );
    }

    public function clear(): void
    {
        (new Filesystem())->remove(files: APPLICATION_ROOT . '/var/cache/container');
    }

    private function file(): string
    {
        return APPLICATION_ROOT . '/var/cache/container/' . self::CLASS_NAME . '.php';
    }
}
