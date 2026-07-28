<?php

declare(strict_types=1);

namespace MaxServ\Core\Render;

use DOMDocument;
use RuntimeException;
use Throwable;

final readonly class IconRenderer
{
    /** @var list<string> */
    private array $directories;

    public function __construct(
        private string $iconsPath = 'assets/icons',
    ) {
        $this->directories = $this->discoverDirectories();
    }

    /** @return list<string> */
    private function discoverDirectories(): array
    {
        $corePrefix = APPLICATION_ROOT . '/packages/core/';
        $found = glob(
            pattern: APPLICATION_ROOT . "/packages/*/$this->iconsPath",
            flags: GLOB_ONLYDIR,
        ) ?: [];

        $core = [];
        $other = [];

        foreach ($found as $directory) {
            if (str_starts_with(haystack: $directory, needle: $corePrefix)) {
                $core[] = $directory;
            } else {
                $other[] = $directory;
            }
        }

        return [...$other, ...$core];
    }

    public function render(string $name, array $attributes = []): string
    {
        try {
            $svg = file_get_contents(filename: $this->resolve(name: $name));

            if ($svg === false) {
                throw new RuntimeException(message: "Could not read icon: $name");
            }

            return $this->applyAttributes(svg: $svg, attributes: $attributes);
        } catch (Throwable $exception) {
            error_log(message: (string)$exception);

            return '';
        }
    }

    private function resolve(string $name): string
    {
        $relative = str_replace(search: ':', replace: '/', subject: $name);

        foreach ($this->directories as $directory) {
            $path = "$directory/$relative.svg";

            if (is_file(filename: $path)) {
                return $path;
            }
        }

        throw new RuntimeException(message: "Icon not found: $name");
    }

    private function applyAttributes(string $svg, array $attributes): string
    {
        $previous = libxml_use_internal_errors(use_errors: true);

        try {
            $document = new DOMDocument();

            if (!$document->loadXML(source: $svg)) {
                throw new RuntimeException(message: 'Malformed SVG.');
            }
        } finally {
            libxml_use_internal_errors(use_errors: $previous);
        }

        $root = $document->documentElement;

        foreach ($attributes as $attribute => $value) {
            $root->setAttribute(
                qualifiedName: $attribute,
                value: match ($value) {
                    true => 'true',
                    false => 'false',
                    default => (string)$value,
                },
            );
        }

        return $document->saveXML(node: $root);
    }
}