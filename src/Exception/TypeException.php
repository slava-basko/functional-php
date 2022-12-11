<?php

namespace Basko\Functional\Exception;

class TypeException extends \Exception
{
    /**
     * @param $value
     * @param string $target
     * @return static
     */
    public static function forValue($value, $target)
    {
        return new self(sprintf('Could not convert "%s" to type "%s"', get_debug_type($value), $target));
    }
}
