<?php

include_once __DIR__ . "/../vendor/autoload.php";

require __DIR__ . "/fixtures.php";
require __DIR__ . "/../example/functions.php";
require __DIR__ . "/../example/factories.php";

use Laminas\Diactoros\Request;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\ResponseInterface;

function createRequestForTesting() : ServerRequest
{
//    $request = new Request(
//        'https://user:pass@host:443/path?query',
//        $method = 'GET',
//        $headers = [],
//        $serverParams = []
//    );

    $request = new ServerRequest(
        $serverParams = [],
        $uploadedFiles = [],
        $uri = 'https://user:pass@host:443/path?query',
        $method = 'GET',
        $body = 'php://input',
        $headers = [],
        $cookies = [],
        $queryParams = [],
        $parsedBody = null,
        $protocol = '1.1'
    );


    return $request;
}


//function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
//{
//    $responseFactory = new ResponseFactory();
//    return $responseFactory->createResponse($code, $reasonPhrase);
//}
