<?php

declare(strict_types=1);

namespace MaxServ\Core\Render\Extension;

use Symfony\Component\Asset\PackageInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class AssetExtension extends AbstractExtension
{
    public function __construct(
        private readonly PackageInterface $assets,
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction(name: 'asset', callable: [$this->assets, 'getUrl']),
        ];
    }
}
