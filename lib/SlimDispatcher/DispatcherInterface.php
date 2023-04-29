<?php

namespace SlimDispatcher;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;


interface DispatcherInterface
{
    public function do_the_needful(Request $request, array $routeArguments, $resolvedCallable);

    public function do_the_needful2($mapCallable, $result, Request $request, Response $response);
}