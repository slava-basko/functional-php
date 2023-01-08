<?php

namespace Basko\Functional\Exception;

class TypeException extends \Exception
{
    private $target;

    /**
     * @param $value
     * @param string $target
     * @return static
     */
    public static function forValue($value, $target)
    {
        $exception = new self(sprintf('Could not convert "%s" to type "%s"', get_debug_type($value), $target));
        $exception->target = $target;
        return $exception;
    }

    /**
     * @return mixed
     */
    public function getTarget()
    {
        return $this->target;
    }
}
