<?php

declare(strict_types = 1);

namespace SlimAurynTest;

use Slim\Interfaces\CallableResolverInterface;

class TestCallableResolver implements CallableResolverInterface
{
    public function resolve($toResolve): callable
    {
        throw new \Exception("resolve not implemented yet.");
    }

}
