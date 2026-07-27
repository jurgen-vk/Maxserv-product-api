<?php

declare(strict_types=1);

namespace MaxServ\Core\FileSystem;

use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader;

final class TwigFileSystemLoader extends FilesystemLoader
{
    private const array EXTENSIONS = [
        '.html.twig',
        '.txt.twig',
        '.xml.twig',
        '.json.twig',
        '.js.twig',
        '.css.twig',
    ];

    /**
     * @throws LoaderError
     */
    protected function findTemplate(string $name, bool $throw = true): ?string
    {
        $hasSlashes = str_contains(haystack: $name, needle: '/');

        $result = parent::findTemplate(name: $name, throw: $hasSlashes ? $throw : false);

        if ($result !== null || $hasSlashes) {
            return $result;
        }

        $path = str_replace(search: '.', replace: '/', subject: $name);

        foreach (self::EXTENSIONS as $extension) {
            $result = parent::findTemplate(name: $path . $extension, throw: false);
            if ($result !== null) {
                return $result;
            }
        }

        foreach (self::EXTENSIONS as $extension) {
            $result = parent::findTemplate(name: $path . '/index' . $extension, throw: false);
            if ($result !== null) {
                return $result;
            }
        }

        return parent::findTemplate(name: $name, throw: $throw);
    }
}