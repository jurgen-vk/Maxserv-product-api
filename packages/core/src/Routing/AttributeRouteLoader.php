<?php

declare(strict_types=1);

namespace MaxServ\Core\Routing;

use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Routing\Loader\AttributeClassLoader;
use Symfony\Component\Routing\Route;

class AttributeRouteLoader extends AttributeClassLoader
{
    protected function configureRoute(Route $route, ReflectionClass $class, ReflectionMethod $method, object $attr): void
    {
        $route->setDefault('_controller', $class->getName() . '::' . $method->getName());
    }
}
