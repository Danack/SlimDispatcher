<?php

declare(strict_types = 1);

namespace SlimAuryn;

use Auryn\Injector;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use RuntimeException;
use Slim\Interfaces\CallableResolverInterface;
use SlimAuryn\Exception\UnresolvableCallableException;

class AurynCallableResolver implements CallableResolverInterface
{
    public function __construct(
        private Injector $injector,
        private array $resultMappers
    ) {
    }

    private function wrapCallableWithResultMappers($resolvedCallable)
    {
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
                $this->resultMappers,
                $this->injector
            );
        };

        return $fn;
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
        if ($toResolve instanceof \Closure) {
            return $this->wrapCallableWithResultMappers($toResolve);
        }

        if (is_callable($toResolve)) {
            return $this->wrapCallableWithResultMappers($toResolve);
        }

        if (is_string($toResolve) !== true) {
            throw new UnresolvableCallableException(sprintf(
                '%s is not resolvable',
                is_array($toResolve) || is_object($toResolve) ? json_encode($toResolve) : $toResolve
            ));
        }

        if (class_exists($toResolve) === true) {
            if (method_exists($toResolve, '__invoke') === true) {
                return $this->wrapCallableWithResultMappers([$toResolve, '__invoke']);
            }
        }

        $parts = explode('::', $toResolve);

        if (count($parts) === 2) {
            $class_name = $parts[0];
            $method_name = $parts[1];

            if (class_exists($class_name) === true) {
                if (method_exists($class_name, $method_name) === true) {
                    return $this->wrapCallableWithResultMappers($toResolve);
                }
            }
        }

        throw new UnresolvableCallableException(sprintf(
            '%s is not resolvable',
            is_array($toResolve) || is_object($toResolve) ? json_encode($toResolve) : $toResolve
        ));
    }
}
