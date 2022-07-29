<?php

declare(strict_types=1);

namespace SlimAurynTest\ResponseMapper;

//use Slim\Http\Response;
use SlimAurynTest\BaseTestCase;
use SlimAuryn\Response\TextResponse;
use SlimAuryn\ResponseMapper\ResponseMapper;

/**
 * @group wip
 * @coversNothing
 */
class ResponseMapperTest extends BaseTestCase
{
    /**
     * @covers \SlimAuryn\ResponseMapper\ResponseMapper::mapStubResponseToPsr7
     */
    public function testMapStubResponseToPsr7()
    {
        $text = 'This is some text';
        $headers = [
            'foo' => 'bar'
        ];
        $status = 201;

        $textResponse = new TextResponse($text, $headers, $status);

        $responseReturned = ResponseMapper::mapStubResponseToPsr7(
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

        $responseReturned = ResponseMapper::mapStubResponseToPsr7(
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

    public function testMapStubResponseToPsr7WithUnknownCustomStatusCodeThrowsException()
    {
        $this->markTestSkipped("apparently this isn't needed.");
        $customStatusCode = 550;
        $text = 'This is some text';
        $headers = [
            'foo' => 'bar'
        ];

        $textResponse = new TextResponse($text, $headers, $customStatusCode);

        $originalResponse = createResponse();

        // This makes me sad.
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ReasonPhrase must be supplied for this code');

        ResponseMapper::mapStubResponseToPsr7(
            $textResponse,
            $request = createRequestForTesting(),
            $originalResponse
        );
    }

    /**
     * @covers \SlimAuryn\ResponseMapper\ResponseMapper::passThroughResponse
     */
    public function testPassThrough()
    {
        $originalResponse = createResponse();
        $controllerResponse = createResponse();

        $responseReturned = ResponseMapper::passThroughResponse(
            $controllerResponse,
            $originalResponse
        );

        $this->assertSame($controllerResponse, $responseReturned);
        // TODO - we should allow users to define a response copier
        // So that they can copy certain headers across. Maybe.
    }
}
