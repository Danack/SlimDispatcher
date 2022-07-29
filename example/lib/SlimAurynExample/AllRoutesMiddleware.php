<?php

declare(strict_types=1);

namespace SlimAurynExample;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * A middleware that is setup for all routes, for testing.
 */
class AllRoutesMiddleware implements MiddlewareInterface
{
    public function process(
        Request $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $response = $handler->handle($request);

        /** @var ResponseInterface $response */
        $response = $response->withAddedHeader('X-all_routes_middleware', 'active');

        return $response;
    }
}
