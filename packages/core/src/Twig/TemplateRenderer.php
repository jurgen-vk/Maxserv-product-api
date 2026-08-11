<?php

declare(strict_types=1);

namespace MaxServ\Core\Twig;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class TemplateRenderer
{
    public function __construct(
        private Environment $environment,
    ) {}

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function render(string $template, array $data): string
    {
        return $this->environment->render(name: $template, context: $data);
    }
}