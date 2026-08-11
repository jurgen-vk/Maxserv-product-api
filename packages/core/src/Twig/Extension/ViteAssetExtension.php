<?php

declare(strict_types=1);

namespace MaxServ\Core\Twig\Extension;

use MaxServ\Core\Twig\Resolver\ViteAssetResolver;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ViteAssetExtension extends AbstractExtension
{
    public function __construct(
        private readonly ViteAssetResolver $resolver,
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction(name: 'vite_asset', callable: $this->resolver->resolve(...)),
        ];
    }
}
