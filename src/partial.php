<?php

namespace Basko\Functional;

/**
 * Returns new function which will behave like `$f` with
 * predefined left arguments passed to partial.
 *
 * ```php
 * $implode_coma = partial('implode', [',']);
 * $implode_coma([1, 2]); // 1,2
 * ```
 *
 * @param callable $f
 * @param array $args
 * @return callable(mixed):mixed
 * @no-named-arguments
 */
function partial(callable $f, array $args)
{
    return function () use ($f, $args) {
        return \call_user_func_array($f, \array_merge($args, \func_get_args()));
    };
}

define('Basko\Functional\partial', __NAMESPACE__ . '\\partial');

/**
 * Returns new partial function which will behave like `$f` with
 * predefined right arguments passed to rpartial.
 *
 * ```php
 * $implode12 = partial_r('implode', [[1, 2]]);
 * $implode12(','); // 1,2
 * ```
 *
 * @param callable $f
 * @param array $args
 * @return callable(mixed):mixed
 * @no-named-arguments
 */
function partial_r(callable $f, array $args)
{
    return function () use ($f, $args) {
        return \call_user_func_array($f, \array_merge(\func_get_args(), $args));
    };
}

define('Basko\Functional\partial_r', __NAMESPACE__ . '\\partial_r');

/**
 * Returns new partial function which will behave like `$f` with
 * predefined positional arguments passed to ppartial.
 *
 * ```php
 * $sub_abcdef_from = partial_p('substr', [
 *      1 => 'abcdef',
 *      3 => 2
 * ]);
 * $sub_abcdef_from(0); // 'ab'
 * ```
 *
 * @param callable $f
 * @param array $args Predefined positional args (position => value)
 * @return callable(mixed):mixed
 * @no-named-arguments
 */
function partial_p(callable $f, array $args)
{
    return function () use ($f, $args) {
        $_args = \func_get_args();
        $position = 1;
        do {
            if (!isset($args[$position]) && !\array_key_exists($position, $args)) {
                $args[$position] = \array_shift($_args);
            }
            ++$position;
        } while ($_args);
        \ksort($args);

        return \call_user_func_array($f, $args);
    };
}

define('Basko\Functional\partial_p', __NAMESPACE__ . '\\partial_p');
