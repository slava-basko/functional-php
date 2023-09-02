<?php

namespace Basko\Functional;

use Basko\Functional\Exception\InvalidArgumentException;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Return number of function arguments.
 *
 * ```php
 * count_args('explode'); // 3
 * ```
 *
 * @param callable $f
 * @param bool $only_required
 * @return int
 * @throws \ReflectionException
 * @no-named-arguments
 */
function count_args(callable $f, $only_required = false)
{
    if (is_string($f) && strpos($f, '::', 1) !== false) {
        $reflection = new ReflectionMethod($f);
    } elseif (is_array($f) && count($f) === 2) {
        $reflection = new ReflectionMethod($f[0], $f[1]);
    } elseif (is_object($f) && method_exists($f, '__invoke')) {
        $reflection = new ReflectionMethod($f, '__invoke');
    } else {
        $reflection = new ReflectionFunction($f);
    }

    return $only_required ? $reflection->getNumberOfRequiredParameters() : $reflection->getNumberOfParameters();
}

define('Basko\Functional\count_args', __NAMESPACE__ . '\\count_args', false);

/**
 * Return a version of the given function where the $count first arguments are curryied.
 *
 * No check is made to verify that the given argument count is either too low or too high.
 * If you give a smaller number you will have an error when calling the given function. If
 * you give a higher number, arguments will simply be ignored.
 *
 * @param int $count Number of arguments you want to curry
 * @param callable $f The function you want to curry
 * @return callable A curryied version of the given function
 * @no-named-arguments
 */
function curry_n($count, callable $f)
{
    $accumulator = function (array $arguments) use ($count, $f, &$accumulator) {
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

define('Basko\Functional\curry_n', __NAMESPACE__ . '\\curry_n', false);

/**
 * Return a curried version of the given function. You can decide if you also
 * want to curry optional parameters or not.
 *
 * ```php
 * $add = function($a, $b, $c) {
 *      return $a + $b + $c;
 * };
 * $curryiedAdd = curry($add);
 * $addTen = $curryiedAdd(10);
 * $addEleven = $addTen(1);
 * $addEleven(4); // 15
 * ```
 *
 * @param callable $f The function to curry
 * @param bool $required Curry optional parameters ?
 * @return callable A curryied version of the given function
 * @no-named-arguments
 * @throws \ReflectionException
 * @no-named-arguments
 */
function curry(callable $f, $required = false)
{
    return curry_n(count_args($f, $required), $f);
}

define('Basko\Functional\curry', __NAMESPACE__ . '\\curry', false);

/**
 * Creates a thunk out of a function. A thunk delays a calculation until its result is needed,
 * providing lazy evaluation of arguments.
 *
 * ```php
 * $add = function($a, $b) {
 *      return $a + $b;
 * };
 * $curryiedAdd = thunkify($add);
 * $addTen = $curryiedAdd(10);
 * $eleven = $addTen(1);
 * $eleven(); // 11
 * ```
 *
 * @param callable $f
 * @param bool $required
 * @return callable
 * @throws \ReflectionException
 * @no-named-arguments
 */
function thunkify(callable $f, $required = false)
{
    return curry_n(count_args($f, $required) + 1, $f);
}

define('Basko\Functional\thunkify', __NAMESPACE__ . '\\thunkify', false);

/**
 * @param callable $f
 * @param int $count number of arguments you want to curry
 * @return callable
 */
function _thunkify_n(callable $f, $count)
{
    return curry_n($count + 1, $f);
}

/**
 * Return function `$f` that will be called only with `abs($count)` arguments,
 * taken either from the left or right depending on the sign.
 *
 * ```php
 * $f = static function ($a = 0, $b = 0, $c = 0) {
 *      return $a + $b + $c;
 * };
 * ary($f, 2)([5, 5]); // 10
 * ary($f, 1)([5, 5]); // 5
 * ary($f, -1)([5, 6]); // 6
 * ```
 *
 * @param callable $f
 * @param int $count A non-zero count (could be negative)
 * @return callable
 * @no-named-arguments
 */
function ary(callable $f, $count)
{
    InvalidArgumentException::assertNonZeroInteger($count, __FUNCTION__, 2);

    return function () use ($f, $count) {
        $args = func_get_args();
        if ($count > 0) {
            return call_user_func_array($f, take($count, $args));
        } elseif ($count < 0) {
            return call_user_func_array($f, take_r(-$count, $args));
        }
    };
}

define('Basko\Functional\ary', __NAMESPACE__ . '\\ary', false);

/**
 * Wraps a function of any arity (including nullary) in a function that accepts exactly 1 parameter.
 * Any extraneous parameters will not be passed to the supplied function.
 *
 * ```php
 * $f = static function ($a = '', $b = '', $c = '') {
 *      return $a . $b . $c;
 * };
 * unary($f)(['one', 'two', 'three]); // one
 * ```
 *
 * @param callable $f
 * @return callable(mixed):mixed
 */
function unary(callable $f)
{
    return ary($f, 1);
}

define('Basko\Functional\unary', __NAMESPACE__ . '\\unary', false);

/**
 * Wraps a function of any arity (including nullary) in a function that accepts exactly 2 parameters.
 * Any extraneous parameters will not be passed to the supplied function.
 *
 * ```php
 * $f = static function ($a = '', $b = '', $c = '') {
 *      return $a . $b . $c;
 * };
 * binary($f)(['one', 'two', 'three]); // onetwo
 * ```
 *
 * @param callable $f
 * @return callable(mixed, mixed):mixed
 */
function binary(callable $f)
{
    return ary($f, 2);
}

define('Basko\Functional\binary', __NAMESPACE__ . '\\binary', false);
