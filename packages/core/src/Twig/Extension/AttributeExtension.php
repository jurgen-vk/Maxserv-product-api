<?php

declare(strict_types=1);

namespace MaxServ\Core\Twig\Extension;

use MaxServ\Core\Twig\Utility\AttributeUtility;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class AttributeExtension extends AbstractExtension
{
    public function __construct(
        private readonly AttributeUtility $attributes,
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction(name: 'attrs', callable: $this->attributes->merge(...), options: ['is_safe' => ['html']]),
        ];
    }
}
