<?php

declare(strict_types=1);

namespace MaxServ\Core\Render\Error;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class JsonErrorRenderer implements ErrorRendererInterface
{
    public function supports(string $format): bool
    {
        return $format === 'json';
    }

    public function render(int $status, string $message): Response
    {
        return new JsonResponse(data: ['error' => $message], status: $status);
    }
}
