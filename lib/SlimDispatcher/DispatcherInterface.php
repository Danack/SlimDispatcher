<?php

namespace SlimDispatcher;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;


interface DispatcherInterface
{
    public function dispatch_route(Request $request, array $routeArguments, $resolvedCallable);

    public function convert_response_to_html($mapCallable, $result, Request $request, Response $response);
}