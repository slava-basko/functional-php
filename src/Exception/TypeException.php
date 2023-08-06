<?php

namespace Basko\Functional\Exception;

use Exception;

class TypeException extends Exception
{
    /**
     * @var string|null
     */
    private $target = null;

    /**
     * @param string $message
     * @param int $code
     * @param null|\Exception $previous
     */
    final public function __construct($message = "", $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @param mixed $value
     * @param string $target
     * @return static
     */
    public static function forValue($value, $target)
    {
        $exception = new static(sprintf('Could not convert "%s" to type "%s"', get_debug_type($value), $target));
        $exception->target = $target;

        return $exception;
    }

    /**
     * @return string|null
     */
    public function getTarget()
    {
        return $this->target;
    }
}
