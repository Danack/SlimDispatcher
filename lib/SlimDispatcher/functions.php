<?php

declare(strict_types = 1);

namespace SlimDispatcher;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SlimDispatcher\Response\StubResponse;

// Define a function that writes a string into the response object.
function convertStringToHtmlResponse(string $result, ResponseInterface $response): ResponseInterface
{
    $response = $response->withHeader('Content-Type', 'text/html');
    $response->getBody()->write($result);
    return $response;
}


/**
 * Extract the status, headers and body from a StubResponse and
 * set the values on the PSR7 response
 * @param StubResponse $builtResponse
 * @param Request $request
 * @param ResponseInterface $response
 * @return ResponseInterface
 */
function mapStubResponseToPsr7(
    StubResponse $builtResponse,
    Request $request,
    ResponseInterface $response
) {
    $status = $builtResponse->getStatus();
    $reasonPhrase = getReasonPhrase($status);

    $response = $response->withStatus($builtResponse->getStatus(), $reasonPhrase);
    foreach ($builtResponse->getHeaders() as $key => $value) {
        /** @var \Psr\Http\Message\ResponseInterface $response */
        $response = $response->withHeader($key, $value);
    }
    $response->getBody()->write($builtResponse->getBody());

    return $response;
}

function getReasonPhrase(int $status): string
{
    $customPhrases = [
        420 => 'Enhance your calm',
        512 => 'Server known limitation',
    ];

    if (array_key_exists($status, $customPhrases) === true) {
        return $customPhrases[$status];
    }

    return '';
}


/**
 * Just directly return the PSR7 Response without processing
 * @param ResponseInterface $controllerResult
 * @param ResponseInterface $originalResponse
 * @return ResponseInterface
 */
function passThroughResponse(
    ResponseInterface $controllerResult,
    ResponseInterface $originalResponse
) {
    // TODO - this may be bad. Should we copy things across?
    // Maybe something could be set in the headers?
    return $controllerResult;
}


function wrapCurrentMiddleWare(
    MiddlewareInterface $middleware,
    RequestHandlerInterface $requestHandler
): callable {
    $fn = function (ServerRequestInterface $req) use (
        $middleware,
        $requestHandler
    ) {
        $response = $middleware->process($req, $requestHandler);
//        if ($response instanceof ResponseInterface === false) {
//            throw new UnexpectedValueException(
//                'Middleware must return instance of \Psr\Http\Message\ResponseInterface'
//            );
//        }

        return $response;
    };

    return $fn;
}