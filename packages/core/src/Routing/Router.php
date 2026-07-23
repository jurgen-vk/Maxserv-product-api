<?php

declare(strict_types=1);

namespace MaxServ\Core\Routing;

use MaxServ\Core\Render\Error\ErrorRenderer;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router as SymfonyRouter;
use Throwable;

readonly class Router
{
    public function __construct(
        private Container $container,
        private SymfonyRouter $router,
        private ErrorRenderer $errorRenderer,
    ) {
    }

    public function match(): void
    {
        $request = Request::createFromGlobals();
        $format = $request->getPreferredFormat('html');

        try {
            $parameters = $this->router->matchRequest($request);

            [$controllerClass, $action] = explode('::', $parameters['_controller']);
            $controller = $this->container->get($controllerClass);

            $response = $controller->$action($request, $parameters);
        } catch (Throwable $exception) {
            $response = $this->errorRenderer->render($exception, $format);
        }

        $response->send();
    }
}
