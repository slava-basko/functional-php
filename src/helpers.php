<?php

namespace Basko\Functional;

use Basko\Functional\Exception\InvalidArgumentException;
use Traversable;

/**
 * @internal
 * @param object $value
 * @return string
 */
function _object_to_ref($value)
{
    /** @var object[]|\WeakReference[] $objectReferences */
    static $objectReferences = [];

    $hash = spl_object_hash($value);
    /**
     * spl_object_hash() will return the same hash twice in a single request if an object goes out of scope
     * and is destructed.
     */
    if (PHP_VERSION_ID >= 70400) {
        /**
         * For PHP >=7.4, we keep a weak reference to the relevant object that we use for hashing. Once the
         * object gets out of scope, the weak ref will no longer return the object, that’s how we know we
         * have a collision and increment a version in the collisions array.
         */
        /** @var int[] $collisions */
        static $collisions = [];

        if (isset($objectReferences[$hash])) {
            if ($objectReferences[$hash]->get() === null) {
                $collisions[$hash] = ($collisions[$hash] ?: 0) + 1;
                $objectReferences[$hash] = \WeakReference::create($value);
            }
        } else {
            $objectReferences[$hash] = \WeakReference::create($value);
        }

        $key = get_class($value) . ':' . $hash . ':' . (isset($collisions[$hash]) ? $collisions[$hash] : 0);
    } else {
        /**
         * For PHP < 7.4 we keep a static reference to the object so that cannot accidentally go out of
         * scope and mess with the object hashes
         */
        $objectReferences[$hash] = $value;
        $key = get_class($value) . ':' . $hash;
    }

    return $key;
}

/**
 * @internal
 * @param mixed $value
 * @return string
 */
function _value_to_ref($value, $key = null)
{
    $type = \gettype($value);
    if ($type === 'array') {
        $ref = '[' . implode(':', map(_value_to_ref, $value)) . ']';
    } elseif ($value instanceof Traversable) {
        $ref = _object_to_ref($value) . '[' . implode(':', map(_value_to_ref, $value)) . ']';
    } elseif ($type === 'object') {
        $ref = _object_to_ref($value);
    } elseif ($type === 'resource') {
        throw new \InvalidArgumentException(
            'Resource type cannot be used as part of a memoization key. Please pass a custom key instead'
        );
    } else {
        $ref = \serialize($value);
    }

    return ($key !== null ? (_value_to_ref($key) . '~') : '') . $ref;
}

/**
 * @internal
 */
define('Basko\Functional\_value_to_ref', __NAMESPACE__ . '\\_value_to_ref');

/**
 * @param mixed $value
 * @return string
 */
function value_to_key($value)
{
    return _value_to_ref($value);
}

/**
 * Create memoized versions of $f function.
 *
 * Note that memoization is safe for pure functions only. For a function to be
 * pure it should:
 *   1. Have no side effects
 *   2. Given the same arguments it should always return the same result
 *
 * Memoizing an impure function will lead to all kinds of hard to debug issues.
 *
 * In particular, the function to be memoized should never rely on a state of a
 * mutable object. Only immutable objects are safe.
 *
 * @param callable $f
 * @return callable
 * @no-named-arguments
 */
function memoized(callable $f)
{
    return function () use ($f) {
        static $cache = [];

        $args = func_get_args();
        $key = value_to_key(array_merge([$f], $args));

        if (!isset($cache[$key]) || !array_key_exists($key, $cache)) {
            $cache[$key] = call_user_func_array($f, $args);
        }

        return $cache[$key];
    };
}

define('Basko\Functional\memoize', __NAMESPACE__ . '\\memoize');

/**
 * @return array
 */
function to_list()
{
    return func_get_args();
}

define('Basko\Functional\to_list', __NAMESPACE__ . '\\to_list');

/**
 * Concatenates `$a` with `$b`.
 *
 * @param $a
 * @param $b
 * @return string
 * @no-named-arguments
 */
function concat($a, $b = null)
{
    if (is_null($b)) {
        return partial(concat, $a);
    }

    return $a . $b;
}

define('Basko\Functional\concat', __NAMESPACE__ . '\\concat');

/**
 * Concatenates given arguments.
 *
 * @param $a
 * @param $b
 * @return string
 * @no-named-arguments
 */
function concat_all($a, $b)
{
    return fold(concat, '', func_get_args());
}

define('Basko\Functional\concat_all', __NAMESPACE__ . '\\concat_all');

/**
 * Returns a string made by inserting the separator between each element and concatenating all the elements
 * into a single string.
 *
 * @param $separator
 * @param $list
 * @return callable
 * @no-named-arguments
 */
function join($separator, $list = null)
{
    if (is_null($list)) {
        return partial('implode', $separator);
    }
    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    if ($list instanceof Traversable) {
        $list = iterator_to_array($list);
    }

    return implode($separator, $list);
}

define('Basko\Functional\join', __NAMESPACE__ . '\\join');

/**
 * Performs an if/else condition over a value using functions as statements.
 *
 * @param callable $if the condition function
 * @param callable $then function to call if condition is true
 * @param callable $else function to call if condition is false
 *
 * @return mixed the return value of the given $then or $else functions
 * @no-named-arguments
 */
function if_else($if, $then = null, $else = null)
{
    if (is_null($then) && is_null($else)) {
        return partial(if_else, $if);
    } elseif (is_null($else)) {
        return partial(if_else, $if, $then);
    }
    InvalidArgumentException::assertCallback($else, __FUNCTION__, 3);

    return function () use ($if, $then, $else) {
        $args = func_get_args();
        return call_user_func_array($if, $args)
            ? call_user_func_array($then, $args)
            : call_user_func_array($else, $args);
    };
}

define('Basko\Functional\if_else', __NAMESPACE__ . '\\if_else');

/**
 * Creates a function that can be used to repeat the execution of $f.
 *
 * @param callable $f
 * @return callable
 * @no-named-arguments
 */
function repeat(callable $f)
{
    return function ($times) use ($f) {
        for ($i = 0; $i < (int)$times; $i++) {
            $f();
        }
    };
}

define('Basko\Functional\repeat', __NAMESPACE__ . '\\repeat');

/**
 * Takes two functions, a tryer and a catcher. The returned function evaluates the tryer. If it does not throw,
 * it simply returns the result. If the tryer does throw, the returned function evaluates the catcher function
 * and returns its result. For effective composition with this function, both the tryer and catcher functions
 * must return the same type of results.
 *
 * @param callable $tryer
 * @param callable $catcher
 * @return callable
 * @no-named-arguments
 */
function try_catch($tryer, $catcher = null)
{
    if (is_null($catcher)) {
        return partial(try_catch, $tryer);
    }
    InvalidArgumentException::assertCallback($tryer, __FUNCTION__, 1);
    InvalidArgumentException::assertCallback($catcher, __FUNCTION__, 2);

    return function () use ($tryer, $catcher) {
        $args = func_get_args();
        try {
            return call_user_func_array($tryer, $args);
        } catch (\Exception $exception) {
            return $catcher();
        }
    };
}

define('Basko\Functional\try_catch', __NAMESPACE__ . '\\try_catch');

/**
 * Returns a function that invokes method `$method` with arguments `$methodArguments` on the object.
 *
 * @param string $methodName
 * @param array $arguments
 * @return callable
 * @no-named-arguments
 */
function invoker($methodName, array $arguments = [])
{
    //todo: curry?
    return static function ($object) use ($methodName, $arguments) {
        return call_user_func_array([$object, $methodName], $arguments);
    };
}

define('Basko\Functional\invoker', __NAMESPACE__ . '\\invoker');

/**
 * Count length of string or number of elements in the array.
 *
 * @param $a
 * @return int
 * @no-named-arguments
 */
function len($a)
{
    if (is_string($a)) {
        return strlen($a);
    }

    if ($a instanceof Traversable) {
        $a = iterator_to_array($a);
    }

    return count($a);
}

define('Basko\Functional\len', __NAMESPACE__ . '\\len');

/**
 * Returns a function that when supplied an object returns the indicated property of that object, if it exists.
 *
 * @param $property
 * @param $object
 * @return mixed|null
 * @no-named-arguments
 */
function prop($property, $object = null)
{
    if (is_null($object)) {
        return partial(prop, $property);
    }

    if (is_object($object) && property_exists($object, $property)) {
        return $object->{$property};
    }

    if (isset($object[$property])) {
        return $object[$property];
    }

    return null;
}

define('Basko\Functional\prop', __NAMESPACE__ . '\\prop');

/**
 * Thunkified version of `prop` function, for more easily composition with `either` for example.
 *
 * @param $property
 * @param $object
 * @return callable
 */
function prop_thunk($property, $object = null)
{
    $prop_thunk = _thunkify_n(prop, 2);

    return $prop_thunk($property, $object);
}

define('Basko\Functional\prop_thunk', __NAMESPACE__ . '\\prop_thunk');

/**
 * Nested version of `prop` function.
 *
 * @param $path
 * @param $object
 * @return mixed
 * @no-named-arguments
 */
function prop_path($path, $object = null)
{
    if (is_null($object)) {
        return partial(prop_path, $path);
    }

    return fold(function ($object, $property) {
        return prop($property, $object);
    }, $object, $path);
}

define('Basko\Functional\prop_path', __NAMESPACE__ . '\\prop_path');

/**
 * Acts as multiple prop: array of keys in, array of values out. Preserves order.
 *
 * @param $properties
 * @param $object
 * @return callable|array
 * @no-named-arguments
 */
function props($properties, $object = null)
{
    if (is_null($object)) {
        return partial(props, $properties);
    }

    return fold(function ($accumulator, $property) use ($object) {
        $accumulator[] = prop($property, $object);

        return $accumulator;
    }, [], $properties);
}

define('Basko\Functional\props', __NAMESPACE__ . '\\props');

/**
 * Creates a shallow clone of a list with an overwritten value at a specified index.
 *
 * @param $key
 * @param $val
 * @param $list
 * @return mixed
 * @no-named-arguments
 */
function assoc($key, $val = null, $list = null)
{
    if (is_null($val) && is_null($list)) {
        return partial(assoc, $key);
    } elseif (is_null($list)) {
        return partial(assoc, $key, $val);
    }

    $possibleCopy = if_else(unary('is_object'), copy, identity);

    return fold(function ($accumulator, $entry, $index) use ($key, $val) {
        if (\is_object($accumulator)) {
            if ($key == $index) {
                $accumulator->{$index} = $entry;
            }

            $accumulator->{$key} = $val;
        } elseif (\is_array($accumulator)) {
            if ($key == $index) {
                $accumulator[$index] = $entry;
            }

            $accumulator[$key] = $val;
        }

        return $accumulator;
    }, $possibleCopy($list), $list);
}

define('Basko\Functional\assoc', __NAMESPACE__ . '\\assoc');

/**
 * Nested version of `assoc` function.
 *
 * @param array $path
 * @param $val
 * @param $list
 * @return mixed
 * @no-named-arguments
 */
function assoc_path($path, $val = null, $list = null)
{
    if (is_null($val) && is_null($list)) {
        return partial(assoc_path, $path);
    } elseif (is_null($list)) {
        return partial(assoc_path, $path, $val);
    }

    $path_len = count($path);
    if ($path_len == 0) {
        return $list;
    }

    $property = head($path);
    if ($path_len > 1) {
        $next = prop($property, $list);

        if (is_object($next) || is_array($next)) {
            array_shift($path);
            $val = assoc_path($path, $val, $next);
        }
    }

    return assoc($property, $val, $list);
}

define('Basko\Functional\assoc_path', __NAMESPACE__ . '\\assoc_path');

/**
 * Returns a function that invokes `$method` with arguments `$arguments` on the $object.
 *
 * @param object $object
 * @param string $methodName
 * @param ...
 * @return callable
 * @no-named-arguments
 */
function to_fn($object, $methodName = null, $arguments = null)
{
    //todo: curry?
    $args = func_get_args();
    array_shift($args);
    array_shift($args);
    $arguments = flatten($args);
    $invoker = invoker($methodName, $arguments);

    return function () use ($object, $invoker) {
        return $invoker($object);
    };
}

define('Basko\Functional\to_fn', __NAMESPACE__ . '\\to_fn');

/**
 * Takes two arguments, $fst and $snd, and returns [$fst, $snd].
 *
 * @param $fst
 * @param $snd
 * @return callable|array
 * @no-named-arguments
 */
function pair($fst, $snd = null)
{
    if (is_null($snd)) {
        return partial(pair, $fst);
    }

    return [$fst, $snd];
}

define('Basko\Functional\pair', __NAMESPACE__ . '\\pair');

/**
 * A function wrapping calls to the functions in an || operation, returning the result of the first function
 * if it is truth-y and the result of the next function otherwise.
 *
 * @return callable|mixed
 * @no-named-arguments
 */
function either()
{
    $all_functions = $functions = func_get_args();
    $arg = array_pop($functions);
    if (is_callable($arg)) {
        return function () use ($all_functions) {
            $args = func_get_args();
            foreach ($all_functions as $function) {
                if ($res = call_user_func_array($function, $args)) {
                    return $res;
                }
            }

            return null;
        };
    }

    foreach ($functions as $function) {
        if ($res = $function($arg)) {
            return $res;
        }
    }

    return null;
}

define('Basko\Functional\either', __NAMESPACE__ . '\\either');

/**
 * @param $value
 * @return string
 */
function quote($value)
{
    return '"' . $value . '"';
}

define('Basko\Functional\quote', __NAMESPACE__ . '\\quote');

/**
 * @param $value
 * @return string
 */
function safe_quote($value)
{
    return quote(addslashes($value));
}

define('Basko\Functional\safe_quote', __NAMESPACE__ . '\\safe_quote');

/**
 * Select the specified keys from the array.
 *
 * @param array $keys
 * @param Traversable|array $array
 * @return callable|array
 * @no-named-arguments
 */
function select_keys(array $keys, $array = null)
{
    if (is_null($array)) {
        return partial(select_keys, $keys);
    }
    InvalidArgumentException::assertList($array, __FUNCTION__, 2);

    if ($array instanceof Traversable) {
        $array = iterator_to_array($array);
    }

    return array_intersect_key($array, array_flip($keys));
}

define('Basko\Functional\select_keys', __NAMESPACE__ . '\\select_keys');

/**
 * Returns an array with the specified keys omitted from the array.
 *
 * @param array $keys
 * @param Traversable|array $array
 * @return callable|array
 * @no-named-arguments
 */
function omit_keys(array $keys, $array = null)
{
    if (is_null($array)) {
        return partial(omit_keys, $keys);
    }
    InvalidArgumentException::assertList($array, __FUNCTION__, 2);

    if ($array instanceof Traversable) {
        $array = iterator_to_array($array);
    }

    return array_diff_key($array, array_flip($keys));
}

define('Basko\Functional\omit_keys', __NAMESPACE__ . '\\omit_keys');

/**
 * Applies provided function to specified keys.
 *
 * @param callable $f
 * @param $keys
 * @param $object
 * @return callable|array
 * @no-named-arguments
 */
function map_keys(callable $f, $keys = null, $object = null)
{
    if (is_null($keys) && is_null($object)) {
        return partial(map_keys, $f);
    } elseif (is_null($object)) {
        return partial(map_keys, $f, $keys);
    }

    return array_merge(
        $object,
        map(unary($f), select_keys($keys, $object))
    );
}

define('Basko\Functional\map_keys', __NAMESPACE__ . '\\map_keys');

/**
 * Finds if a given array has all of the required keys set.
 *
 * @param $keys
 * @param $array
 * @return callable|int[]|string[]
 */
function find_missing_keys($keys, $array = null)
{
    InvalidArgumentException::assertList($keys, __FUNCTION__, 1);

    if (is_null($array)) {
        return partial(find_missing_keys, $keys);
    }

    InvalidArgumentException::assertList($array, __FUNCTION__, 2);

    return array_keys(array_diff_key(array_flip($keys), $array));
}

define('Basko\Functional\find_missing_keys', __NAMESPACE__ . '\\find_missing_keys');

/**
 * @param $instanceof
 * @param $object
 * @return bool|callable
 */
function instance_of($instanceof, $object = null)
{
    if (is_null($object)) {
        return partial(instance_of, $instanceof);
    }
    return $object instanceof $instanceof;
}

define('Basko\Functional\instance_of', __NAMESPACE__ . '\\instance_of');

/**
 * @param $object
 * @return mixed
 */
function copy($object)
{
    $cond = cond([
        ['is_object', function ($obj) {
            return clone $obj;
        }], // TODO: what todo with Traversable?
        ['is_array', identity],
        [T, identity],
    ]);
    return $cond($object);
}

define('Basko\Functional\copy', __NAMESPACE__ . '\\copy');

/**
 * Return random value from list.
 *
 * @param $list
 * @return \Closure
 */
function pick_random_value($list)
{
    InvalidArgumentException::assertList($list, __FUNCTION__, 1);

    return function () use ($list) {
        if ($list instanceof Traversable) {
            $list = iterator_to_array($list);
        }
        $index = rand(0, count($list) - 1);

        return $list[$index];
    };
}

define('Basko\Functional\pick_random_value', __NAMESPACE__ . '\\pick_random_value');
