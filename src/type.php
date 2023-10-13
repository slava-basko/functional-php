<?php

namespace Basko\Functional;

use Basko\Functional\Exception\InvalidArgumentException;
use Basko\Functional\Exception\TypeException;

/**
 * This function can't be automatically partialed because `$object` can be NULL and thant's OK.
 *
 * ```php
 * instance_of(stdClass::class, new stdClass()); // true
 * instance_of(User::class, new stdClass()); // false
 * ```
 *
 * @param string $instanceof
 * @param object $object
 * @return bool
 * @no-named-arguments
 */
function instance_of($instanceof, $object)
{
    InvalidArgumentException::assertClass($instanceof, __FUNCTION__, 1);

    return $object instanceof $instanceof;
}

define('Basko\Functional\instance_of', __NAMESPACE__ . '\\instance_of', false);

/**
 * Curryied version of `instance_of`.
 *
 * ```php
 * is_instance_of(stdClass::class)(new stdClass()); // true
 * is_instance_of(User::class)(new stdClass()); // false
 * ```
 *
 * @param mixed $instanceof
 * @return callable
 */
function is_instance_of($instanceof)
{
    InvalidArgumentException::assertClass($instanceof, __FUNCTION__, 1);

    return
        /**
         * @param mixed $value
         * @return bool
         */
        function ($value) use ($instanceof) {
            return instance_of($instanceof, $value);
        };
}

define('Basko\Functional\is_instance_of', __NAMESPACE__ . '\\is_instance_of', false);

/**
 * Return a function that checks `$value instanceof SomeClass`.
 *
 * ```php
 * type_of(\User::class)(new User()); // User
 * type_of(\User::class)(new SomeClass()); // TypeException: Could not convert "SomeClass" to type "User"
 * ```
 *
 * @param string $instanceof
 * @return callable
 */
function type_of($instanceof)
{
    InvalidArgumentException::assertClass($instanceof, __FUNCTION__, 1);

    return
        /**
         * @param mixed $value
         * @return object
         * @throws \Basko\Functional\Exception\TypeException
         */
        function ($value) use ($instanceof) {
            if (instance_of($instanceof, $value)) {
                return $value;
            }

            throw TypeException::forValue($value, $instanceof);
        };
}

define('Basko\Functional\type_of', __NAMESPACE__ . '\\type_of', false);

/**
 * Checks and coerces value to `bool`.
 *
 * ```php
 * type_bool(true); // true
 * type_bool(1); // true
 * type_bool('1'); // true
 * type_bool(false); // false
 * type_bool(0); // false
 * type_bool('0'); // false
 * type_bool('some-string'); // TypeException: Could not convert "string" to type "bool"
 * ```
 *
 * @param mixed $value
 * @return bool
 * @no-named-arguments
 * @throws \Basko\Functional\Exception\TypeException
 */
function type_bool($value)
{
    if (is_bool($value)) {
        return $value;
    }

    if (0 === $value || '0' === $value) {
        return false;
    }

    if (1 === $value || '1' === $value) {
        return true;
    }

    throw TypeException::forValue($value, 'bool');
}

define('Basko\Functional\type_bool', __NAMESPACE__ . '\\type_bool', false);

/**
 * Checks and coerces value to `string`.
 * Object: method `__toString` will be called
 * Array: all values will be concatenated with comma.
 *
 * ```php
 * type_string('hello'); // 'hello'
 * type_string(123); // '123'
 * ```
 *
 * @param mixed $value
 * @return string
 * @no-named-arguments
 * @throws \Basko\Functional\Exception\TypeException
 */
function type_string($value)
{
    if (is_string($value)) {
        return $value;
    }

    if (is_int($value) || (is_object($value) && method_exists($value, '__toString'))) {
        return (string)$value;
    }

    if (is_array($value)) {
        return implode(', ', map(type_string, $value));
    }

    throw TypeException::forValue($value, 'string');
}

define('Basko\Functional\type_string', __NAMESPACE__ . '\\type_string', false);

/**
 * Checks and coerces value to `int`.
 *
 * ```php
 * type_int('123'); // 123
 * type_int('007'); // 7
 * type_int('1.0'); // 1
 * ```
 *
 * @param mixed $value
 * @return int
 * @no-named-arguments
 * @throws \Basko\Functional\Exception\TypeException
 */
function type_int($value)
{
    if (is_int($value)) {
        return $value;
    }

    if (is_float($value)) {
        $integerValue = (int)$value;
        if (((float)$integerValue) === $value) {
            return $integerValue;
        }
    }

    if (is_string($value) || (is_object($value) && method_exists($value, '__toString'))) {
        $str = (string)$value;
        $int = (int)$str;
        if ($str === (string)$int) {
            return $int;
        }

        $trimmed = ltrim($str, '0');
        $int = (int)$trimmed;
        if ($trimmed === (string)$int) {
            return $int;
        }

        // Exceptional case "000" -(trim)-> "", but we want to return 0
        if ('' === $trimmed && '' !== $str) {
            return 0;
        }
    }

    throw TypeException::forValue($value, 'int');
}

define('Basko\Functional\type_int', __NAMESPACE__ . '\\type_int', false);

/**
 * Checks and coerces value to `float`.
 *
 * ```php
 * type_float(123); // 123.0
 * type_float('123'); // 123.0
 * ```
 *
 * @param mixed $value
 * @return float
 * @no-named-arguments
 * @throws \Basko\Functional\Exception\TypeException
 */
function type_float($value)
{
    if (is_float($value)) {
        return $value;
    }

    if (is_int($value)) {
        return $value;
    }

    if (is_string($value) || (is_object($value) && method_exists($value, '__toString'))) {
        $str = (string)$value;
        if ('' !== $str) {
            if (ctype_digit($str)) {
                return (float)$str;
            }

            if (1 === preg_match("/^[+-]?(\d+([.]\d*)?([eE][+-]?\d+)?|[.]\d+([eE][+-]?\d+)?)$/", $str)) {
                return (float)$str;
            }
        }
    }


    throw TypeException::forValue($value, 'float');
}

define('Basko\Functional\type_float', __NAMESPACE__ . '\\type_float', false);

/**
 * Union type.
 *
 * ```php
 * $t = type_union(type_int, type_float);
 * $t(1); // 1;
 * $t(1.00); // 1
 * $t('1'); // 1
 * ```
 *
 * @param callable $firsts
 * @param callable $second
 * @return callable
 * @no-named-arguments
 */
function type_union($firsts, $second)
{
    $pfn = __FUNCTION__;

    $u =
        /**
         * @return callable
         */
        function (callable $left, callable $right) use ($pfn) {
            return
                /**
                 * @param mixed $value
                 * @return mixed
                 * @throws \Basko\Functional\Exception\TypeException
                 */
                function ($value) use ($left, $right, $pfn) {
                    try {
                        return call_user_func($left, $value);
                    } catch (TypeException $typeException) {
                        $leftType = $typeException->getTarget();
                    } catch (\Exception $exception) {
                        throw new TypeException(sprintf(
                            '%s() fail and there no \Basko\Functional\Exception\TypeException exception was thrown',
                            $pfn
                        ), 0, $exception);
                    }

                    try {
                        return call_user_func($right, $value);
                    } catch (TypeException $typeException) {
                        $rightType = $typeException->getTarget();
                    } catch (\Exception $exception) {
                        throw new TypeException(sprintf(
                            '%s() fail and there no \Basko\Functional\Exception\TypeException exception was thrown',
                            $pfn
                        ), 0, $exception);
                    }

                    if (!is_string($leftType) || !is_string($rightType)) {
                        throw new TypeException(sprintf(
                            'One of type in %s() fail and TypeException::forValue() never called',
                            $pfn
                        ));
                    }

                    throw TypeException::forValue($value, sprintf('%s|%s', $leftType, $rightType));
                };
        };

    $types = func_get_args();
    $firsts = array_shift($types);
    $second = array_shift($types);

    $accumulatedType = $u($firsts, $second);

    foreach ($types as $type) {
        $accumulatedType = $u($accumulatedType, $type);
    }

    return $accumulatedType;
}

define('Basko\Functional\type_union', __NAMESPACE__ . '\\type_union', false);

/**
 * Checks and coerces value to `positive_int`.
 *
 * ```php
 * type_positive_int(2); // 2
 * type_positive_int('2'); // 2
 * ```
 *
 * @param mixed $value
 * @return int Positive int
 * @no-named-arguments
 * @throws \Basko\Functional\Exception\TypeException
 */
function type_positive_int($value)
{
    if (type_int($value) && $value > 0) {
        return $value;
    }

    throw TypeException::forValue($value, 'positive_int');
}

define('Basko\Functional\type_positive_int', __NAMESPACE__ . '\\type_positive_int', false);


/**
 * Checks and coerces value to valid array key that can either be an `int` or a `string`.
 *
 * ```php
 * type_array_key(1); // 1
 * type_array_key('some_key'); // some_key
 * ```
 *
 * @param mixed $value
 * @return string|int
 */
function type_array_key($value)
{
    return call_user_func(type_union(type_string, type_int), $value);
}

define('Basko\Functional\type_array_key', __NAMESPACE__ . '\\type_array_key', false);

/**
 * Checks and coerces list values to `$type[]`.
 *
 * ```php
 * type_list(type_int, [1, '2']); // [1, 2]
 * type_list(type_int, [1, 2.0]); // [1, 2]
 * type_list(type_of(SomeEntity::class), [$entity1, $entity2]); // [$entity1, $entity2]
 * ```
 *
 * @template T of iterable|null
 * @param callable $type
 * @param T $value
 * @return ($value is null ? callable(T):array : array)
 * @throws \Basko\Functional\Exception\TypeException
 * @no-named-arguments
 */
function type_list(callable $type, $value = null)
{
    if (is_null($value)) {
        return partial(type_list, $type);
    }

    InvalidArgumentException::assertList($value, __FUNCTION__, 2);

    $result = [];

    foreach ($value as $k => $v) {
        try {
            $result[] = call_user_func($type, $v);
        } catch (TypeException $typeException) {
            throw new TypeException(
                'List element \'' . $k . '\': ' . $typeException->getMessage(),
                0,
                $typeException
            );
        }
    }

    return $result;
}

define('Basko\Functional\type_list', __NAMESPACE__ . '\\type_list', false);

/**
 * Checks and coerces array keys to `$keyType` and values to `$valueType`.
 *
 * ```php
 * type_map(type_array_key, type_int, ['one' => 1, 'two' => 2]); // ['one' => 1, 'two' => 2]
 * ```
 *
 * @param callable $keyType
 * @param callable|null $valueType
 * @param mixed $value
 * @return array|callable
 * @no-named-arguments
 */
function type_map(callable $keyType, callable $valueType = null, $value = null)
{

    if (is_null($valueType) && is_null($value)) {
        return partial(type_map, $keyType);
    } elseif (is_null($value)) {
        return partial(type_map, $keyType, $valueType);
    }

    InvalidArgumentException::assertList($value, __FUNCTION__, 3);

    $result = [];

    foreach ($value as $k => $v) {
        $result[call_user_func($keyType, $k)] = call_user_func($valueType, $v);
    }

    return $result;
}

define('Basko\Functional\type_map', __NAMESPACE__ . '\\type_map', false);

/**
 * Checks array keys presence and coerces values to according types.
 * All `key => value` pair that not described will be removed.
 *
 * ```php
 * $parcelShape = type_shape([
 *      'description' => type_string,
 *      'value' => type_union(type_int, type_float),
 *      'dimensions' => type_shape([
 *          'width' => type_union(type_int, type_float),
 *          'height' => type_union(type_int, type_float),
 *      ]),
 *      'products' => type_list(type_shape([
 *          'description' => type_string,
 *          'qty' => type_int,
 *          'price' => type_union(type_int, type_float),
 *      ]))
 * ]);
 *
 * $parcelShape([
 *      'description' => 'some goods',
 *      'value' => 200,
 *      'dimensions' => [
 *          'width' => 0.1,
 *          'height' => 2.4,
 *      ],
 *      'products' => [
 *          [
 *              'description' => 'product 1',
 *              'qty' => 2,
 *              'price' => 50,
 *          ],
 *          [
 *              'description' => 'product 2',
 *              'qty' => 2,
 *              'price' => 50,
 *          ],
 *      ],
 *      'additional' => 'some additional element value that should not present in result'
 * ]); // checked and coerced array will be returned and `additional` will be removed
 * ```
 *
 * @template T of array|null
 * @param array $shape
 * @param T $value
 * @return ($value is null ? callable(T):array : array)
 * @throws \Basko\Functional\Exception\TypeException
 * @no-named-arguments
 */
function type_shape(array $shape, $value = null)
{
    if (is_null($value)) {
        return partial(type_shape, $shape);
    }

    $result = [];

    foreach ($shape as $k => $type) {
        if (array_key_exists($k, $value)) {
            try {
                $result[$k] = call_user_func($type, $value[$k]);
            } catch (TypeException $typeException) {
                throw new TypeException(
                    'Shape element \'' . $k . '\': ' . $typeException->getMessage(),
                    0,
                    $typeException
                );
            } catch (\Exception $exception) {
                throw new TypeException(
                    'Exception on shape element \'' . $k . '\': ' . $exception->getMessage(),
                    0,
                    $exception
                );
            }
        } else {
            throw new TypeException('Shape element \'' . $k . '\': not exist in ' . var_export($value, true));
        }
    }

    return $result;
}

define('Basko\Functional\type_shape', __NAMESPACE__ . '\\type_shape', false);
