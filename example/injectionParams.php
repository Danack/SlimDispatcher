<?php

use SlimDispatcher\InjectionParams;

function injectionParams(): InjectionParams
{
    // These classes will only be created once by the injector.
    $shares = [
        \Twig\Environment::class,
        \Auryn\Injector::class,
        \SlimDispatcher\RouteMiddlewares::class,
    ];

    // Alias interfaces (or classes) to the actual types that should be used
    // where they are required.
    $aliases = [
//        Dijon\VariableMap::class => Dijon\VariableMap\Psr7VariableMap::class,
    ];

    // Delegate the creation of types to callables.
    $delegates = [
        Psr\Log\LoggerInterface::class => 'createLogger',
        Twig\Environment::class => 'createTwigForSite',
//        SlimAuryn\SlimAurynInvokerFactory::class => 'createSlimAurynInvokerFactory',

        SlimDispatcher\ExceptionMiddleware::class => 'createExceptionMiddleware',
    ];

    // Define some params that can be injected purely by name.
    $params = [];

    $prepares = [
    ];

    $defines = [];

    $injectionParams = new InjectionParams(
        $shares,
        $aliases,
        $delegates,
        $params,
        $prepares,
        $defines
    );

    return $injectionParams;
}
