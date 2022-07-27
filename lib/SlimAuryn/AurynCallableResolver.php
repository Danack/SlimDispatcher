<?php

declare(strict_types = 1);

namespace SlimAuryn;

use Auryn\Injector;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use RuntimeException;
use Slim\Interfaces\CallableResolverInterface;

class AurynCallableResolver implements CallableResolverInterface
{
    public function __construct(
        private Injector $injector,
        private array $resultMappers
    ) {
    }

    /**
     * Resolve toResolve into a closure that that the router can dispatch.
     *
     * If toResolve is of the format 'class:method', then try to extract 'class'
     * from the container otherwise instantiate it and then dispatch 'method'.
     *
     * @param mixed $toResolve
     *
     * @return callable
     *
     * @throws RuntimeException if the callable does not exist
     * @throws RuntimeException if the callable is not resolvable
     */
    public function resolve($toResolve): callable
    {
        $resolvedCallable = $toResolve;

        if (is_array($toResolve) === true) {
            $class = $toResolve[0];
            $method = $toResolve[1];
            if (class_exists($class) === true) {
                if (method_exists($class, $method) === true) {
                    return $toResolve;
                }
            }
        }

        $fn = function (
            Request $request,
            Response $response,
            array $routeArguments
        ) use ($resolvedCallable) {

            Util::setInjectorInfo($this->injector, $request, $routeArguments);
            // TODO - we could share $response

            $result = $this->injector->execute($resolvedCallable);

            return Util::mapResult(
                $result,
                $request,
                $response,
                $this->resultMappers
            );
        };

        // TODO - the code below is proably also needed.
        return $fn;

        if (!is_callable($toResolve) && is_string($toResolve)) {
            // check for slim callable as "class:method"
            $callablePattern = '!^([^\:]+)\:{1,2}([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$!';
            if (preg_match($callablePattern, $toResolve, $matches)) {
                $class = $matches[1];
                $method = $matches[2];

//                if ($this->container->has($class)) {
//                    $resolved = [$this->container->get($class), $method];
//                }
//                else {
                    if (!class_exists($class)) {
                        throw new RuntimeException(sprintf('Callable %s does not exist', $class));
                    }
                    $resolved = [new $class($this->container), $method];
//                }
            }
            else {
                // check if string is something in the DIC that's callable or is a class name which
                // has an __invoke() method
                $class = $toResolve;
                if ($this->container->has($class)) {
                    $resolved = $this->container->get($class);
                }
                else {
                    if (!class_exists($class)) {
                        throw new RuntimeException(sprintf('Callable %s does not exist', $class));
                    }
                    $resolved = new $class($this->container);
                }
            }
        }

        if (!is_callable($resolved)) {
            throw new RuntimeException(sprintf(
                '%s is not resolvable',
                is_array($toResolve) || is_object($toResolve) ? json_encode($toResolve) : $toResolve
            ));
        }

        return $resolved;
    }
}
