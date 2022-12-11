<?php

namespace Basko\Functional;

use Basko\Functional\Exception\TypeException;

/**
 * This function can't be automatically partialed because `$object` can be NULL and thant's OK.
 *
 * @param $instanceof
 * @param $object
 * @return bool
 * @no-named-arguments
 */
function instance_of($instanceof, $object)
{
    return $object instanceof $instanceof;
}

define('Basko\Functional\instance_of', __NAMESPACE__ . '\\instance_of');

/**
 * @param $instanceof
 * @return callable
 */
function is_instance_of($instanceof)
{
    return function ($value) use ($instanceof) {
        return instance_of($instanceof, $value);
    };
}

define('Basko\Functional\is_instance_of', __NAMESPACE__ . '\\is_instance_of');

/**
 * @param $instanceof
 * @return callable
 */
function type_of($instanceof)
{
    return function ($value) use ($instanceof) {
        if (instance_of($instanceof, $value)) {
            return $value;
        }

        throw TypeException::forValue($value, $instanceof);
    };
}

define('Basko\Functional\type_of', __NAMESPACE__ . '\\type_of');

/**
 * @param $value
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

define('Basko\Functional\type_bool', __NAMESPACE__ . '\\type_bool');

/**
 * @param $value
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

    throw TypeException::forValue($value, 'string');
}

define('Basko\Functional\type_string', __NAMESPACE__ . '\\type_string');

/**
 * @param $value
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
        $integer_value = (int)$value;
        if (((float)$integer_value) === $value) {
            return $integer_value;
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

define('Basko\Functional\type_int', __NAMESPACE__ . '\\type_int');

/**
 * @param $value
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

define('Basko\Functional\type_float', __NAMESPACE__ . '\\type_float');
