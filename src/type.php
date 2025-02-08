<?php

namespace Basko\Functional;

use Basko\Functional\Exception\InvalidArgumentException;
use Basko\Functional\Exception\TypeException;

/**
 * Validates that the value is instance of specific class.
 *
 * ```
 * is_type_of(\User::class, new User()); // true
 * is_type_of(\User::class, new SomeClass()); // false
 * ```
 *
 * @template T
 * @param class-string $class
 * @param T $value
 * @return ($value is null ? callable(T $value):bool : bool)
 * @no-named-arguments
 */
function is_type_of($class, $value = null)
{
    InvalidArgumentException::assertClass($class, __FUNCTION__, 1);

    if (\func_num_args() < 2) {
        return function ($value) use ($class) {
            return $value instanceof $class;
        };
    }

    return $value instanceof $class;
}

define('Basko\Functional\is_type_of', __NAMESPACE__ . '\\is_type_of');

/**
 * Checks that the value is instance of specific class.
 *
 * ```
 * type_of(\User::class, new User()); // User
 * type_of(\User::class, new SomeClass()); // TypeException: Could not convert "SomeClass" to type "User"
 * ```
 *
 * @template T
 * @param class-string $class
 * @param T $value
 * @return ($value is null ? callable(T $value):T : T)
 * @throws \Basko\Functional\Exception\TypeException
 */
function type_of($class, $value = null)
{
    InvalidArgumentException::assertClass($class, __FUNCTION__, 1);

    if (\func_num_args() < 2) {
        return partial(type_of, $class);
    }

    InvalidArgumentException::assertObject($value, __FUNCTION__, 2);

    if (is_type_of($class, $value)) {
        return $value;
    }

    throw TypeException::forValue($value, $class);
}

define('Basko\Functional\type_of', __NAMESPACE__ . '\\type_of');

/**
 * Checks and coerces value to `bool`.
 *
 * ```
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
    if (\is_bool($value)) {
        return $value;
    }

    if ($value === 0 || $value === '0') {
        return false;
    }

    if ($value === 1 || $value === '1') {
        return true;
    }

    throw TypeException::forValue($value, 'bool');
}

define('Basko\Functional\type_bool', __NAMESPACE__ . '\\type_bool');

/**
 * Checks and coerces value to `string`.
 * Object: method `__toString` will be called
 * Array: all values will be concatenated with comma.
 *
 * ```
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
    if (\is_string($value)) {
        return $value;
    }

    if (\is_int($value) || (\is_object($value) && \method_exists($value, '__toString'))) {
        return (string)$value;
    }

    if (\is_array($value)) {
        return \implode(', ', \array_map(type_string, $value));
    }

    throw TypeException::forValue($value, 'string');
}

define('Basko\Functional\type_string', __NAMESPACE__ . '\\type_string');

/**
 * Checks and coerces value to `non-empty-string`.
 * Object: method `__toString` will be called
 * Array: all values will be concatenated with comma.
 *
 * ```
 * type_non_empty_string('abc'); // 'abc'
 * type_non_empty_string([]); // TypeException: Could not convert "array" to type "non-empty-string"
 * ```
 *
 * @param mixed $value
 * @return non-empty-string
 * @no-named-arguments
 * @throws \Basko\Functional\Exception\TypeException
 */
function type_non_empty_string($value)
{
    $newValue = type_string($value);
    if (\strlen($newValue) > 0) {
        return $newValue;
    }

    throw TypeException::forValue($value, 'non-empty-string');
}

define('Basko\Functional\type_non_empty_string', __NAMESPACE__ . '\\type_non_empty_string');

/**
 * Checks and coerces value to `int`.
 *
 * ```
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
    if (\is_int($value)) {
        return $value;
    }

    if (\is_float($value)) {
        $integerValue = (int)$value;
        if (((float)$integerValue) === $value) {
            return $integerValue;
        }
    }

    if (\is_string($value) || (\is_object($value) && \method_exists($value, '__toString'))) {
        $str = (string)$value;
        $int = (int)$str;
        if ($str === (string)$int) {
            return $int;
        }

        $trimmed = \ltrim($str, '0');
        $int = (int)$trimmed;
        if ($trimmed === (string)$int) {
            return $int;
        }

        // Exceptional case "000" -(trim)-> "", but we want to return 0
        if ($trimmed === '' && $str !== '') {
            return 0;
        }
    }

    throw TypeException::forValue($value, 'int');
}

define('Basko\Functional\type_int', __NAMESPACE__ . '\\type_int');

/**
 * Checks and coerces value to `positive_int`.
 *
 * ```
 * type_positive_int(2); // 2
 * type_positive_int('2'); // 2
 * ```
 *
 * @param mixed $value
 * @return int
 * @no-named-arguments
 * @throws \Basko\Functional\Exception\TypeException
 */
function type_positive_int($value)
{
    $newValue = type_int($value);
    if ($newValue > 0) {
        return $newValue;
    }

    throw TypeException::forValue($value, 'positive_int');
}

define('Basko\Functional\type_positive_int', __NAMESPACE__ . '\\type_positive_int');

/**
 * Checks and coerces value to `float`.
 *
 * ```
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
    if (\is_float($value)) {
        return $value;
    }

    if (\is_int($value)) {
        return $value;
    }

    if (\is_string($value) || (\is_object($value) && \method_exists($value, '__toString'))) {
        $str = (string)$value;
        if ($str !== '') {
            if (ctype_digit($str)) {
                return (float)$str;
            }

            if (1 === \preg_match("/^[+-]?(\d+([.]\d*)?([eE][+-]?\d+)?|[.]\d+([eE][+-]?\d+)?)$/", $str)) {
                return (float)$str;
            }
        }
    }


    throw TypeException::forValue($value, 'float');
}

define('Basko\Functional\type_float', __NAMESPACE__ . '\\type_float');

/**
 * Union type.
 *
 * ```
 * $t = type_union(type_int, type_float);
 * $t(1); // 1;
 * $t(1.25); // 1.25
 * $t('1'); // 1
 * ```
 *
 * @param callable $firsts
 * @param callable $second
 * @return callable
 * @throws \Basko\Functional\Exception\TypeException
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
                        return \call_user_func($left, $value);
                    } catch (TypeException $typeException) {
                        $leftType = $typeException->getTarget();
                    } catch (\Exception $exception) {
                        throw new TypeException(\sprintf(
                            '%s() fail and there no \Basko\Functional\Exception\TypeException exception was thrown',
                            $pfn
                        ), 0, $exception);
                    }

                    try {
                        return \call_user_func($right, $value);
                    } catch (TypeException $typeException) {
                        $rightType = $typeException->getTarget();
                    } catch (\Exception $exception) {
                        throw new TypeException(\sprintf(
                            '%s() fail and there no \Basko\Functional\Exception\TypeException exception was thrown',
                            $pfn
                        ), 0, $exception);
                    }

                    if (!\is_string($leftType) || !\is_string($rightType)) {
                        throw new TypeException(\sprintf(
                            'One of type in %s() fail and TypeException::forValue() never called',
                            $pfn
                        ));
                    }

                    throw TypeException::forValue($value, \sprintf('%s|%s', $leftType, $rightType));
                };
        };

    $types = \func_get_args();
    $firsts = \array_shift($types);
    $second = \array_shift($types);

    $accumulatedType = $u($firsts, $second);

    foreach ($types as $type) {
        $accumulatedType = $u($accumulatedType, $type);
    }

    return $accumulatedType;
}

define('Basko\Functional\type_union', __NAMESPACE__ . '\\type_union');


/**
 * Checks and coerces value to valid array key that can either be an `int` or a `string`.
 *
 * ```
 * type_array_key(1); // 1
 * type_array_key('some_key'); // some_key
 * ```
 *
 * @param mixed $value
 * @return string|int
 * @throws \Basko\Functional\Exception\TypeException
 */
function type_array_key($value)
{
    return \call_user_func(type_union(type_string, type_int), $value);
}

define('Basko\Functional\type_array_key', __NAMESPACE__ . '\\type_array_key');

/**
 * Checks and coerces list values to `$type[]`.
 *
 * ```
 * type_list(type_int, [1, '2']); // [1, 2]
 * type_list(type_int, [1, 2.0]); // [1, 2]
 * type_list(type_of(SomeEntity::class), [$entity1, $entity2]); // [$entity1, $entity2]
 * ```
 *
 * @template TValue
 * @template TNewValue
 * @template T of array<TValue>|\Traversable<TValue>
 * @param callable(TValue):TNewValue $type
 * @param T $value
 * @return ($value is null ? callable(T $value):array<TNewValue> : array<TNewValue>)
 * @throws \Basko\Functional\Exception\TypeException
 * @no-named-arguments
 */
function type_list(callable $type, $value = null)
{
    if (\func_num_args() < 2) {
        return partial(type_list, $type);
    }

    InvalidArgumentException::assertList($value, __FUNCTION__, 2);

    $result = [];

    /** @var T $value */
    foreach ($value as $k => $v) {
        try {
            $result[] = \call_user_func($type, $v);
        } catch (TypeException $typeException) {
            throw new TypeException(
                'List element \'' . $k . '\' -> ' . $typeException->getMessage(),
                0,
                $typeException
            );
        }
    }

    return $result;
}

define('Basko\Functional\type_list', __NAMESPACE__ . '\\type_list');

/**
 * Checks and coerces array keys to `$keyType` and values to `$valueType`.
 *
 * ```
 * type_array(type_array_key, type_int, ['one' => '1', 'two' => 2]); // ['one' => 1, 'two' => 2]
 * ```
 *
 * @template TKey of array-key
 * @template TValue
 * @template TNewKey of array-key
 * @template TNewValue
 * @param callable(TKey):TNewKey $keyType
 * @param callable(TValue):TNewValue $valueType
 * @param array<TKey, TValue>|\Traversable<TKey, TValue> $value
 * @return array<TNewKey, TNewValue>|callable
 * @throws \Basko\Functional\Exception\TypeException
 * @no-named-arguments
 */
function type_array(callable $keyType, $valueType = null, $value = null)
{
    $n = \func_num_args();
    if ($n === 1) {
        return partial(type_array, $keyType);
    } elseif ($n === 2) {
        return partial(type_array, $keyType, $valueType);
    }

    InvalidArgumentException::assertList($value, __FUNCTION__, 3);
    InvalidArgumentException::assertCallable($valueType, __FUNCTION__, 2);

    $result = [];

    /** @var array<TKey, TValue>|\Traversable<TKey, TValue> $value */
    foreach ($value as $k => $v) {
        /**
         * @var callable(TKey):TNewKey $keyType
         * @var callable(TValue):TNewValue $valueType
         */
        $result[\call_user_func($keyType, $k)] = \call_user_func($valueType, $v);
    }

    return $result;
}

define('Basko\Functional\type_array', __NAMESPACE__ . '\\type_array');

/**
 * Checks array keys presence and coerces values to according types.
 * All `key => value` pair that not described will be removed.
 *
 * ```
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
 * @template TKey of array-key
 * @template TValue
 * @template TNewValue
 * @template T of array<TKey, TValue>
 * @param array<TKey, callable(TValue):TNewValue> $shape
 * @param T $value
 * @return ($value is null ? callable(T $value):array<TKey, TNewValue> : array<TKey, TNewValue>)
 * @throws \Basko\Functional\Exception\TypeException
 * @no-named-arguments
 */
function type_shape(array $shape, $value = null)
{
    if (\func_num_args() < 2) {
        return partial(type_shape, $shape);
    }

    InvalidArgumentException::assertArrayAccess($value, __FUNCTION__, 2);

    $result = [];

    foreach ($shape as $k => $type) {
        /** @var T $value */
        if (\array_key_exists($k, $value)) {
            try {
                $result[$k] = \call_user_func($type, $value[$k]);
            } catch (TypeException $typeException) {
                throw new TypeException(
                    'Shape element \'' . $k . '\' -> ' . $typeException->getMessage(),
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
            $optionalValue = \call_user_func($type, null); // @phpstan-ignore argument.type
            if ($optionalValue === '__basko_functional_type_optional') {
                continue;
            }
            throw new TypeException('Shape element \'' . $k . '\': not exist in ' . \var_export($value, true));
        }
    }

    return $result;
}

define('Basko\Functional\type_shape', __NAMESPACE__ . '\\type_shape');

/**
 * Makes sense to use in `type_shape`.
 * ```
 * $typeUser = type_shape([
 *      'name' => type_string,
 *      'lastName' => type_string,
 *      'location' => type_optional(type_string),
 * ]);
 *
 * $typeUser(['name' => 'Slava', 'lastName' => 'Basko']);
 * // ['name' => 'Slava', 'lastName' => 'Basko']
 *
 * $typeUser(['name' => 'Slava', 'lastName' => 'Basko', 'location' => 'Vancouver']);
 * // ['name' => 'Slava', 'lastName' => 'Basko', 'location' => 'Vancouver']
 *
 * $typeUser(['name' => 'Slava', 'lastName' => 'Basko', 'location' => function() {}]); // TypeException
 * ```
 *
 * @param callable $type
 * @param mixed $value
 * @return callable|mixed|string
 */
function type_optional(callable $type, $value = null)
{
    if (\func_num_args() < 2) {
        return partial(type_optional, $type);
    }

    if ($value === null) {
        return '__basko_functional_type_optional';
    }

    return \call_user_func($type, $value);
}

define('Basko\Functional\type_optional', __NAMESPACE__ . '\\type_optional');

/**
 *  Checks if a given value is within a predefined set of values (enumeration).
 *
 * ```
 * type_enum(['one', 'two', 'three'], 'one'); // 'one'
 * type_enum(['one', 'two', 'three'], 'four'); // TypeException: Value "four" is not in enum('one', 'two', 'three')
 * ```
 *
 * @param array<mixed> $enum
 * @param mixed $value
 * @return ($value is null ? callable(mixed $value):mixed : mixed)
 * @throws \Basko\Functional\Exception\TypeException
 * @no-named-arguments
 */
function type_enum(array $enum, $value = null)
{
    if (\func_num_args() < 2) {
        return partial(type_enum, $enum);
    }

    if (\in_array($value, $enum, true)) {
        return $value;
    }

    throw new TypeException(\sprintf(
        'Value "%s" is not in enum(%s)',
        \var_export($value, true),
        \implode(
            ', ',
            \array_map(
                function ($val) {
                    return \var_export($val, true);
                },
                $enum
            )
        )
    ));
}

define('Basko\Functional\type_enum', __NAMESPACE__ . '\\type_enum');
