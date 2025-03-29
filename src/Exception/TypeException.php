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
        $exception = new static(\sprintf('Could not convert "%s" to type "%s"', \get_debug_type($value), $target));
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

    /**
     * @param mixed $value
     * @param string $type
     * @param string $callee
     * @return void
     * @throws \Basko\Functional\Exception\TypeException
     */
    public static function assertReturnType($value, $type, $callee)
    {
        if (!$value instanceof $type) {
            throw new static(
                \sprintf(
                    '%s(): Return value must be of type %s, %s returned',
                    $callee,
                    $type,
                    \is_object($value) ? \get_class($value) : \get_debug_type($value)
                )
            );
        }
    }

    /**
     * @param mixed $value
     * @param string $type
     * @param string $callee
     * @return void
     * @throws \Basko\Functional\Exception\TypeException
     */
    public static function assertType($value, $type, $callee)
    {
        if (!$value instanceof $type) {
            throw new static(
                \sprintf(
                    '%s(): Parameter must be of type %s, %s given',
                    $callee,
                    $type,
                    \is_object($value) ? \get_class($value) : \get_debug_type($value)
                )
            );
        }
    }

    /**
     * @param mixed $value
     * @param string $type
     * @param string $callee
     * @return void
     * @throws \Basko\Functional\Exception\TypeException
     */
    public static function assertNotSelfType($value, $type, $callee)
    {
        if ($value instanceof $type) {
            throw new static(
                \sprintf(
                    '%s(): argument type is %s, but is should not be %s',
                    $callee,
                    \get_class($value),
                    $type
                )
            );
        }
    }
}
