<?php

namespace Functional;

use Functional\Exception\InvalidArgumentException;

/**
 * Return number of function arguments.
 *
 * @param callable $f
 * @param $only_required
 * @return int
 * @throws \ReflectionException
 * @no-named-arguments
 */
function count_args(callable $f, $only_required = false)
{
    if (\is_string($f) && \strpos($f, '::', 1) !== false) {
        $reflection = new \ReflectionMethod($f);
    } elseif (\is_array($f) && \count($f) === 2) {
        $reflection = new \ReflectionMethod($f[0], $f[1]);
    } elseif (\is_object($f) && \method_exists($f, '__invoke')) {
        $reflection = new \ReflectionMethod($f, '__invoke');
    } else {
        $reflection = new \ReflectionFunction($f);
    }

    return $only_required ? $reflection->getNumberOfRequiredParameters() : $reflection->getNumberOfParameters();
}

define('Functional\count_args', __NAMESPACE__ . '\\count_args');

/**
 * Return a version of the given function where the $count first arguments are curryied.
 *
 * No check is made to verify that the given argument count is either too low or too high.
 * If you give a smaller number you will have an error when calling the given function. If
 * you give a higher number, arguments will simply be ignored.
 *
 * @param int $count number of arguments you want to curry
 * @param callable $f the function you want to curry
 * @return callable a curryied version of the given function
 * @no-named-arguments
 */
function curry_n($count, callable $f)
{
    $accumulator = function ($arguments) use ($count, $f, &$accumulator) {
        return function () use ($count, $f, $arguments, $accumulator) {
            $newArguments = func_get_args();
            if (!$newArguments) {
                $newArguments = [1];
            }
            $arguments = array_merge($arguments, $newArguments);

            if ($count <= count($arguments)) {
                return call_user_func_array($f, $arguments);
            }

            return $accumulator($arguments);
        };
    };

    return $accumulator([]);
}

define('Functional\curry_n', __NAMESPACE__ . '\\curry_n');

/**
 * Return a curried version of the given function. You can decide if you also
 * want to curry optional parameters or not.
 *
 * @param callable $f the function to curry
 * @param bool $required curry optional parameters ?
 * @return callable a curryied version of the given function
 * @no-named-arguments
 * @throws \ReflectionException
 * @no-named-arguments
 */
function curry(callable $f, $required = false)
{
    return curry_n(count_args($f, $required), $f);
}

define('Functional\curry', __NAMESPACE__ . '\\curry');

/**
 * Creates a thunk out of a function. A thunk delays a calculation until its result is needed,
 * providing lazy evaluation of arguments.
 *
 * @param callable $f
 * @param $required
 * @return callable|\Closure
 * @throws \ReflectionException
 * @no-named-arguments
 */
function thunkify(callable $f, $required = false)
{
    return curry_n(count_args($f, $required) + 1, $f);
}

define('Functional\thunkify', __NAMESPACE__ . '\\thunkify');

/**
 * @param callable $f
 * @param $count number of arguments you want to curry
 * @return callable|\Closure
 */
function _thunkify_n(callable $f, $count)
{
    return curry_n($count + 1, $f);
}

/**
 * Call $func with only abs($count) arguments, taken either from the
 * left or right depending on the sign
 *
 * @no-named-arguments
 */
function ary(callable $f, $count)
{
    InvalidArgumentException::assertNonZeroInteger($count, __FUNCTION__);

    return function () use ($f, $count) {
        $args = func_get_args();
        if ($count > 0) {
            return call_user_func_array($f, take($count, $args));
        } else if ($count < 0) {
            return call_user_func_array($f, take_r(-$count, $args));
        }
    };
}

define('Functional\ary', __NAMESPACE__ . '\\ary');

/**
 * Wraps a function of any arity (including nullary) in a function that accepts exactly 1 parameter.
 * Any extraneous parameters will not be passed to the supplied function.
 *
 * @param callable $f
 * @return callable
 */
function unary(callable $f)
{
    return ary($f, 1);
}

define('Functional\unary', __NAMESPACE__ . '\\unary');

/**
 * Wraps a function of any arity (including nullary) in a function that accepts exactly 2 parameters.
 * Any extraneous parameters will not be passed to the supplied function.
 *
 * @param callable $f
 * @return callable
 */
function binary(callable $f)
{
    return ary($f, 2);
}

define('Functional\binary', __NAMESPACE__ . '\\binary');