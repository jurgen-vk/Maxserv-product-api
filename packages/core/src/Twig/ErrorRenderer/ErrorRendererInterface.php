<?php

declare(strict_types=1);

namespace MaxServ\Core\Twig\ErrorRenderer;

use Symfony\Component\HttpFoundation\Response;

interface ErrorRendererInterface
{
    public function supports(string $format): bool;

    public function render(int $status, string $message): Response;
}
