<?php

declare(strict_types=1);

namespace SlimAurynExample;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * A null middleware for testing.
 * Person_1) This doesn't do anything.
 * Person_2) No, it does NOTHING!
 */
class NullMiddleware implements MiddlewareInterface
{
    private $wasCalled = false;

    public function process(
        Request $request,
        RequestHandler $requestHandler
    ): Response {
        $this->wasCalled = true;

        $response = $requestHandler->handle($request);

        return $response;
    }

    /**
     * @return bool
     */
    public function wasCalled(): bool
    {
        return $this->wasCalled;
    }
}
