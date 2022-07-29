<?php

declare(strict_types = 1);

namespace SlimAurynTest;

use Auryn\Injector;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SlimAuryn\AurynCallableResolver;
use SlimAurynTest\ExampleCallableClass;
use SlimAurynTest\ExampleInvokableClass;
use SlimAuryn\Exception\UnresolvableCallableException;
use function SlimAuryn\convertStringToHtmlResponse;


/**
 * @coversNothing
 */
class AurynCallableResolverTest extends BaseTestCase
{
    public function providesCallableResolveFunctionWorks()
    {
        $anonymous_fn = function () {
            return "I was called";
        };
        $class_with_method_instance = new ExampleCallableClass();
        $class_with_invoke_instance = new ExampleInvokableClass();

        // anonymous function - done.
        yield [$anonymous_fn];

        // Class with method
        yield ['SlimAurynTest\ExampleCallableClass::instanceMethodToCall'];

        // instance with method
        yield [[$class_with_method_instance, 'instanceMethodToCall']];

        // instance with invoke
        yield [$class_with_invoke_instance];

        // class with invoke
        yield ['SlimAurynTest\ExampleInvokableClass'];

        // static class method
        yield ['SlimAurynTest\ExampleCallableClass::staticMethodToCall'];

        // function
        yield ['functionCallableForAurynCallableResolverTest'];
    }

    /**
     * @param $callable mixed, to support classname::method which
     *   isn't technically callable.
     * @covers \SlimAuryn\AurynCallableResolver
     * @dataProvider providesCallableResolveFunctionWorks
     */
    public function testCallableResolveFunctionWorks($callable)
    {
        $injector = new Injector();
        $stub_response_mappers = [
            'string' => 'convertStringToHtmlResponse'
        ];

        $callableResolver = new AurynCallableResolver(
            $injector,
            $stub_response_mappers
        );

        $resolved_callable = $callableResolver->resolve($callable);
        $this->assertIsCallable($resolved_callable);

        $full_response = $resolved_callable(
            $request = createRequestForTesting(),
            $response = createResponse(),
            $route_arguments = [],
        );

        $this->assertInstanceOf(Response::class, $full_response);

        /** @var $full_response Response::class */
        $body_stream = $full_response->getBody();
        $body_stream->rewind();
        $full_contents = $body_stream->getContents();

        $this->assertSame(
            "I was called",
            $full_contents
        );
    }

    public function providesCallableResolveFunctionErrors()
    {
        yield ['this_function_doesnt_exist'];
        yield [new \StdClass()];
        yield [new ExampleCallableClass(), 'this_method_doesnt_exist'];
    }

    /**
     * @param $alleged_callable
     * @covers \SlimAuryn\AurynCallableResolver
     * @dataProvider providesCallableResolveFunctionErrors
     */
    public function testCallableResolveFunctionErrors($alleged_callable)
    {
        $injector = new Injector();

        $callableResolver = new AurynCallableResolver(
            $injector,
            []
        );

        $this->expectException(UnresolvableCallableException::class);
        $callableResolver->resolve($alleged_callable);
    }
}
