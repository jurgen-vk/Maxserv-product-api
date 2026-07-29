<?php

declare(strict_types=1);

DEFINE('APPLICATION_ROOT', dirname(__DIR__));

require_once APPLICATION_ROOT . '/vendor/autoload.php';

use MaxServ\Core\Bootstrap;
use MaxServ\Core\Http\Request\RequestFactory;
use MaxServ\Core\Routing\Router;

$bootstrap = new Bootstrap();
$bootstrap->boot();

$request = $bootstrap->container->get(id: RequestFactory::class)->createFromGlobals();
$bootstrap->container->get(id: Router::class)->match(request: $request);
