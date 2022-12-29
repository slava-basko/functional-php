<?php

namespace Basko\Functional;

use Basko\Functional\Exception\InvalidArgumentException;

/**
 * Alias of `explode`.
 *
 * @param string $separator
 * @param string $string
 * @return callable|false|string[]
 * @no-named-arguments
 */
function str_split($separator, $string = null)
{
    InvalidArgumentException::assertString($separator, __FUNCTION__, 1);

    if (is_null($string)) {
        return partial(str_split, $separator);
    }

    InvalidArgumentException::assertString($string, __FUNCTION__, 2);

    return explode($separator, $string);
}

define('Basko\Functional\str_split', __NAMESPACE__ . '\\str_split');

/**
 * Example:
 * ```
 * str_split_on(2, 'GB123456789000'); // ['GB', '123456789000']
 * ```
 *
 * @param int $num
 * @param string $string
 * @return callable|string[]
 * @no-named-arguments
 */
function str_split_on($num, $string = null)
{
    InvalidArgumentException::assertPositiveInteger($num, __FUNCTION__, 1);

    if (is_null($string)) {
        return partial(str_split_on, $num);
    }

    InvalidArgumentException::assertString($string, __FUNCTION__, 2);

    $length = strlen($string);
    $output[0] = substr($string, 0, $num);
    $output[1] = substr($string, $num, $length);

    return $output;
}

define('Basko\Functional\str_split_on', __NAMESPACE__ . '\\str_split_on');

/**
 * Alias of native `str_replace`.
 *
 * Use `partial_p` if you need $count argument:
 * $f = partial_p('str_replace', [
 *  1 => $search,
 *  2 => $replace,
 *  4 => &$count
 * ]);
 *
 * @param array|string $search
 * @param array|string $replace
 * @param array|string $subject
 * @return array|string|string[]|callable
 * @no-named-arguments
 */
function str_replace($search, $replace = null, $subject = null)
{
    if (is_null($replace) && is_null($subject)) {
        return partial(str_replace, $search);
    } elseif (is_null($subject)) {
        return partial(str_replace, $search, $replace);
    }

    return \str_replace($search, $replace, $subject);
}

define('Basko\Functional\str_replace', __NAMESPACE__ . '\\str_replace');

/**
 * Checks if `$string` starts with `$token`.
 *
 * @param $token
 * @param $string
 * @return bool|callable
 * @no-named-arguments
 */
function str_starts_with($token, $string = null)
{
    InvalidArgumentException::assertString($token, __FUNCTION__, 1);

    if (is_null($string)) {
        return partial(str_starts_with, $token);
    }

    InvalidArgumentException::assertString($string, __FUNCTION__, 2);

    return strlen($token) <= strlen($string) && substr($string, 0, strlen($token)) === $token;
}

define('Basko\Functional\str_starts_with', __NAMESPACE__ . '\\str_starts_with');

/**
 * Checks if `$string` ends with `$token`.
 *
 * @param $token
 * @param $string
 * @return bool|callable
 * @no-named-arguments
 */
function str_ends_with($token, $string = null)
{
    InvalidArgumentException::assertString($token, __FUNCTION__, 1);

    if (is_null($string)) {
        return partial(str_ends_with, $token);
    }

    InvalidArgumentException::assertString($string, __FUNCTION__, 2);

    return strlen($token) <= strlen($string) && substr($string, -strlen($token)) === $token;
}

define('Basko\Functional\str_ends_with', __NAMESPACE__ . '\\str_ends_with');

/**
 * Checks if a string matches a regular expression.
 *
 * @param $pattern
 * @param $string
 * @return bool|callable
 * @no-named-arguments
 */
function str_test($pattern, $string = null)
{
    InvalidArgumentException::assertString($pattern, __FUNCTION__, 1);

    if (is_null($string)) {
        return partial(str_test, $pattern);
    }

    InvalidArgumentException::assertString($string, __FUNCTION__, 2);

    return 1 === preg_match($pattern, $string);
}

define('Basko\Functional\str_test', __NAMESPACE__ . '\\str_test');

/**
 * Alias of `str_pad`.
 *
 * @param $length
 * @param $pad_string
 * @param $string
 * @return callable|string
 * @no-named-arguments
 */
function str_pad_left($length, $pad_string = null, $string = null)
{
    InvalidArgumentException::assertPositiveInteger($length, __FUNCTION__, 1);

    if (is_null($pad_string) && is_null($string)) {
        return partial(str_pad_left, $length);
    } elseif (is_null($string)) {
        InvalidArgumentException::assertString($pad_string, __FUNCTION__, 2);
        return partial(str_pad_left, $length, $pad_string);
    }

    InvalidArgumentException::assertString($string, __FUNCTION__, 3);

    return str_pad($string, $length, $pad_string, STR_PAD_LEFT);
}

define('Basko\Functional\str_pad_left', __NAMESPACE__ . '\\str_pad_left');

/**
 * Alias of `str_pad`.
 *
 * @param $length
 * @param $pad_string
 * @param $string
 * @return callable|string
 * @no-named-arguments
 */
function str_pad_right($length, $pad_string = null, $string = null)
{
    InvalidArgumentException::assertPositiveInteger($length, __FUNCTION__, 1);

    if (is_null($pad_string) && is_null($string)) {
        return partial(str_pad_right, $length);
    } elseif (is_null($string)) {
        InvalidArgumentException::assertString($pad_string, __FUNCTION__, 2);
        return partial(str_pad_right, $length, $pad_string);
    }

    InvalidArgumentException::assertString($string, __FUNCTION__, 3);

    return str_pad($string, $length, $pad_string, STR_PAD_RIGHT);
}

define('Basko\Functional\str_pad_right', __NAMESPACE__ . '\\str_pad_right');
