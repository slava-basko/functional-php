<?php

namespace Functional;

/**
 * @param callable $getter
 * @param callable $setter
 * @return callable
 * @no-named-arguments
 */
function lens(callable $getter, callable $setter)
{
    return function ($func) use ($getter, $setter) {
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

define('Functional\lens', __NAMESPACE__ . '\\lens');

/**
 * @param callable $lens
 * @param $store
 * @return mixed
 * @no-named-arguments
 */
function view(callable $lens, $store)
{
    $fn = $lens(Constant::of);
    $obj = $fn($store);

    return $obj->extract();
}

define('Functional\view', __NAMESPACE__ . '\\view');

/**
 * @param callable $lens
 * @param callable $operation
 * @param $store
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

define('Functional\over', __NAMESPACE__ . '\\over');

/**
 * @param callable $lens
 * @param $value
 * @param $store
 * @return mixed
 * @no-named-arguments
 */
function set(callable $lens, $value, $store)
{
    return over($lens, always($value), $store);
}

define('Functional\set', __NAMESPACE__ . '\\set');

/**
 * @param $property
 * @return callable
 * @no-named-arguments
 */
function lens_prop($property)
{
    return lens(prop($property), assoc($property));
}

define('Functional\lens_prop', __NAMESPACE__ . '\\lens_prop');

/**
 * @param $path
 * @return callable
 * @no-named-arguments
 */
function lens_path($path)
{
    return lens(prop_path($path), assoc_path($path));
}

define('Functional\lens_path', __NAMESPACE__ . '\\lens_path');