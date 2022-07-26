<?php

declare(strict_types=1);

namespace SlimAurynTest\ResponseMapper;

use SlimAurynTest\BaseTestCase;
use SlimAuryn\ResponseMapper\TwigResponseMapper;
use SlimAuryn\Response\TwigResponse;
use Twig\Loader\FilesystemLoader;
use Twig\Environment as TwigEnvironment;

class TwigResponseMapperTest extends BaseTestCase
{
    /**
     * @covers \SlimAuryn\ResponseMapper\TwigResponseMapper
     */
    public function testMapStubResponseToPsr7()
    {
        // The templates are included in order of priority.
        $templatePaths = [
            __DIR__ . '/../../templates'
        ];

        $loader = new FilesystemLoader($templatePaths);
        $twig = new TwigEnvironment($loader, array(
            'cache' => false,
            'strict_variables' => true,
            'debug' => true
        ));

        $twigResponseMapper = new TwigResponseMapper($twig);

        $twigResponse = new TwigResponse(
            'test.html',
            $params = ['foo' => 'bar'],
            $status = 201,
            ['x-foo' => 'bar']
        );

        $originalResponse = createResponse();
        $response = $twigResponseMapper($twigResponse, $originalResponse);
        $this->assertSame($status, $response->getStatusCode());

        $response->getBody()->rewind();
        $bodyString = $response->getBody()->getContents();

        $this->assertStringContainsString('foo was set to bar', $bodyString);

        $this->assertTrue($response->hasHeader('x-foo'));
        $this->assertSame('bar', $response->getHeaderLine('x-foo'));
    }
}