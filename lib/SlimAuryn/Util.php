<?php

declare(strict_types=1);

namespace SlimAuryn;

use Auryn\Injector;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Laminas\Diactoros\Response as LaminasResponse;

/**
 * Class Util
 * This class only exists because PHP doesn't have function autoloading.
 */
class Util
{

    public static function mapResultWithNewResponse(
        mixed $result,
        Request $request,
        array $stubResponseToPSR7ResponseHandlerList
    ): Response {
        $response = new LaminasResponse();
        return self::mapResult(
            $result,
            $request,
            $response,
            $stubResponseToPSR7ResponseHandlerList
        );
    }

    public static function mapResult(
        mixed $result,
        Request $request,
        Response $response,
        array $stubResponseToPSR7ResponseHandlerList
    ): Response {
        // Test each of the result mapper, and use an appropriate one.
        foreach ($stubResponseToPSR7ResponseHandlerList as $type => $mapCallable) {
            if ((is_object($result) && $result instanceof $type) ||
                gettype($result) === $type) {
                return $mapCallable($result, $request, $response);
            }
        }

        // Allow PSR responses to just be passed back.
        // This is after the responseHandlerList is processed, to
        // allow custom handlers for specfic types to take precedence.
        if ($result instanceof Response) {
            return $result;
        }

        // Unknown result type, throw an exception
        $type = gettype($result);
        if ($type === "object") {
            $type = "object of type ". get_class($result);
        }
        $message = sprintf(
            'Resolved callable returned [%s] which is not a type known to the resultMappers.',
            $type
        );
        throw new SlimAurynException($message);
    }

    public static function setInjectorInfo(
        Injector $injector,
        Request $request,
        array $routeArguments
    ): void {
        $injector->alias(Request::class, get_class($request));
        $injector->share($request);
        foreach ($routeArguments as $key => $value) {
            $injector->defineParam($key, $value);
        }

        $routeParams = new RouteParams($routeArguments);
        $injector->share($routeParams);
    }
}
