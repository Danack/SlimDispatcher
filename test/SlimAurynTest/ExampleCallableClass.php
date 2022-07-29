<?php

declare(strict_types = 1);

namespace SlimAurynTest;

class ExampleCallableClass
{
    public function __construct()
    {

    }

    public function instanceMethodToCall()
    {
        return "I was called";
    }

    public static function staticMethodToCall()
    {
        return "I was called";
    }
}
