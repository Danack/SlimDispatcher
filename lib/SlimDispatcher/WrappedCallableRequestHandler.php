<?php

declare(strict_types = 1);

namespace SlimDispatcher;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class WrappedCallableRequestHandler implements RequestHandlerInterface
{
    /**
     * @param mixed $fn Can't be callable as Auryn supports 'classname::method'
     */
    public function __construct(private mixed /*callable*/ $fn)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return ($this->fn)($request);
    }
}
