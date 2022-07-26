<?php

declare(strict_types = 1);

namespace SlimAuryn;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class WrappedCallableRequestHandler implements RequestHandlerInterface
{
    /**
     * @param $fn callable
     */
    public function __construct(private /*callable*/ $fn)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return ($this->fn)($request);
    }
}
