<?php

namespace Basko\Functional\Exception;

use ArrayAccess;

final class InvalidArgumentException extends \InvalidArgumentException
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
        if (\is_callable($callback)) {
            return;
        }

        if (!\is_array($callback) && !\is_string($callback)) {
            throw new static(
                \sprintf(
                    '%s() expects parameter %d to be a valid callback, array, string, closure or functor, %s given',
                    $callee,
                    $parameterPosition,
                    self::getType($callback)
                )
            );
        }

        if (\is_array($callback)) {
            $type = 'method';
            $callback = \array_values($callback);

            $sep = '::';
            if (\is_object($callback[0])) {
                $callback[0] = \get_class($callback[0]);
                $sep = '->';
            }

            $callback = \array_map(
                static function ($value) {
                    return (string)$value;
                },
                $callback
            );
            $callback = \implode($sep, $callback);
        } else {
            $type = 'function';
        }

        throw new static(
            \sprintf(
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
     * @param callable[]|null $listOfCallables
     * @param string $callee
     * @param int $parameterPosition
     * @return void
     * @throws InvalidArgumentException
     */
    public static function assertListOfCallables($listOfCallables, $callee, $parameterPosition)
    {
        if (empty($listOfCallables)) {
            throw new static(
                \sprintf(
                    '%s() expects "callable ...$functions"',
                    $callee
                ),
                0
            );
        }

        $listOfCallables = \array_values($listOfCallables);

        foreach ($listOfCallables as $index => $possiblyCallable) {
            try {
                InvalidArgumentException::assertCallable($possiblyCallable, __FUNCTION__, $index);
            } catch (InvalidArgumentException $invalidArgumentException) {
                if ($parameterPosition === static::ALL) {
                    throw new static(
                        \sprintf(
                            '%s() expects all parameters to be "callable"',
                            $callee
                        ),
                        0,
                        $invalidArgumentException
                    );
                } else {
                    throw new static(
                        \sprintf(
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
        if (!\is_string($methodName)) {
            throw new static(
                \sprintf(
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
            !\is_string($propertyName) &&
            !\is_int($propertyName) &&
            !\is_float($propertyName) &&
            !\is_null($propertyName)
        ) {
            throw new static(
                \sprintf(
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
        if (!\is_int($value) || $value < 0) {
            $type = self::getType($value);
            $type = $type === 'integer' ? 'negative integer' : $type;

            throw new static(
                \sprintf(
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

        $keyType = \gettype($key);

        if (!\in_array($keyType, $keyTypes, true)) {
            throw new static(
                \sprintf(
                    '%s(): callback returned invalid array key of type "%s". Expected %4$s or %3$s',
                    $callee,
                    $keyType,
                    \array_pop($keyTypes),
                    \implode(', ', $keyTypes)
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
        if (!\is_bool($value)) {
            throw new static(
                \sprintf(
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
        if (!\is_int($value)) {
            throw new static(
                \sprintf(
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
     * @param string $callee
     * @param int $parameterPosition
     * @return void
     * @throws static
     */
    public static function assertNumeric($value, $callee, $parameterPosition)
    {
        if (!\is_numeric($value)) {
            throw new static(
                \sprintf(
                    '%s() expects parameter %d to be numeric, %s given',
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
        if (!\is_int($value) || $value < $limit) {
            throw new static(
                \sprintf(
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
        if (!\is_int($value) || $value > $limit) {
            throw new static(
                \sprintf(
                    '%s() expects parameter %d to be an integer less than or equal to %d',
                    $callee,
                    $parameterPosition,
                    $limit
                )
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
        if (!\is_array($list) && !$list instanceof $className && !\is_object($list)) {
            throw new static(
                \sprintf(
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
     * @param int $position
     * @return void
     */
    public static function assertNonZeroInteger($value, $callee, $position)
    {
        if (!\is_int($value) || $value == 0) {
            throw new static(\sprintf('%s expected parameter %d to be non-zero', $callee, $position));
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
        if (!(\is_array($pair) || $pair instanceof ArrayAccess) || !isset($pair[0], $pair[1])) {
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
            !\is_string($value)
            && !\is_numeric($value)
            && !(\is_object($value) && \method_exists($value, '__toString'))
        ) {
            throw new static(
                \sprintf(
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
     * @param string $callee
     * @param int $parameterPosition
     * @return void
     * @throws self
     */
    public static function assertNotEmptyString($value, $callee, $parameterPosition)
    {
        static::assertString($value, $callee, $parameterPosition);

        if ($value == '') {
            throw new static(
                \sprintf(
                    '%s() expects parameter %d to be non-empty-string, empty %s given',
                    $callee,
                    $parameterPosition,
                    self::getType($value)
                )
            );
        }
    }

    /**
     * @param object|string $value
     * @param class-string $type
     * @param string $callee
     * @param int $parameterPosition
     * @return void
     */
    public static function assertType($value, $type, $callee, $parameterPosition)
    {
        if ((\is_string($value) && !\class_exists($value)) || !\is_a($value, $type, true)) {
            throw new static(
                \sprintf(
                    '%s() expects parameter %d to be %s, %s (%s) given',
                    $callee,
                    $parameterPosition,
                    $type,
                    self::getType($value),
                    var_export($value, true)
                )
            );
        }
    }

    /**
     * @param mixed $value
     * @param string $callee
     * @param int $parameterPosition
     * @return void
     */
    public static function assertStringOrList($value, $callee, $parameterPosition)
    {
        if (static::isString($value)) {
            return;
        }

        if ((\is_array($value) || \is_object($value)) && static::isListAlike($value, \Traversable::class)) {
            return;
        }

        throw new static(
            \sprintf(
                '%s() expects parameter %d to be string or list, %s given',
                $callee,
                $parameterPosition,
                self::getType($value)
            )
        );
    }

    /**
     * @param mixed $value
     * @param string $callee
     * @param int $parameterPosition
     * @return void
     */
    public static function assertClass($value, $callee, $parameterPosition)
    {
        if (!\is_string($value) || !\class_exists($value)) {
            throw new static(
                \sprintf(
                    '%s() expects parameter %d to be valid class, %s given',
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
    public static function assertObject($value, $callee, $parameterPosition)
    {
        if (!\is_object($value)) {
            throw new static(
                \sprintf(
                    '%s() expects parameter %d to be object, %s given',
                    $callee,
                    $parameterPosition,
                    self::getType($value)
                )
            );
        }
    }

    /**
     * @param mixed $value
     * @return bool
     */
    private static function isString($value)
    {
        return \is_string($value) || \is_numeric($value)
            || (\is_object($value) && \method_exists($value, '__toString'));
    }

    /**
     * @param array<array-key, mixed>|object $list
     * @param class-string $className
     * @return bool
     */
    private static function isListAlike($list, $className)
    {
        return \is_array($list) || ($list instanceof $className);
    }

    /**
     * @param mixed $value
     * @return string
     */
    private static function getType($value)
    {
        return \is_object($value) ? \get_class($value) : \gettype($value);
    }
}
