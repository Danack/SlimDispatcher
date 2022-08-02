<?php

namespace SlimAurynTest;

use SlimAurynTest\BaseTestCase;
use SlimAuryn\RouteParams;
use SlimAuryn\RouteParamsException;
use function SlimAuryn\convertStringToHtmlResponse;

/**
 * @covers ::SlimAuryn\convertStringToHtmlResponse
 */
class FunctionsTest extends BaseTestCase
{
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
}