<?php

declare(strict_types=1);

namespace MaxServ\Core\Twig\Extension;

use MaxServ\Core\Twig\ResourceRenderer\IconRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class IconExtension extends AbstractExtension
{
    public function __construct(
        private readonly IconRenderer $iconRenderer,
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                name: 'icon',
                callable: $this->iconRenderer->render(...),
                options: ['is_safe' => ['html']],
            ),
        ];
    }
}