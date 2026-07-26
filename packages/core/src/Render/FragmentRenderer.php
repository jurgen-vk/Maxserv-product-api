<?php

declare(strict_types=1);

namespace MaxServ\Core\Render;

use Twig\Error\LoaderError;

readonly class FragmentRenderer
{
    public function __construct(
        private TemplateRenderer $templateRenderer,
        private string $defaultRoot = 'pages',
    ) {}

    public function render(string $page, array $fragments, array $data, ?string $root = null): array
    {
        $prefix = ($root ?? $this->defaultRoot) . '.' . $page;

        $rendered = [];
        foreach ($fragments as $fragment) {
            try {
                $rendered[$fragment] = $this->templateRenderer->render(
                    template: "$prefix.$fragment",
                    arguments: $data,
                );
            } catch (LoaderError $exception) {
                error_log(message: (string)$exception);
                $rendered[$fragment] = null;
            }
        }

        return $rendered;
    }
}
