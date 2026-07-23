<?php

declare(strict_types=1);

namespace MaxServ\Core\Render\Error;

use LogicException;
use MaxServ\Core\Http\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Throwable;

readonly class ErrorRenderer
{
    /** @param iterable<ErrorRendererInterface> $renderers */
    public function __construct(
        private iterable $renderers,
        private bool $debug,
    ) {
    }

    public function render(Throwable $exception, string $format): Response
    {
        [$status, $message] = $this->resolve($exception);

        $renderer = $this->findSupporting($format) ?? $this->findSupporting('html');

        if ($renderer === null) {
            throw new LogicException('No HTML error renderer registered.');
        }

        return $renderer->render($status, $message);
    }

    private function resolve(Throwable $exception): array
    {
        return match (true) {
            $exception instanceof HttpException => [$exception->getStatusCode(), $exception->getMessage()],
            $exception instanceof ResourceNotFoundException => [404, $exception->getMessage()],
            $exception instanceof MethodNotAllowedException => [405, $exception->getMessage()],
            $this->debug => [500, (string) $exception],
            default => [500, 'Something went wrong'],
        };
    }

    private function findSupporting(string $format): ?ErrorRendererInterface
    {
        foreach ($this->renderers as $renderer) {
            if ($renderer->supports($format)) {
                return $renderer;
            }
        }

        return null;
    }
}
