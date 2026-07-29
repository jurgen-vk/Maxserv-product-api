<?php

declare(strict_types=1);

namespace MaxServ\Core\Http\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RequestContext;

final readonly class RequestFactory
{
    public function __construct(
        private Session $session,
        private RequestStack $requestStack,
        private RequestContext $requestContext,
    ) {}

    public function createFromGlobals(): Request
    {
        $request = Request::createFromGlobals();
        $request->setSession(session: $this->session);
        $this->requestStack->push(request: $request);
        $this->requestContext->fromRequest(request: $request);

        return $request;
    }
}