<?php

namespace Basko\Functional\Exception;

class InvalidArgumentException extends \InvalidArgumentException
{
    const ALL = 99;

    /**
     * @param mixed $callback
     * @param string $callee
     * @param int $parameterPosition
     * @return void
     * @throws InvalidArgumentException
     */
    public static function assertCallable($callback, $callee, $parameterPosition)
    {
        if (is_callable($callback)) {
            return;
        }

        if (!is_array($callback) && !is_string($callback)) {
            throw new static(
                \sprintf(
                    '%s() expected parameter %d to be a valid callback, no array, string, closure or functor given',
                    $callee,
                    $parameterPosition
                )
            );
        }

        $type = gettype($callback);
        switch ($type) {
            case 'array':
                $type = 'method';
                $callback = array_values($callback);

                $sep = '::';
                if (is_object($callback[0])) {
                    $callback[0] = get_class($callback[0]);
                    $sep = '->';
                }

                $callback = implode($sep, $callback);
                break;

            default:
                $type = 'function';
                break;
        }

        throw new static(
            sprintf(
                "%s() expects parameter %d to be a valid callback, %s '%s' not found or invalid %s name",
                $callee,
                $parameterPosition,
                $type,
                $callback,
                $type
            )
        );
    }

    /**
     * @param callable[] $listOfCallables
     * @param string $callee
     * @param int $parameterPosition
     * @return void
     * @throws InvalidArgumentException
     */
    public static function assertListOfCallables($listOfCallables, $callee, $parameterPosition)
    {
        foreach ($listOfCallables as $index => $possiblyCallable) {
            try {
                InvalidArgumentException::assertCallable($possiblyCallable, __FUNCTION__, $index);
            } catch (InvalidArgumentException $invalidArgumentException) {
                if ($parameterPosition === static::ALL) {
                    throw new static(
                        sprintf(
                            '%s() expects all parameters to be "callable"',
                            $callee
                        ),
                        0,
                        $invalidArgumentException
                    );
                } else {
                    throw new static(
                        sprintf(
                            '%s() expects parameter %d to be "callable[]"',
                            $callee,
                            $parameterPosition
                        ),
                        0,
                        $invalidArgumentException
                    );
                }
            }
        }
    }

    /**
     * @param mixed $list
     * @param string $callee
     * @param int $parameterPosition
     * @return void
     * @throws static
     */
    public static function assertList($list, $callee, $parameterPosition)
    {
        self::assertListAlike($list, 'Traversable', $callee, $parameterPosition);
    }

    /**
     * @param mixed $collection
     * @param string $callee
     * @param int $parameterPosition
     * @return void
     * @throws static
     */
    public static function assertArrayAccess($collection, $callee, $parameterPosition)
    {
        self::assertListAlike($collection, 'ArrayAccess', $callee, $parameterPosition);
    }

    /**
     * @param mixed $methodName
     * @param string $callee
     * @param int $parameterPosition
     * @return void
     * @throws static
     */
    public static function assertMethodName($methodName, $callee, $parameterPosition)
    {
        if (!is_string($methodName)) {
            throw new static(
                sprintf(
                    '%s() expects parameter %d to be string, %s given',
                    $callee,
                    $parameterPosition,
                    self::getType($methodName)
                )
            );
        }
    }

    /**
     * @param mixed $propertyName
     * @param string $callee
     * @param int $parameterPosition
     * @return void
     * @throws static
     */
    public static function assertPropertyName($propertyName, $callee, $parameterPosition)
    {
        if (
            !is_string($propertyName) &&
            !is_int($propertyName) &&
            !is_float($propertyName) &&
            !is_null($propertyName)
        ) {
            throw new static(
                sprintf(
                    '%s() expects parameter %d to be a valid property name or array index, %s given',
                    $callee,
                    $parameterPosition,
                    self::getType($propertyName)
                )
            );
        }
    }

    /**
     * @param mixed $value
     * @param string $callee
     * @param int $parameterPosition
     * @return void
     * @throws static
     */
    public static function assertPositiveInteger($value, $callee, $parameterPosition)
    {
        if ((string)(int)$value !== (string)$value || $value < 0) {
            $type = self::getType($value);
            $type = $type === 'integer' ? 'negative integer' : $type;

            throw new static(
                sprintf(
                    '%s() expects parameter %d to be positive integer, %s given',
                    $callee,
                    $parameterPosition,
                    $type
                )
            );
        }
    }

    /**
     * @param mixed $key
     * @param string $callee
     * @return void
     * @throws static
     */
    public static function assertValidArrayKey($key, $callee)
    {
        $keyTypes = ['NULL', 'string', 'integer', 'double', 'boolean'];

        $keyType = gettype($key);

        if (!in_array($keyType, $keyTypes, true)) {
            throw new static(
                sprintf(
                    '%s(): callback returned invalid array key of type "%s". Expected %4$s or %3$s',
                    $callee,
                    $keyType,
                    array_pop($keyTypes),
                    implode(', ', $keyTypes)
                )
            );
        }
    }

    /**
     * @param mixed $collection
     * @param mixed $key
     * @param string $callee
     * @return void
     * @throws static
     */
    public static function assertArrayKeyExists($collection, $key, $callee)
    {
        if (!isset($collection[$key])) {
            throw new static(
                sprintf(
                    '%s(): unknown key "%s"',
                    $callee,
                    $key
                )
            );
        }
    }

    /**
     * @param mixed $value
     * @param string $callee
     * @param int $parameterPosition
     * @return void
     * @throws static
     */
    public static function assertBoolean($value, $callee, $parameterPosition)
    {
        if (!is_bool($value)) {
            throw new static(
                sprintf(
                    '%s() expects parameter %d to be boolean, %s given',
                    $callee,
                    $parameterPosition,
                    self::getType($value)
                )
            );
        }
    }

    /**
     * @param mixed $value
     * @param string $callee
     * @param int $parameterPosition
     * @return void
     * @throws static
     */
    public static function assertInteger($value, $callee, $parameterPosition)
    {
        if (!is_int($value)) {
            throw new static(
                sprintf(
                    '%s() expects parameter %d to be integer, %s given',
                    $callee,
                    $parameterPosition,
                    self::getType($value)
                )
            );
        }
    }

    /**
     * @param mixed $value
     * @param int $limit
     * @param string $callee
     * @param int $parameterPosition
     * @return void
     * @throws static
     */
    public static function assertIntegerGreaterThanOrEqual($value, $limit, $callee, $parameterPosition)
    {
        if (!is_int($value) || $value < $limit) {
            throw new static(
                sprintf(
                    '%s() expects parameter %d to be an integer greater than or equal to %d',
                    $callee,
                    $parameterPosition,
                    $limit
                )
            );
        }
    }

    /**
     * @param mixed $value
     * @param int $limit
     * @param string $callee
     * @param int $parameterPosition
     * @return void
     * @throws static
     */
    public static function assertIntegerLessThanOrEqual($value, $limit, $callee, $parameterPosition)
    {
        if (!is_int($value) || $value > $limit) {
            throw new static(
                sprintf(
                    '%s() expects parameter %d to be an integer less than or equal to %d',
                    $callee,
                    $parameterPosition,
                    $limit
                )
            );
        }
    }

    /**
     * @param array<array-key, mixed> $args
     * @param int $position
     * @return void
     * @throws static
     */
    public static function assertResolvablePlaceholder(array $args, $position)
    {
        if (count($args) === 0) {
            throw new static(
                sprintf('Cannot resolve parameter placeholder at position %d. Parameter stack is empty.', $position)
            );
        }
    }

    /**
     * @param mixed $list
     * @param string $className
     * @param string $callee
     * @param int $parameterPosition
     * @return void
     * @throws static
     */
    private static function assertListAlike($list, $className, $callee, $parameterPosition)
    {
        if (!is_array($list) && !$list instanceof $className && !is_object($list)) {
            throw new static(
                sprintf(
                    '%s() expects parameter %d to be array or instance of %s, %s given',
                    $callee,
                    $parameterPosition,
                    $className,
                    self::getType($list)
                )
            );
        }
    }

    /**
     * @param mixed $value
     * @param string $callee
     * @return void
     * @throws static
     */
    public static function assertNonZeroInteger($value, $callee)
    {
        if (!is_int($value) || $value == 0) {
            throw new static(sprintf('%s expected parameter %d to be non-zero', $callee, $value));
        }
    }

    /**
     * @param mixed $pair
     * @param string $callee
     * @param int $position
     * @return void
     * @throws static
     */
    public static function assertPair($pair, $callee, $position)
    {
        if (!(is_array($pair) || $pair instanceof \ArrayAccess) || !isset($pair[0], $pair[1])) {
            throw new static(\sprintf(
                '%s() expects parameter %d to be a pair (array with two elements)',
                $callee,
                $position
            ));
        }
    }

    /**
     * @param mixed $value
     * @param string $callee
     * @param int $parameterPosition
     * @return void
     * @throws static
     */
    public static function assertString($value, $callee, $parameterPosition)
    {
        if (
            !is_string($value)
            && !is_numeric($value)
            && !(is_object($value) && method_exists($value, '__toString'))
        ) {
            throw new static(
                sprintf(
                    '%s() expects parameter %d to be string, %s given',
                    $callee,
                    $parameterPosition,
                    self::getType($value)
                )
            );
        }
    }

    /**
     * @param mixed $value
     * @return string
     */
    private static function getType($value)
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }
}
