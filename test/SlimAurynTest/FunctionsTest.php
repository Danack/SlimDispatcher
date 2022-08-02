<?php

namespace SlimAurynTest;

use SlimAuryn\Response\TextResponse;
use SlimAurynTest\BaseTestCase;
use SlimAuryn\RouteParams;
use SlimAuryn\RouteParamsException;
use function SlimAuryn\convertStringToHtmlResponse;
use function SlimAuryn\mapStubResponseToPsr7;
use function SlimAuryn\passThroughResponse;
use function SlimAuryn\getReasonPhrase;

/**
 * @coversNothing
 */
class FunctionsTest extends BaseTestCase
{
    /**
     * @covers ::SlimAuryn\convertStringToHtmlResponse
     */
    public function testSetInjectorInfo()
    {
        $string = "Hello world!";
        $response = createResponse();
        $response_written = convertStringToHtmlResponse($string, $response);

        $this->assertTrue($response_written->hasHeader('Content-Type'));
        $headersForKey = $response_written->getHeader('Content-Type');
        $this->assertCount(1, $headersForKey);
        $header = $headersForKey[0];
        $this->assertSame('text/html', $header);
    }

    /**
     * @covers ::SlimAuryn\getReasonPhrase
     */
    public function testGetReasonPhrase()
    {
        $result = getReasonPhrase(555);
        $this->assertSame('', $result);

        $result = getReasonPhrase(420);
        $this->assertSame('Enhance your calm', $result);
    }

    /**
     * @covers ::\SlimAuryn\mapStubResponseToPsr7
     */
    public function testMapStubResponseToPsr7()
    {
        $text = 'This is some text';
        $headers = [
            'foo' => 'bar'
        ];
        $status = 201;

        $textResponse = new TextResponse($text, $headers, $status);

        $responseReturned = mapStubResponseToPsr7(
            $textResponse,
            $request = createRequestForTesting(),
            $originalResponse = createResponse()
        );

        $this->assertSame($status, $responseReturned->getStatusCode());
        $responseReturned->getBody()->rewind();
        $this->assertSame($text, $responseReturned->getBody()->getContents());

        $this->assertTrue($responseReturned->hasHeader('foo'));
        $this->assertSame('bar', $responseReturned->getHeaderLine('foo'));
    }

    public function providesMapStubResponseToPsr7WithCustomStatusCodeWorks()
    {
        return [
            [420, 'Enhance your calm'],
            [512, 'Server known limitation'],
        ];
    }

    /**
     * @dataProvider providesMapStubResponseToPsr7WithCustomStatusCodeWorks
     * @covers ::\SlimAuryn\mapStubResponseToPsr7
     */
    public function testMapStubResponseToPsr7WithCustomStatusCodeWorks(
        int $customStatusCode,
        string $customReasonPhrase
    ) {
        $originalResponse = createResponse();

        $text = 'This is some text';
        $headers = [
            'foo' => 'bar'
        ];

        $textResponse = new TextResponse($text, $headers, $customStatusCode);

        $responseReturned = mapStubResponseToPsr7(
            $textResponse,
            $request = createRequestForTesting(),
            $originalResponse
        );

        $this->assertSame($customStatusCode, $responseReturned->getStatusCode());
        $responseReturned->getBody()->rewind();
        $this->assertSame($text, $responseReturned->getBody()->getContents());

        $this->assertTrue($responseReturned->hasHeader('foo'));
        $this->assertSame('bar', $responseReturned->getHeaderLine('foo'));

        $this->assertSame($customReasonPhrase, $responseReturned->getReasonPhrase());
    }

    // TODO - delete after test coverage is 100%
//    public function testMapStubResponseToPsr7WithUnknownCustomStatusCodeThrowsException()
//    {
//        $this->markTestSkipped("apparently this isn't needed.");
//        $customStatusCode = 550;
//        $text = 'This is some text';
//        $headers = [
//            'foo' => 'bar'
//        ];
//
//        $textResponse = new TextResponse($text, $headers, $customStatusCode);
//
//        $originalResponse = createResponse();
//
//        // This makes me sad.
//        $this->expectException(\InvalidArgumentException::class);
//        $this->expectExceptionMessage('ReasonPhrase must be supplied for this code');
//
//        ResponseMapper::mapStubResponseToPsr7(
//            $textResponse,
//            $request = createRequestForTesting(),
//            $originalResponse
//        );
//    }

    /**
     * @covers ::\SlimAuryn\passThroughResponse
     */
    public function testPassThrough()
    {
        $originalResponse = createResponse();
        $controllerResponse = createResponse();

        $responseReturned = passThroughResponse(
            $controllerResponse,
            $originalResponse
        );

        $this->assertSame($controllerResponse, $responseReturned);
        // TODO - we should allow users to define a response copier
        // So that they can copy certain headers across. Maybe.
    }
}