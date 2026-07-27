<?php

declare(strict_types=1);

namespace MaxServ\Core\Routing;

use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Routing\Loader\AttributeClassLoader;
use Symfony\Component\Routing\Route;

final class AttributeRouteLoader extends AttributeClassLoader
{
    protected function configureRoute(
        Route $route,
        ReflectionClass $class,
        ReflectionMethod $method,
        object $attr,
    ): void {
        $route->setDefault(
            name: '_controller',
            default: $class->getName() . '::' . $method->getName(),
        );
    }
}
