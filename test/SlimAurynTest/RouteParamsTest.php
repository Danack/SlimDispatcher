<?php

namespace SlimAurynTest;

use SlimAurynTest\BaseTestCase;
use SlimDispatcher\RouteParams;
use SlimDispatcher\RouteParamsException;


/**
 * @covers \SlimDispatcher\RouteParams
 */
class RouteParamsTest extends BaseTestCase
{
    public function testSetInjectorInfo()
    {
        $routeParams = new RouteParams([]);
        $this->expectException(RouteParamsException::class);
        $routeParams->getValue('none_existent_key');
    }

    public function testGetAll()
    {
        $data = [
            'foo' => 'bar',
            'quux' => '123'
        ];

        $routeParams = new RouteParams($data);
        $this->assertSame($data, $routeParams->getAll());

        $this->assertFalse($routeParams->hasValue('unknown_key'));
        $this->assertTrue($routeParams->hasValue('quux'));
        $this->assertSame('123', $routeParams->getValue('quux'));
    }
}