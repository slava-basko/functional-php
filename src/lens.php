<?php

namespace Basko\Functional;

use Basko\Functional\Exception\InvalidArgumentException;
use Basko\Functional\Functor\Constant;
use Basko\Functional\Functor\Identity;

/**
 * Returns a lens for the given getter and setter functions.
 * The getter "gets" the value of the focus; the setter "sets" the value of the focus.
 *
 * ```php
 * $xLens = lens(prop('x'), assoc('x'));
 * view($xLens, ['x' => 1, 'y' => 2]); // 1
 * set($xLens, 4, ['x' => 1, 'y' => 2]); // ['x' => 4, 'y' => 2]
 * over($xLens, dec, ['x' => 1, 'y' => 2]); // ['x' => 0, 'y' => 2]
 * ```
 *
 * @param callable $getter
 * @param callable $setter
 * @return callable
 * @no-named-arguments
 */
function lens(callable $getter, callable $setter)
{
    return function (callable $func) use ($getter, $setter) {
        // apply functor function (Constant, Identity)
        return function ($list) use ($getter, $setter, $func) {
            // apply list (array, object)
            return call_user_func($func, $getter($list))
                ->map(
                    function ($replacement) use ($setter, $list) {
                        // apply setter to list item
                        return $setter($replacement, $list);
                    }
                );
        };
    };
}

define('Basko\Functional\lens', __NAMESPACE__ . '\\lens');

/**
 * Returns a "view" of the given data structure, determined by the given lens.
 *
 * ```php
 * $xLens = lens_prop('x');
 * view($xLens, ['x' => 1, 'y' => 2]); // 1
 * view($xLens, ['x' => 4, 'y' => 2]); // 4
 * ```
 *
 * @param callable $lens
 * @param mixed $store
 * @return mixed
 * @no-named-arguments
 */
function view(callable $lens, $store)
{
    $fn = $lens(Constant::of);
    $obj = $fn($store);

    return $obj->extract();
}

define('Basko\Functional\view', __NAMESPACE__ . '\\view');

/**
 * Returns the result of "setting" the portion of the given data structure
 * focused by the given lens to the result of applying the given function to the focused value.
 *
 * ```php
 * $xLens = lens_prop('x');
 * over($xLens, plus(100), ['x' => 1, 'y' => 2]); // ['x' => 101, 'y' => 2]
 * ```
 *
 * @param callable $lens
 * @param callable $operation
 * @param mixed $store
 * @return mixed
 * @no-named-arguments
 */
function over(callable $lens, callable $operation, $store)
{
    $fn = $lens(function ($res) use ($operation) {
        // transform value in lens context
        return Identity::of($operation($res));
    });
    $obj = $fn($store);

    return $obj->extract();
}

define('Basko\Functional\over', __NAMESPACE__ . '\\over');

/**
 * Returns the result of "setting" the portion of the given data structure focused by the given lens to the given value.
 *
 * ```php
 * $xLens = lens_prop('x');
 * set($xLens, 4, ['x' => 1, 'y' => 2]); // ['x' => 4, 'y' => 2]
 * set($xLens, 8, ['x' => 1, 'y' => 2]); // ['x' => 8, 'y' => 2]
 * ```
 *
 * @param callable $lens
 * @param mixed $value
 * @param mixed $store
 * @return mixed
 * @no-named-arguments
 */
function set(callable $lens, $value, $store)
{
    return over($lens, always($value), $store);
}

define('Basko\Functional\set', __NAMESPACE__ . '\\set');

/**
 * Returns a lens whose focus is the specified property.
 *
 * ```php
 * $xLens = lens_prop('x');
 * view($xLens, ['x' => 1, 'y' => 2]); // 1
 * set($xLens, 4, ['x' => 1, 'y' => 2]); // ['x' => 4, 'y' => 2]
 * over($xLens, dec, ['x' => 1, 'y' => 2]); // ['x' => 0, 'y' => 2]
 * ```
 *
 * @param string $property
 * @return callable
 * @no-named-arguments
 */
function lens_prop($property)
{
    InvalidArgumentException::assertString($property, __FUNCTION__, 1);

    return lens(prop($property), assoc($property));
}

define('Basko\Functional\lens_prop', __NAMESPACE__ . '\\lens_prop');

/**
 * Returns a lens whose focus is the specified path.
 *
 * ```php
 * $data = [
 *      'a' => 1,
 *      'b' => [
 *          'c' => 2
 *      ],
 * ];
 * $lens = lens_path(['b', 'c']);
 * view($lens, $data); // 2
 * view($lens, set($lens, 4, $data)); // ['a' => 1, 'b' => ['c' => 4]]
 * view($lens, over($lens, multiply(2), $data)); // ['a' => 1, 'b' => ['c' => 4]]
 * ```
 *
 * @param array $path
 * @return callable
 * @no-named-arguments
 */
function lens_path(array $path)
{
    return lens(prop_path($path), assoc_path($path));
}

define('Basko\Functional\lens_path', __NAMESPACE__ . '\\lens_path');
