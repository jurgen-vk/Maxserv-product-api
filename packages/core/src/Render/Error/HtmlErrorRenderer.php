<?php

declare(strict_types=1);

namespace MaxServ\Core\Render\Error;

use MaxServ\Core\Render\TemplateRenderer;
use Symfony\Component\HttpFoundation\Response;
use Twig\Error\LoaderError;

final readonly class HtmlErrorRenderer implements ErrorRendererInterface
{
    public function __construct(
        private TemplateRenderer $templateRenderer,
    ) {}

    public function supports(string $format): bool
    {
        return $format === 'html';
    }

    public function render(int $status, string $message): Response
    {
        try {
            $html = $this->templateRenderer->render(
                template: "error/{$status}.html.twig",
                data: ['code' => $status, 'message' => $message],
            );
        } catch (LoaderError) {
            $html = $this->templateRenderer->render(
                template: 'error/default.html.twig',
                data: ['code' => $status, 'message' => $message],
            );
        }

        return new Response(content: $html, status: $status);
    }
}
