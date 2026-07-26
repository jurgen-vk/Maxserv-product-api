<?php

declare(strict_types=1);

namespace MaxServ\App\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class HomeController
{
    #[Route(path: '/', name: 'home_index', methods: ['GET'])]
    public function index(Request $request, array $parameters): RedirectResponse
    {
        return new RedirectResponse(url: '/products', status: 301);
    }
}
