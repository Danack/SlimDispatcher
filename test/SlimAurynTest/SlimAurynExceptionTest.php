<?php

namespace SlimAurynTest;

use SlimDispatcher\Response\TextResponse;

use SlimDispatcher\SlimDispatcherException;

/**
 * @coversNothing
 */
class SlimAurynExceptionTest extends BaseTestCase
{
    public function providesWorks()
    {
        yield[new \StdClass(), 'object of type stdClass'];
        yield[[], 'array'];

        yield[5, 'integer'];
        yield[true, 'bool'];
    }

    /**
     * @covers \SlimDispatcher\SlimDispatcherException
     * @dataProvider providesWorks
     */
    public function testWorks($type, $string_to_check)
    {
        $exception = SlimDispatcherException::unknownResultType($type);

        $this->assertStringMatchesTemplateString(
            SlimDispatcherException::UNKNOWN_RESULT_TYPE,
            $exception->getMessage()
        );

        $this->assertStringContainsString(
            $string_to_check,
            $exception->getMessage()
        );
    }
}