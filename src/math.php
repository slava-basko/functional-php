<?php

namespace Basko\Functional;

use Basko\Functional\Exception\InvalidArgumentException;

/**
 * @param $n
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
 * @param $n
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
 * @param $a
 * @param $b
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
 * @param $a
 * @param $b
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
 * @param $a
 * @param $b
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
 * @param $a
 * @param $b
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
 * @param $list
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
 * @param $list
 * @return mixed
 * @no-named-arguments
 */
function diff($list)
{
    InvalidArgumentException::assertList($list, __FUNCTION__, 1);

    return fold(minus, array_shift($list), $list);
}

define('Basko\Functional\diff', __NAMESPACE__ . '\\diff', false);

/**
 * @param $list
 * @return mixed
 * @no-named-arguments
 */
function divide($list)
{
    InvalidArgumentException::assertList($list, __FUNCTION__, 1);

    return fold(div, array_shift($list), $list);
}

define('Basko\Functional\divide', __NAMESPACE__ . '\\divide', false);

/**
 * @param $list
 * @return mixed
 * @no-named-arguments
 */
function product($list)
{
    InvalidArgumentException::assertList($list, __FUNCTION__, 1);

    return fold(multiply, array_shift($list), $list);
}

define('Basko\Functional\product', __NAMESPACE__ . '\\product', false);

/**
 * @param $list
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
 * @param $list
 * @return mixed
 * @no-named-arguments
 */
function median($list)
{
    InvalidArgumentException::assertList($list, __FUNCTION__, 1);

    \sort($list);
    $count = count($list);
    $middleValue = floor(($count - 1) / 2); // find the middle value, or the lowest middle value

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
