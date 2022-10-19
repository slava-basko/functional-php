<?php

namespace Functional;

use Functional\Exception\InvalidArgumentException;

/**
 * @param $n
 * @return bool
 * @no-named-arguments
 */
function is_even($n)
{
    return $n % 2 === 0;
}

define('Functional\is_even', __NAMESPACE__ . '\\is_even');

/**
 * @param $n
 * @return mixed
 * @no-named-arguments
 */
function is_odd($n)
{
    $odd = not(is_even);

    return $odd($n);
}

define('Functional\is_odd', __NAMESPACE__ . '\\is_odd');

/**
 * @param $a
 * @param $b
 * @return mixed
 * @no-named-arguments
 */
function plus($a, $b = null)
{
    if (is_null($b)) {
        return partial(plus, $a);
    }

    return $a + $b;
}

define('Functional\plus', __NAMESPACE__ . '\\plus');

/**
 * @param $a
 * @param $b
 * @return mixed
 * @no-named-arguments
 */
function minus($a, $b = null)
{
    if (is_null($b)) {
        return partial(flipped(minus), $a);
    }

    return $a - $b;
}

define('Functional\minus', __NAMESPACE__ . '\\minus');

/**
 * @param $a
 * @param $b
 * @return mixed
 * @no-named-arguments
 */
function div($a, $b = null)
{
    if (is_null($b)) {
        return partial(flipped(div), $a);
    }

    return $a / $b;
}

define('Functional\div', __NAMESPACE__ . '\\div');

/**
 * @param $a
 * @param $b
 * @return mixed
 * @no-named-arguments
 */
function multiply($a, $b = null)
{
    if (is_null($b)) {
        return partial(multiply, $a);
    }

    return $a * $b;
}

define('Functional\multiply', __NAMESPACE__ . '\\multiply');

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

define('Functional\sum', __NAMESPACE__ . '\\sum');

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

define('Functional\diff', __NAMESPACE__ . '\\diff');

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

define('Functional\divide', __NAMESPACE__ . '\\divide');

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

define('Functional\product', __NAMESPACE__ . '\\product');

/**
 * @param $list
 * @return mixed
 * @no-named-arguments
 */
function average($list)
{
    InvalidArgumentException::assertList($list, __FUNCTION__, 1);

    return sum($list) / count($list);
}

define('Functional\average', __NAMESPACE__ . '\\average');

/**
 * Increments its argument.
 *
 * @return float|int
 * @no-named-arguments
 */
function inc($n)
{
    return plus($n, 1);
}

define('Functional\inc', __NAMESPACE__ . '\\inc');

/**
 * Decrements its argument.
 *
 * @return float|int
 * @no-named-arguments
 */
function dec($n)
{
    return minus($n, 1);
}

define('Functional\dec', __NAMESPACE__ . '\\dec');

/**
 * Power its argument.
 *
 * @return float|int
 * @no-named-arguments
 */
function power($n)
{
    return $n * $n;
}

define('Functional\power', __NAMESPACE__ . '\\power');

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
    $middle_value = floor(($count - 1) / 2); // find the middle value, or the lowest middle value
    if ($count % 2) { // odd number, middle is the median
        $median = $list[$middle_value];
    } else { // even number, calculate avg of 2 medians
        $low = $list[$middle_value];
        $high = $list[$middle_value + 1];
        $median = (($low + $high) / 2);
    }

    return $median;
}
