<?php

declare(strict_types=1);

namespace MaxServ\Core\Render\Error;

use MaxServ\Core\Render\TemplateRenderer;
use Symfony\Component\HttpFoundation\Response;
use Twig\Error\LoaderError;

class HtmlErrorRenderer implements ErrorRendererInterface
{
    public function __construct(
        private readonly TemplateRenderer $templateRenderer,
    ) {
    }

    public function supports(string $format): bool
    {
        return $format === 'html';
    }

    public function render(int $status, string $message): Response
    {
        try {
            $html = $this->templateRenderer->render("error/{$status}.html.twig", ['code' => $status, 'message' => $message]);
        } catch (LoaderError) {
            $html = $this->templateRenderer->render('error/default.html.twig', ['code' => $status, 'message' => $message]);
        }

        return new Response($html, $status);
    }
}
