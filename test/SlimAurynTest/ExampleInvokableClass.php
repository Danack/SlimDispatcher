<?php

declare(strict_types = 1);

namespace SlimAurynTest;

class ExampleInvokableClass
{
    public function __construct()
    {

    }

    public function __invoke()
    {
        return "I was called";
    }
}
