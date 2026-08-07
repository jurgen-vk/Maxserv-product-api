<?php

declare(strict_types=1);

namespace MaxServ\Core\Render\Error;

use LogicException;
use MaxServ\Core\Http\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Throwable;

final readonly class ErrorRenderer
{
    /** @param iterable<ErrorRendererInterface> $renderers */
    public function __construct(
        private iterable $renderers,
        private bool $debug,
    ) {}

    public function render(Throwable $exception, string $format): Response
    {
        $status = $this->statusOf(exception: $exception);

        if ($status >= 500) {
            error_log(message: (string) $exception);
        }

        $message = match (true) {
            $status < 500 => $exception->getMessage(),
            $this->debug => (string) $exception,
            default => 'Something went wrong',
        };

        $renderer = $this->findSupporting(format: $format)
            ?? $this->findSupporting(format: 'html');

        if ($renderer === null) {
            throw new LogicException(message: 'No HTML error renderer registered.');
        }

        return $renderer->render(status: $status, message: $message);
    }

    private function statusOf(Throwable $exception): int
    {
        return match (true) {
            $exception instanceof HttpException => $exception->getStatusCode(),
            $exception instanceof ResourceNotFoundException => 404,
            $exception instanceof MethodNotAllowedException => 405,
            default => 500,
        };
    }

    private function findSupporting(string $format): ?ErrorRendererInterface
    {
        return array_find(
            array: iterator_to_array($this->renderers),
            callback: static fn($renderer) => $renderer->supports(format: $format),
        );
    }
}
