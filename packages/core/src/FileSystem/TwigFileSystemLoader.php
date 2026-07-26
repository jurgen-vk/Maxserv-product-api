<?php

declare(strict_types=1);

namespace MaxServ\Core\FileSystem;

use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader;

class TwigFileSystemLoader extends FilesystemLoader
{
    private const EXTENSIONS = [
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
        $hasSlashes = str_contains($name, '/');

        $result = parent::findTemplate(name: $name, throw: $hasSlashes ? $throw : false);

        if ($result !== null || $hasSlashes) {
            return $result;
        }

        $path = str_replace(search: '.', replace: '/', subject: $name);

        foreach (self::EXTENSIONS as $extension) {
            $result = parent::findTemplate($path . $extension, throw: false);
            if ($result !== null) {
                return $result;
            }
        }

        foreach (self::EXTENSIONS as $extension) {
            $result = parent::findTemplate($path . '/index' . $extension, throw: false);
            if ($result !== null) {
                return $result;
            }
        }

        return parent::findTemplate($name, $throw);
    }
}