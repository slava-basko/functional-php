<?php

namespace Basko\Functional;

use Basko\Functional\Exception\InvalidArgumentException;

/**
 * Check if number is even.
 *
 * ```php
 * is_even(4); // true
 * is_even(3); // false
 * ```
 *
 * @param numeric $n
 * @return bool
 * @no-named-arguments
 */
function is_even($n)
{
    InvalidArgumentException::assertNumeric($n, __FUNCTION__, 1);

    return $n % 2 === 0;
}

define('Basko\Functional\is_even', __NAMESPACE__ . '\\is_even', false);

/**
 * Check if number is odd.
 *
 * ```php
 * is_odd(5); // true
 * is_odd(2); // false
 * ```
 *
 * @param numeric $n
 * @return mixed
 * @no-named-arguments
 */
function is_odd($n)
{
    InvalidArgumentException::assertNumeric($n, __FUNCTION__, 1);

    $odd = complement(is_even);

    return $odd($n);
}

define('Basko\Functional\is_odd', __NAMESPACE__ . '\\is_odd', false);

/**
 * Perform `$a + $b`.
 *
 * ```php
 * plus(4, 2); // 6
 * ```
 *
 * @param numeric $a
 * @param numeric $b
 * @return mixed
 * @no-named-arguments
 */
function plus($a, $b = null)
{
    InvalidArgumentException::assertNumeric($a, __FUNCTION__, 1);

    if (is_null($b)) {
        return partial(plus, $a);
    }

    InvalidArgumentException::assertNumeric($b, __FUNCTION__, 2);

    return $a + $b;
}

define('Basko\Functional\plus', __NAMESPACE__ . '\\plus', false);

/**
 * Perform `$a - $b`.
 *
 * ```php
 * minus(4, 2); // 2
 * ```
 *
 * @param numeric $a
 * @param numeric $b
 * @return mixed
 * @no-named-arguments
 */
function minus($a, $b = null)
{
    InvalidArgumentException::assertNumeric($a, __FUNCTION__, 1);

    if (is_null($b)) {
        return partial(flipped(minus), $a);
    }

    InvalidArgumentException::assertNumeric($b, __FUNCTION__, 2);

    return $a - $b;
}

define('Basko\Functional\minus', __NAMESPACE__ . '\\minus', false);

/**
 * Perform `$a / $b`.
 *
 * ```php
 * div(4, 2); // 2
 * ```
 *
 * @param numeric $a
 * @param numeric $b
 * @return callable|float|int
 * @no-named-arguments
 */
function div($a, $b = null)
{
    InvalidArgumentException::assertNumeric($a, __FUNCTION__, 1);

    if (is_null($b)) {
        return partial(flipped(div), $a);
    }

    InvalidArgumentException::assertNumeric($b, __FUNCTION__, 2);

    return $a / $b;
}

define('Basko\Functional\div', __NAMESPACE__ . '\\div', false);

/**
 * Perform `$a * $b`.
 *
 * ```php
 * multiply(4, 2); // 8
 * ```
 *
 * @param numeric $a
 * @param numeric $b
 * @return callable|float|int
 * @no-named-arguments
 */
function multiply($a, $b = null)
{
    InvalidArgumentException::assertNumeric($a, __FUNCTION__, 1);

    if (is_null($b)) {
        return partial(multiply, $a);
    }

    InvalidArgumentException::assertNumeric($b, __FUNCTION__, 2);

    return $a * $b;
}

define('Basko\Functional\multiply', __NAMESPACE__ . '\\multiply', false);

/**
 * Fold list with `plus`.
 *
 * ```php
 * sum([3, 2, 1]); // 6
 * ```
 *
 * @param iterable $list
 * @return mixed
 * @no-named-arguments
 */
function sum($list)
{
    InvalidArgumentException::assertList($list, __FUNCTION__, 1);

    return fold(plus, 0, $list);
}

define('Basko\Functional\sum', __NAMESPACE__ . '\\sum', false);

/**
 * Fold list with `minus`.
 *
 * ```php
 * diff([10, 2, 1]); // 7
 * ```
 *
 * @param iterable $list
 * @return mixed
 * @no-named-arguments
 */
function diff($list)
{
    InvalidArgumentException::assertList($list, __FUNCTION__, 1);

    $list = $list instanceof \Traversable ? iterator_to_array($list) : $list;

    return fold(minus, array_shift($list), $list);
}

define('Basko\Functional\diff', __NAMESPACE__ . '\\diff', false);

/**
 * Fold list with `div`.
 *
 * ```php
 * divide([20, 2, 2]); // 5
 * ```
 *
 * @param iterable $list
 * @return mixed
 * @no-named-arguments
 */
function divide($list)
{
    InvalidArgumentException::assertList($list, __FUNCTION__, 1);

    $list = $list instanceof \Traversable ? iterator_to_array($list) : $list;

    return fold(div, array_shift($list), $list);
}

define('Basko\Functional\divide', __NAMESPACE__ . '\\divide', false);

/**
 * Fold list with `multiply`.
 *
 * ```php
 * product([4, 2, 2]); // 16
 * ```
 *
 * @param iterable $list
 * @return mixed
 * @no-named-arguments
 */
function product($list)
{
    InvalidArgumentException::assertList($list, __FUNCTION__, 1);

    $list = $list instanceof \Traversable ? iterator_to_array($list) : $list;

    return fold(multiply, array_shift($list), $list);
}

define('Basko\Functional\product', __NAMESPACE__ . '\\product', false);

/**
 * Calculate average value.
 *
 * ```php
 * average([1, 2, 3, 4, 5, 6, 7]); // 4
 * ```
 *
 * @template T
 * @param T[] $list
 * @return float|int
 * @no-named-arguments
 */
function average($list)
{
    InvalidArgumentException::assertList($list, __FUNCTION__, 1);

    return sum($list) / count($list);
}

define('Basko\Functional\average', __NAMESPACE__ . '\\average', false);

/**
 * Increments its argument.
 *
 * ```php
 * inc(41); // 42
 * ```
 *
 * @param numeric $n
 * @return float|int
 * @no-named-arguments
 */
function inc($n)
{
    InvalidArgumentException::assertNumeric($n, __FUNCTION__, 1);

    return plus($n, 1);
}

define('Basko\Functional\inc', __NAMESPACE__ . '\\inc', false);

/**
 * Decrements its argument.
 *
 * ```php
 * dec(43); // 42
 * ```
 *
 * @param numeric $n
 * @return float|int
 * @no-named-arguments
 */
function dec($n)
{
    InvalidArgumentException::assertNumeric($n, __FUNCTION__, 1);

    return minus($n, 1);
}

define('Basko\Functional\dec', __NAMESPACE__ . '\\dec', false);

/**
 * Power its argument.
 *
 * ```php
 * power(4); // 16
 * ```
 *
 * @param numeric $n
 * @return float|int
 * @no-named-arguments
 */
function power($n)
{
    InvalidArgumentException::assertNumeric($n, __FUNCTION__, 1);

    return $n * $n;
}

define('Basko\Functional\power', __NAMESPACE__ . '\\power', false);

/**
 * Calculate median.
 *
 * ```php
 * median([2, 9, 7]); // 7
 * median([7, 2, 10, 9]); // 8
 * ```
 *
 * @template T
 * @param T[] $list
 * @return T
 * @no-named-arguments
 */
function median($list)
{
    InvalidArgumentException::assertList($list, __FUNCTION__, 1);

    \sort($list);
    $count = count($list);
    $middleValue = (int)floor(($count - 1) / 2); // find the middle value, or the lowest middle value

    if ($count % 2) { // odd number, middle is the median
        $median = $list[$middleValue];
    } else { // even number, calculate avg of 2 medians
        $low = $list[$middleValue];
        $high = $list[$middleValue + 1];
        $median = (($low + $high) / 2);
    }

    return $median;
}

define('Basko\Functional\median', __NAMESPACE__ . '\\median', false);
