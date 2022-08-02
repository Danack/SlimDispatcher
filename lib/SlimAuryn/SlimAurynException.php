<?php

namespace SlimAuryn;

class SlimAurynException extends \Exception
{
    const UNKNOWN_RESULT_TYPE = 'Resolved callable returned [%s] which is not a type known to the resultMappers.';

    public static function unknownResultType($result)
    {
        // Unknown result type, throw an exception
        $type = gettype($result);
        if ($type === "object") {
            $type = "object of type " . get_class($result);
        }

        $message = sprintf(
            self::UNKNOWN_RESULT_TYPE,
            $type
        );
        return new self($message);
    }
}
