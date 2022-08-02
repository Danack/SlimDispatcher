<?php

namespace SlimAurynTest;

use SlimAuryn\Response\TextResponse;

use SlimAuryn\SlimAurynException;

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
     * @covers \SlimAuryn\SlimAurynException
     * @dataProvider providesWorks
     */
    public function testWorks($type, $string_to_check)
    {
        $exception = SlimAurynException::unknownResultType($type);

        $this->assertStringMatchesTemplateString(
            SlimAurynException::UNKNOWN_RESULT_TYPE,
            $exception->getMessage()
        );

        $this->assertStringContainsString(
            $string_to_check,
            $exception->getMessage()
        );
    }
}