<?php

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Laminas\Diactoros\ResponseFactory;
use Slim\CallableResolver;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\MiddlewareDispatcherInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteResolverInterface;
use SlimDispatcher\AurynCallableResolver;
use SlimDispatcher\SlimAurynInvokerFactory;
use SlimAurynExample\AllRoutesMiddleware;
use SlimDispatcher\ExceptionMiddleware;

error_reporting(E_ALL);

require_once __DIR__ . "/../../vendor/autoload.php";
require_once __DIR__ . '/../factories.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../injectionParams.php';
require_once __DIR__ . '/../routes.php';

set_error_handler('saneErrorHandler');

// Setup the Injector
$injector = new Auryn\Injector();
$injectionParams = injectionParams();
$injectionParams->addToInjector($injector);
$injector->share($injector);

// Add any custom rules you'd like to the injector here, or in
// the injectionParams.php file.

// Create the app with the container set to use SlimAurynInvoker
// for the 'foundHandler'.

//$invoker = $injector->make(SlimAurynInvokerFactory::class);

$callableResolver = new AurynCallableResolver(
    $injector,
    $resultMappers = getResultMappers()
);

//$container['callableResolver'] = $callableResolver;

$app = new \Slim\App(
    /* ResponseFactoryInterface */    $responseFactory = new ResponseFactory(),
    /* ?ContainerInterface */ $container = null,
    /* ?CallableResolverInterface */ $callableResolver,
    /* ?RouteCollectorInterface */ $routeCollector = null,
    /* ?RouteResolverInterface */ $routeResolver = null,
    /* ?MiddlewareDispatcherInterface */ $middlewareDispatcher = null
);

// Configure any middlewares that should be applied to all routes here.
$app->add(new AllRoutesMiddleware());

// Create a middleware that catches all otherwise uncaught application
// level exceptions.
$app->add($injector->make(ExceptionMiddleware::class));

// Add Error Middleware
// $errorMiddleware = $app->addErrorMiddleware(true, true, true);
//$errorMiddleware->setDefaultErrorHandler($customErrorHandler);


// Setup the routes for the app
setupRoutes($app);

// Run!
$app->run();
