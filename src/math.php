<?php

namespace Basko\Functional;

use Basko\Functional\Exception\InvalidArgumentException;

/**
 * Check if number is even.
 *
 * ```
 * is_even(4); // true
 * is_even(3); // false
 * ```
 *
 * @param int $n
 * @return bool
 * @no-named-arguments
 */
function is_even($n)
{
    InvalidArgumentException::assertInteger($n, __FUNCTION__, 1);

    return $n % 2 === 0;
}

define('Basko\Functional\is_even', __NAMESPACE__ . '\\is_even');

/**
 * Check if number is odd.
 *
 * ```
 * is_odd(5); // true
 * is_odd(2); // false
 * ```
 *
 * @param int $n
 * @return bool
 * @no-named-arguments
 */
function is_odd($n)
{
    InvalidArgumentException::assertInteger($n, __FUNCTION__, 1);

    return $n % 2 !== 0;
}

define('Basko\Functional\is_odd', __NAMESPACE__ . '\\is_odd');

/**
 * Increments its argument.
 *
 * ```
 * inc(41); // 42
 * ```
 *
 * @param int $n
 * @return int
 * @no-named-arguments
 */
function inc($n)
{
    InvalidArgumentException::assertInteger($n, __FUNCTION__, 1);

    return $n + 1;
}

define('Basko\Functional\inc', __NAMESPACE__ . '\\inc');

/**
 * Decrements its argument.
 *
 * ```
 * dec(43); // 42
 * ```
 *
 * @param int $n
 * @return int
 * @no-named-arguments
 */
function dec($n)
{
    InvalidArgumentException::assertInteger($n, __FUNCTION__, 1);

    return $n - 1;
}

define('Basko\Functional\dec', __NAMESPACE__ . '\\dec');

/**
 * Perform `$a + $b`.
 *
 * ```
 * plus(4, 2); // 6
 * ```
 *
 * @param numeric $a
 * @param numeric $b
 * @return ($b is null ? callable(numeric $b):int|float : int|float)
 * @no-named-arguments
 */
function plus($a, $b = null)
{
    InvalidArgumentException::assertNumeric($a, __FUNCTION__, 1);

    if (\func_num_args() < 2) {
        $pfn = __FUNCTION__;

        return function ($b) use ($a, $pfn) {
            InvalidArgumentException::assertNumeric($b, $pfn, 2);

            return $a + $b;
        };
    }

    InvalidArgumentException::assertNumeric($b, __FUNCTION__, 2);

    return $a + $b;
}

define('Basko\Functional\plus', __NAMESPACE__ . '\\plus');

/**
 * Perform `$a - $b`.
 *
 * ```
 * minus(4, 2); // 2
 * ```
 *
 * @param numeric $a
 * @param numeric $b
 * @return ($b is null ? callable(numeric $b):int|float : int|float)
 * @no-named-arguments
 */
function minus($a, $b = null)
{
    InvalidArgumentException::assertNumeric($a, __FUNCTION__, 1);

    if (\func_num_args() < 2) {
        $pfn = __FUNCTION__;

        return function ($b) use ($a, $pfn) {
            InvalidArgumentException::assertNumeric($b, $pfn, 2);

            return $a - $b;
        };
    }

    InvalidArgumentException::assertNumeric($b, __FUNCTION__, 2);

    return $a - $b;
}

define('Basko\Functional\minus', __NAMESPACE__ . '\\minus');

/**
 * Perform `$a / $b`.
 *
 * ```
 * div(4, 2); // 2
 * ```
 *
 * @param numeric $a
 * @param numeric $b
 * @return ($b is null ? callable(numeric $b):int|float : int|float)
 * @no-named-arguments
 */
function div($a, $b = null)
{
    InvalidArgumentException::assertNumeric($a, __FUNCTION__, 1);

    if (\func_num_args() < 2) {
        $pfn = __FUNCTION__;

        return function ($b) use ($a, $pfn) {
            InvalidArgumentException::assertNumeric($b, $pfn, 2);

            return $a / $b;
        };
    }

    InvalidArgumentException::assertNumeric($b, __FUNCTION__, 2);

    return $a / $b;
}

define('Basko\Functional\div', __NAMESPACE__ . '\\div');

/**
 * Modulo of two numbers.
 *
 * ```
 * modulo(1089, 37)); // 16
 * ```
 *
 * @param int $a
 * @param int $b
 * @return ($b is null ? callable(int $b):int : int)
 */
function modulo($a, $b = null)
{
    InvalidArgumentException::assertInteger($a, __FUNCTION__, 1);

    if (\func_num_args() < 2) {
        $pfn = __FUNCTION__;

        return function ($b) use ($a, $pfn) {
            InvalidArgumentException::assertInteger($b, $pfn, 2);

            return $a % $b;
        };
    }

    InvalidArgumentException::assertInteger($b, __FUNCTION__, 2);

    return $a % $b;
}

define('Basko\Functional\modulo', __NAMESPACE__ . '\\modulo');

/**
 * Perform `$a * $b`.
 *
 * ```
 * multiply(4, 2); // 8
 * ```
 *
 * @param numeric $a
 * @param numeric $b
 * @return ($b is null ? callable(numeric $b):int|float : int|float)
 * @no-named-arguments
 */
function multiply($a, $b = null)
{
    InvalidArgumentException::assertNumeric($a, __FUNCTION__, 1);

    if (\func_num_args() < 2) {
        $pfn = __FUNCTION__;

        return function ($b) use ($a, $pfn) {
            InvalidArgumentException::assertNumeric($b, $pfn, 2);

            return $a * $b;
        };
    }

    InvalidArgumentException::assertNumeric($b, __FUNCTION__, 2);

    return $a * $b;
}

define('Basko\Functional\multiply', __NAMESPACE__ . '\\multiply');

/**
 * Fold list with `plus`.
 *
 * ```
 * sum([3, 2, 1]); // 6
 * ```
 *
 * @param array<int|float>|\Traversable<int|float> $list
 * @return int|float
 * @no-named-arguments
 */
function sum($list) // @phpstan-ignore return.unusedType
{
    InvalidArgumentException::assertList($list, __FUNCTION__, 1);

    return fold(plus, 0, $list);
}

define('Basko\Functional\sum', __NAMESPACE__ . '\\sum');

/**
 * Fold list with `minus`.
 *
 * ```
 * diff([10, 2, 1]); // 7
 * ```
 *
 * @param array<int|float>|\Traversable<int|float> $list
 * @return int|float
 * @no-named-arguments
 */
function diff($list)
{
    InvalidArgumentException::assertList($list, __FUNCTION__, 1);

    $list = $list instanceof \Traversable ? \iterator_to_array($list) : $list;

    $accumulator = \array_shift($list);
    if (!\is_numeric($accumulator)) {
        throw new InvalidArgumentException(\sprintf(
            '%s() expects first element of parameter %d to be numeric, %s given',
            __FUNCTION__,
            1,
            \is_object($accumulator) ? \get_class($accumulator) : \gettype($accumulator)
        ));
    }

    return fold(minus, $accumulator, $list);
}

define('Basko\Functional\diff', __NAMESPACE__ . '\\diff');

/**
 * Fold list with `div`.
 *
 * ```
 * divide([20, 2, 2]); // 5
 * ```
 *
 * @param array<int|float>|\Traversable<int|float> $list
 * @return int|float
 * @no-named-arguments
 */
function divide($list)
{
    InvalidArgumentException::assertList($list, __FUNCTION__, 1);

    $list = $list instanceof \Traversable ? \iterator_to_array($list) : $list;

    $accumulator = \array_shift($list);
    if (!\is_numeric($accumulator)) {
        throw new InvalidArgumentException(\sprintf(
            '%s() expects first element of parameter %d to be numeric, %s given',
            __FUNCTION__,
            1,
            \is_object($accumulator) ? \get_class($accumulator) : \gettype($accumulator)
        ));
    }

    return fold(div, $accumulator, $list);
}

define('Basko\Functional\divide', __NAMESPACE__ . '\\divide');

/**
 * Fold list with `multiply`.
 *
 * ```
 * product([4, 2, 2]); // 16
 * ```
 *
 * @param array<int|float>|\Traversable<int|float> $list
 * @return int|float
 * @no-named-arguments
 */
function product($list)
{
    InvalidArgumentException::assertList($list, __FUNCTION__, 1);

    $list = $list instanceof \Traversable ? \iterator_to_array($list) : $list;

    $accumulator = \array_shift($list);
    if (!\is_numeric($accumulator)) {
        throw new InvalidArgumentException(\sprintf(
            '%s() expects first element of parameter %d to be numeric, %s given',
            __FUNCTION__,
            1,
            \is_object($accumulator) ? \get_class($accumulator) : \gettype($accumulator)
        ));
    }

    return fold(multiply, $accumulator, $list);
}

define('Basko\Functional\product', __NAMESPACE__ . '\\product');

/**
 * Calculate average value.
 *
 * ```
 * average([1, 2, 3, 4, 5, 6, 7]); // 4
 * ```
 *
 * @param array<int|float>|\Traversable<int|float> $list
 * @return float|int
 * @no-named-arguments
 */
function average($list)
{
    InvalidArgumentException::assertList($list, __FUNCTION__, 1);

    return sum($list) / len($list);
}

define('Basko\Functional\average', __NAMESPACE__ . '\\average');

/**
 * Power its argument.
 *
 * ```
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

define('Basko\Functional\power', __NAMESPACE__ . '\\power');

/**
 * Calculate median.
 *
 * ```
 * median([2, 9, 7]); // 7
 * median([7, 2, 10, 9]); // 8
 * ```
 *
 * @param array<int|float>|\Traversable<int|float> $list
 * @return int|float
 * @no-named-arguments
 */
function median($list)
{
    InvalidArgumentException::assertList($list, __FUNCTION__, 1);

    $list = $list instanceof \Traversable ? \iterator_to_array($list) : $list;

    \sort($list);
    $count = \count($list);
    $middleValue = (int)\floor(($count - 1) / 2); // find the middle value, or the lowest middle value

    if ($count % 2) { // odd number, middle is the median
        $median = $list[$middleValue];
    } else { // even number, calculate avg of 2 medians
        $low = $list[$middleValue];
        $high = $list[$middleValue + 1];
        $median = (($low + $high) / 2);
    }

    return $median;
}

define('Basko\Functional\median', __NAMESPACE__ . '\\median');

/**
 * Restricts a value to be within a range.
 *
 * ```
 * clamp(1, 10, -5); // 1
 * clamp(1, 10, 15); // 10
 * clamp(1, 10, 4); // 4
 * clamp('2023-01-01', '2023-11-22', '2012-11-22'); // 2023-01-01
 * clamp('2023-01-01', '2023-11-22', '2023-04-22'); // 2023-04-22
 *
 * // Example:
 * $pagePortion = clamp(MIN_PORTION, MAX_PORTION, $_REQUEST['perPage']);
 * ```
 *
 * @param numeric $min
 * @param numeric $max
 * @param numeric $value
 * @return callable|numeric
 * @phpstan-return ($max is null ? (callable(numeric $max, numeric $value=):($value is null ? callable(numeric $value):numeric : numeric|numeric)) : ($value is null ? callable(numeric $value):numeric : numeric))
 */
function clamp($min, $max = null, $value = null)
{
    $n = \func_num_args();
    if ($n === 1) {
        return partial(clamp, $min);
    } elseif ($n === 2) {
        return partial(clamp, $min, $max);
    }

    /**
     * @var numeric $max
     * @var numeric $value
     */
    return $value < $min ? $min : ($value > $max ? $max : $value);
}

define('Basko\Functional\clamp', __NAMESPACE__ . '\\clamp');

/**
 * Cartesian product of sets.
 * X = {1, 2}
 * Y = {a, b}
 * Z = {A, B, C}
 * X × Y × Z = { (1, a, A), (2, a, A), (1, b, A), (2, b, A)
 *               (1, a, B), (2, a, B), (1, b, B), (2, b, B)
 *               (1, a, C), (2, a, C), (1, b, C), (2, b, C) }
 *
 * Note: This function is not curried because of no fixed arity.
 *
 * ```
 * $ranks = [2, 3, 4, 5, 6, 7, 8, 9, 10, 'Jack', 'Queen', 'King', 'Ace'];
 * $suits = ["Hearts", "Diamonds", "Clubs", "Spades"];
 *
 * $cards = pipe(cartesian_product, map(join('')))($ranks, [' of '], $suits);
 * // [
 * //    '2 of Hearts',
 * //    '2 of Diamonds',
 * //    ...
 * //    'Ace of Clubs',
 * //    'Ace of Spades',
 * // ];
 * ```
 *
 * @param array<mixed>|\Traversable<mixed> $list1
 * @param array<mixed>|\Traversable<mixed> $list2
 * @return array<mixed>
 */
function cartesian_product($list1, $list2)
{
    $lists = \func_get_args();

    $setsCount = \count($lists);
    $size = $setsCount > 0 ? 1 : 0;

    foreach ($lists as $k => &$list) {
        InvalidArgumentException::assertList($list, __FUNCTION__, $k + 1);

        $list = \is_array($list) ? $list : \iterator_to_array($list);
        $size = $size * \count($list);
    }

    $result = [];

    for ($i = 0; $i < $size; $i++) {
        $result[$i] = [];
        for ($j = 0; $j < $setsCount; $j++) {
            $result[$i][] = \current($lists[$j]);
        }

        for ($j = ($setsCount - 1); $j >= 0; $j--) {
            if (\next($lists[$j])) {
                break;
            } elseif (isset($lists[$j])) {
                \reset($lists[$j]);
            }
        }
    }

    return $result;
}

define('Basko\Functional\cartesian_product', __NAMESPACE__ . '\\cartesian_product');
