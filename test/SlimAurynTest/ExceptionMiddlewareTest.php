<?php

declare(strict_types=1);

namespace SlimAurynTest;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use SlimAuryn\ExceptionMiddleware;

/**
 * @group wip
 */
class ExceptionMiddlewareTest extends BaseTestCase
{
    // This can't be tested like this.
//    public function testCallableCalledProperly()
//    {
//        $nextFn = function (Request $request, ResponseInterface $response) {
//            return 'Test output';
//        };
//
//
//        $responseString = $this->invokeMiddleware(
//            $nextFn,
//            [],
//            []
//        );
//
//
//        $this->assertEquals('Test output', $responseString);
//    }


    public function testParseErrorMappedProperly()
    {
//        $nextFn = function (Request $request, ResponseInterface $response) {
//            return eval('return $x + 4a');
//        };

        $requestHandler = new class () implements RequestHandler {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return eval('return $x + 4a');
            }
        };

        $message = null;

        $handleException = function (\ParseError $exception/*, ResponseInterface $response*/) use (&$message) {
            $response = createResponse();
            $message = $exception->getMessage();
            $response = $response->withStatus(503);

            return $response;
        };

        $response = $this->processMiddleware(
            $requestHandler,
            [],
            [\ParseError::class => $handleException]
        );

        if (PHP_VERSION_ID > 80000) {
            $this->assertSame(
                'syntax error, unexpected identifier "a", expecting ";"',
                $message
            );
        }
        else {
            $this->assertSame(
                "syntax error, unexpected 'a' (T_STRING), expecting ';'",
                $message
            );
        }

        $this->assertSame(503, $response->getStatusCode());
    }

    public function testExceptionResultConvertedToResponse()
    {
//        $nextFn = function (Request $request, ResponseInterface $response) {
//            throw new MappedException("This is a mapped exception.");
//        };

        $requestHandler = new class () implements RequestHandler {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                throw new MappedException("This is a mapped exception.");
            }
        };

        $convertStringToHtmlResponseFn = function (string $result) {//, ResponseInterface $response) {
            $response = createResponse();
            $response = $response->withHeader('Content-Type', 'text/html');
            $response->getBody()->write($result . " That was mapped to html");
            return $response;
        };

        $resultMappers = [
            'string' => $convertStringToHtmlResponseFn
        ];

        $requestPassedIn = null;

        $exceptionHandlerToResponseList = [
            MappedException::class => function (
                MappedException $mappedException,
                Request $request,
            ) use (&$requestPassedIn) {
                $requestPassedIn = $request;
                return $mappedException->getMessage();
            }
        ];

        $response = $this->processMiddleware(
            $requestHandler,
            $resultMappers,
            $exceptionHandlerToResponseList
        );

        $response->getBody()->rewind();
        $contents = $response->getBody()->getContents();

        $this->assertEquals(
            "This is a mapped exception." . " That was mapped to html",
            $contents
        );
        $this->assertTrue($response->hasHeader('Content-Type'));
        $this->assertEquals('text/html', $response->getHeaderLine('Content-Type'));
    }


    public function testExceptionUnmappedEscapes()
    {
//        $nextFn = function (Request $request, ResponseInterface $response) {
//            throw new UnmappedException("This is an unmapped exception.");
//        };

        $requestHandler = new class () implements RequestHandler {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                throw new UnmappedException("This is an unmapped exception.");
            }
        };

        $convertStringToHtmlResponseFn = function (string $result) {// , ResponseInterface $response) {
            $response = createResponse();
            $response = $response->withHeader('Content-Type', 'text/html');
            $response->getBody()->write($result . " That was mapped to html");
            return $response;
        };

        $resultMappers = [
            'string' => $convertStringToHtmlResponseFn
        ];

        $exceptionMappers = [
            MappedException::class => function (MappedException $mappedException) {
                return $mappedException->getMessage();
            }
        ];

        $this->expectException(UnmappedException::class);
        $this->expectExceptionMessage("This is an unmapped exception.");
        $this->processMiddleware(
            $requestHandler,
            $resultMappers,
            $exceptionMappers
        );
    }

//    public function testAllThreeParametersArePassedCorrectly()
//    {
//        $requestUsed = null;
//        $responseUsed = null;
//
//        $fn = function (
//            MappedException $mappedException,
//            $response,
//            $request
//        ) use (&$requestUsed, &$responseUsed) {
//            $requestUsed = $request;
//            $responseUsed = $response;
//            return $mappedException->getMessage();
//        };
//
//        $exceptionHandlers = [
//            MappedException::class => $fn
//        ];
//
//        $request = $this->createRequest();
//
//        $mapStringToHtmlResponseFn = function (string $result, ResponseInterface $response)
//        {
//            $response = $response->withHeader('Content-Type', 'text/html');
//            $response->getBody()->write($result . " That was mapped to html");
//            return $response;
//        };
//
//        $resultMappers = [
//            'string' => $mapStringToHtmlResponseFn
//        ];
//
//        $exceptionMiddleware = new ExceptionMiddleware(
//            $exceptionHandlers,
//            $resultMappers
//        );
//
//        $requestHandler = new class () implements RequestHandlerInterface {
//            public function handle(ServerRequestInterface $request): ResponseInterface
//            {
//                throw new MappedException("This is a mapped exception.");
//            }
//        };
//
//
//        $response = $exceptionMiddleware->__invoke($request, $requestHandler);
//
//        $this->assertSame($request, $requestUsed);
//        $this->assertSame($response, $responseUsed);
//    }

    public function processMiddleware(
        RequestHandler $requestHandler,
        $resultMappers,
        $exceptionHandlers
    ): Response {
        // $request = $this->createRequest();
        $request = createRequestForTesting();

        $exceptionMiddleware = new ExceptionMiddleware(
            $exceptionHandlers,
            $resultMappers
        );

        return $exceptionMiddleware->process($request, $requestHandler);
    }

//    private function createRequest()
//    {
//        $request = new ServerRequest(
//            $serverParams = [],
//            $uploadedFiles = [],
//            $uri = 'https://user:pass@host:443/path?query',
//            $method = 'GET',
//            $body = 'php://input',
//            $headers = [],
//            $cookies = [],
//            $queryParams = [],
//            $parsedBody = null,
//            $protocol = '1.1'
//        );
//
//        return $request;
//    }
}
