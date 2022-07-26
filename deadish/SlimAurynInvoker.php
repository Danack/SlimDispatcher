<?php

namespace SlimAuryn;

use Auryn\Injector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

// This is dead as Slim no longer supports it

class SlimAurynInvoker
{
    // TODO - kill $setupCallable
//    const SETUP_ARGUMENT_NAME = 'setupCallable';

    /** @var Injector The injector to use for execution */
    private $injector;

    /** @var array A list of callables that can map known return types
     * into PSR-7 Response type.
     */
    private $resultMappers;

    /** @var RouteMiddlewares  */
    private $routeMiddlewares;

    public function __construct(
        Injector $injector,
        RouteMiddlewares $routeMiddlewares,
        array $resultMappers
    ) {
        $this->injector = $injector;
        $this->routeMiddlewares = $routeMiddlewares;
        $this->resultMappers = $resultMappers;
    }

    public function __invoke(
        $callable,
        ServerRequestInterface $request,
        array $routeArguments
    ) {
        Util::setInjectorInfo($this->injector, $request, $routeArguments);

//        // If the route has a setup callable, call that first.
//        $setupCallable = null;
//        if (($attribute = $request->getAttribute('route')) !== null) {
//            $setupCallable = $attribute->getArgument(self::SETUP_ARGUMENT_NAME, null);
//
//            if ($setupCallable !== null) {
//                $this->injector->execute($setupCallable);
//            }
//        }

//        $callableWrappedIntoRequestHandler = new WrappedCallableRequestHandler($callable);

        // Wrap the route callable so that it can be called by middlewares
        $fn = function (ServerRequestInterface $request) use ($callable) {
            $result = $this->injector->execute($callable);

            return Util::mapResult(
                $result,
                $request,
                $this->resultMappers
            );
        };

        // Dispatch the middlewares.
        $response = $this->routeMiddlewares->execute($fn, $request);

        return $response;
    }
}
