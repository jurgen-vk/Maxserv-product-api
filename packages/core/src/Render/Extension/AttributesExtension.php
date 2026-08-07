<?php

declare(strict_types=1);

namespace MaxServ\Core\Render\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class AttributesExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(name: 'attrs', callable: $this->render(...), options: ['is_safe' => ['html']]),
        ];
    }

    private function render(array $defaults, ?array $attributes = null): string
    {
        $attributes ??= [];

        $class = trim(
            string: $this->normalizeClass(class: $defaults['class'] ?? '')
                . ' ' . $this->normalizeClass(class: $attributes['class'] ?? ''),
        );
        $style = trim(
            string: $this->normalizeStyle(style: $defaults['style'] ?? '')
                . '; ' . $this->normalizeStyle(style: $attributes['style'] ?? ''),
            characters: '; ',
        );

        $merged = array_merge($defaults, $attributes, ['class' => $class, 'style' => $style]);

        $parts = [];
        foreach ($merged as $name => $value) {
            if ($value === null || $value === false) {
                continue;
            }
            $name = htmlspecialchars(string: (string) $name, flags: ENT_QUOTES);
            $parts[] = $value === true
                ? $name
                : $name . '="' . htmlspecialchars(string: (string) $value, flags: ENT_QUOTES) . '"';
        }

        return implode(separator: ' ', array: $parts);
    }

    /**
     * Accepts a plain string, a list where each truthy value is a class name
     * (e.g. ['btn', condition ? 'btn--active' : null]), or a map where each
     * truthy value keeps its key as a class name (e.g. {'btn--active': condition}).
     */
    private function normalizeClass(mixed $class): string
    {
        if (!is_array($class)) {
            return (string) $class;
        }

        $parts = [];
        foreach ($class as $key => $value) {
            if ($value === null || $value === false) {
                continue;
            }
            $parts[] = is_int($key) ? (string) $value : $key;
        }

        return implode(separator: ' ', array: $parts);
    }

    /**
     * Accepts a plain string, a list where each truthy value is a complete
     * "property: value" declaration, or a map where each truthy value pairs
     * its key as the CSS property (e.g. {'display': isHidden ? 'none' : null}).
     */
    private function normalizeStyle(mixed $style): string
    {
        if (!is_array($style)) {
            return (string) $style;
        }

        $parts = [];
        foreach ($style as $key => $value) {
            if ($value === null || $value === false) {
                continue;
            }
            $parts[] = is_int($key) ? rtrim(string: (string) $value, characters: ';') : "$key: $value";
        }

        return implode(separator: '; ', array: $parts);
    }
}
