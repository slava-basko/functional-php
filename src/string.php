<?php

namespace Basko\Functional;

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
    if (is_null($string)) {
        return partial(str_split, $separator);
    }

    return explode($separator, $string);
}

define('Basko\Functional\str_split', __NAMESPACE__ . '\\str_split');

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
    if (is_null($string)) {
        return partial(str_starts_with, $token);
    }

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
    if (is_null($string)) {
        return partial(str_ends_with, $token);
    }

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
    if (is_null($string)) {
        return partial(str_test, $pattern);
    }

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
    if (is_null($pad_string) && is_null($string)) {
        return partial(str_pad_left, $length);
    } elseif (is_null($string)) {
        return partial(str_pad_left, $length, $pad_string);
    }

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
    if (is_null($pad_string) && is_null($string)) {
        return partial(str_pad_right, $length);
    } elseif (is_null($string)) {
        return partial(str_pad_right, $length, $pad_string);
    }

    return str_pad($string, $length, $pad_string, STR_PAD_RIGHT);
}

define('Basko\Functional\str_pad_right', __NAMESPACE__ . '\\str_pad_right');
