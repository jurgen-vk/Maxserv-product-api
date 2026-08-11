<?php

declare(strict_types=1);

namespace MaxServ\Core\Twig\Resolver;

use Symfony\Component\Asset\PackageInterface;

final readonly class ViteAssetResolver
{
    public function __construct(
        private PackageInterface $package,
        private string $basePath,
        private string $serverUrl,
        private bool $devEnabled,
    ) {}

    public function resolve(string $path): string
    {
        $path = $this->basePath . ltrim($path, '/');

        return $this->devEnabled
            ? $this->serverUrl . $path
            : $this->package->getUrl($path);
    }
}
