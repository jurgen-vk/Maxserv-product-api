<?php

declare(strict_types=1);

namespace MaxServ\Core\Routing;

use MaxServ\Core\Render\Error\ErrorRenderer;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router as SymfonyRouter;
use Throwable;

final readonly class Router
{
    public function __construct(
        private Container $container,
        private SymfonyRouter $router,
        private ErrorRenderer $errorRenderer,
    ) {}

    public function match(Request $request): void
    {
        $format = $request->getPreferredFormat(default: 'html');

        try {
            $parameters = $this->router->matchRequest(request: $request);

            [$controllerClass, $action] = explode(
                separator: '::',
                string: $parameters['_controller'],
            );
            $controller = $this->container->get(id: $controllerClass);

            $response = $controller->$action($request, $parameters);
        } catch (Throwable $exception) {
            $response = $this->errorRenderer->render(exception: $exception, format: $format);
        }

        $response->send();
    }
}
