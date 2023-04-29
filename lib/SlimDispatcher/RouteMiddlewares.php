<?php

declare(strict_types=1);

namespace SlimDispatcher;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use UnexpectedValueException;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;



class RouteMiddlewares
{
    /**
     * @var MiddlewareInterface[]
     */
    private array $middlewareList = [];

    public function addMiddleware($middleware)
    {
        $this->middlewareList[] = $middleware;
    }

    /**
     * @param callable $callable The final callable that serves the request.
     * @param ServerRequestInterface $request The request
     * @return ResponseInterface
     */
    public function execute(
        callable $callable,
        ServerRequestInterface $request
    ) {
        $currentCallable = $callable;

        // need to wrap $callable in a requestHandlerInterface
        $callableWrappedIntoRequestHandler = new WrappedCallableRequestHandler($callable);

        foreach ($this->middlewareList as $middleware) {
            $middlewareCallable = wrapCurrentMiddleWare($middleware, $callableWrappedIntoRequestHandler);
            $currentCallable = $middlewareCallable;
        }

        $result = call_user_func(
            $currentCallable,
            $request
        );

        if ($result instanceof ResponseInterface === false) {
            throw new UnexpectedValueException(
                'Middleware must return instance of \Psr\Http\Message\ResponseInterface'
            );
        }

        return $result;
    }
}
