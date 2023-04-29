<?php

declare(strict_types = 1);

namespace SlimAurynTest;

use Auryn\Injector;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SlimDispatcher\AurynCallableResolver;
use SlimAurynTest\ExampleCallableClass;
use SlimAurynTest\ExampleInvokableClass;
use SlimDispatcher\Exception\UnresolvableCallableException;
use SlimDispatcher\SlimDispatcherException;
use function SlimDispatcher\convertStringToHtmlResponse;


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

        // anonymous function
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
     * @covers \SlimDispatcher\AurynCallableResolver
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

    /**
     * @covers \SlimDispatcher\AurynCallableResolver::convertStubResponseToFullResponse
     */
    public function testCoversPSR7ResponsePassThrough()
    {
        $custom_status = 301;

        $anonymous_fn_that_returns_response = function () use ($custom_status) {
            return createResponse($custom_status);
        };

        $injector = new Injector();

        $callableResolver = new AurynCallableResolver(
            $injector,
            []
        );

        $resolved_callable = $callableResolver->resolve($anonymous_fn_that_returns_response);
        $this->assertIsCallable($resolved_callable);

        $full_response = $resolved_callable(
            $request = createRequestForTesting(),
            $response = createResponse(),
            $route_arguments = [],
        );

        $this->assertInstanceOf(Response::class, $full_response);

        /** @var Response $full_response */
        $this->assertSame($custom_status, $full_response->getStatusCode());
    }

    public function providesCallableResolveFunctionErrors()
    {
        yield ['this_function_doesnt_exist'];
        yield [new \StdClass()];
        yield [new ExampleCallableClass(), 'this_method_doesnt_exist'];
    }

    /**
     * @param $alleged_callable
     * @covers \SlimDispatcher\AurynCallableResolver
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
        // TODO - exception message need checking
        $callableResolver->resolve($alleged_callable);
    }




    /**
     * @covers \SlimDispatcher\AurynCallableResolver
     */
    public function testCallableReturnsBadType()
    {
        $fn = function () {
            return new \StdClass();
        };

        $injector = new Injector();

        $callableResolver = new AurynCallableResolver(
            $injector,
            []
        );

        $fn = $callableResolver->resolve($fn);

        $this->expectException(SlimDispatcherException::class);
        $this->expectExceptionMessageMatchesTemplateString(
            SlimDispatcherException::UNKNOWN_RESULT_TYPE
        );

        $fn(
          $request = createRequestForTesting(),
          $response = createResponse(),
          $routeArguments = []
        );
    }



    /**
     * @covers \SlimDispatcher\AurynCallableResolver
     */
    public function testCallableNeedsARouteParameter()
    {
        $fn = function (string $name) {
            return "Hello there $name";
        };

        $injector = new Injector();

        $stub_response_mappers = [
            'string' => 'convertStringToHtmlResponse'
        ];

        $callableResolver = new AurynCallableResolver(
            $injector,
            $stub_response_mappers
        );

        $fn = $callableResolver->resolve($fn);

        $routeArguments = [
            'name' => 'John'
        ];

        $full_response = $fn(
            $request = createRequestForTesting(),
            $response = createResponse(),
            $routeArguments
        );

        $this->assertInstanceOf(Response::class, $full_response);

        /** @var $full_response Response::class */
        $body_stream = $full_response->getBody();
        $body_stream->rewind();
        $full_contents = $body_stream->getContents();

        $this->assertSame(
            "Hello there John",
            $full_contents
        );
    }
}
