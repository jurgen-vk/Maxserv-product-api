<?php

declare(strict_types=1);

namespace MaxServ\Core\Render;

use Twig\Error\LoaderError;

final readonly class FragmentRenderer
{
    public function __construct(
        private TemplateRenderer $templateRenderer,
        private string $defaultRoot = 'pages',
    ) {}

    public function render(string $page, string $fragment, array $data, ?string $root = null): ?string
    {
        $prefix = ($root ?? $this->defaultRoot) . '.' . $page;

        try {
            return $this->templateRenderer->render(
                template: "$prefix.$fragment",
                data: $data,
            );
        } catch (LoaderError $exception) {
            error_log(message: (string)$exception);

            return null;
        }
    }

    public function renderMany(string $page, array $fragments, array $data, ?string $root = null): array
    {
        $rendered = [];
        foreach ($fragments as $fragment) {
            $rendered[$fragment] = $this->render(page: $page, fragment: $fragment, data: $data, root: $root);
        }

        return $rendered;
    }
}