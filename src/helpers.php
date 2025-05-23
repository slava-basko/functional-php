<?php

namespace Basko\Functional;

use Basko\Functional\Exception\InvalidArgumentException;
use Basko\Functional\Sequences\ExponentialSequence;
use Basko\Functional\Sequences\LinearSequence;

/**
 * Internal function.
 *
 * @param object $value
 * @return string
 * @internal
 */
function _object_to_ref($value)
{
    static $objectReferences = [];

    $hash = \spl_object_hash($value);
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
        static $collisions = [];

        if (isset($objectReferences[$hash])) {
            if ($objectReferences[$hash]->get() === null) {
                $collisions[$hash] = ($collisions[$hash] ?: 0) + 1;
                $objectReferences[$hash] = \WeakReference::create($value);
            }
        } else {
            $objectReferences[$hash] = \WeakReference::create($value);
        }

        $key = \get_class($value) . ':' . $hash . ':' . (isset($collisions[$hash]) ? $collisions[$hash] : 0);
    } else {
        /**
         * For PHP < 7.4 we keep a static reference to the object so that cannot accidentally go out of
         * scope and mess with the object hashes
         */
        $objectReferences[$hash] = $value;
        $key = \get_class($value) . ':' . $hash;
    }

    return $key;
}

/**
 * Internal function.
 *
 * @param mixed $value
 * @param mixed $key
 * @return string
 * @internal
 * @no-named-arguments
 */
function _value_to_ref($value, $key = null)
{
    $type = \gettype($value);
    if ($type === 'array') {
        $ref = '[' . \implode(':', map(_value_to_ref, $value)) . ']';
    } elseif ($value instanceof \Traversable) {
        $ref = _object_to_ref($value) . '[' . \implode(':', map(_value_to_ref, $value)) . ']';
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
 * @internal
 */
function _value_to_key($value)
{
    return _value_to_ref($value);
}

/**
 * Returns arguments as a list.
 *
 * ```
 * to_list(1, 2, 3); // [1, 2, 3]
 * to_list('1, 2, 3'); // [1, 2, 3]
 * ```
 *
 * @param mixed $args
 * @return array
 */
function to_list($args)
{
    if (\is_string($args)) {
        return \array_filter(
            \array_map('trim', \explode(',', $args)),
            'strlen'
        );
    }

    return \func_get_args();
}

define('Basko\Functional\to_list', __NAMESPACE__ . '\\to_list');

/**
 * Concatenates `$a` with `$b`.
 *
 * ```
 * concat('foo', 'bar'); // 'foobar'
 * ```
 *
 * @param string $a
 * @param string $b
 * @return string|callable
 * @no-named-arguments
 */
function concat($a, $b = null)
{
    InvalidArgumentException::assertString($a, __FUNCTION__, 1);

    if (\func_num_args() < 2) {
        $pfn = __FUNCTION__;

        return function ($b) use ($a, $pfn) {
            InvalidArgumentException::assertString($b, $pfn, 2);

            return $a . $b;
        };
    }

    InvalidArgumentException::assertString($b, __FUNCTION__, 2);

    return $a . $b;
}

define('Basko\Functional\concat', __NAMESPACE__ . '\\concat');

/**
 * Concatenates all given arguments.
 *
 * ```
 * concat('foo', 'bar', 'baz'); // 'foobarbaz'
 * ```
 *
 * @param string $a
 * @param string $b
 * @return string
 * @no-named-arguments
 */
function concat_all($a, $b)
{
    return fold(concat, '', \func_get_args());
}

define('Basko\Functional\concat_all', __NAMESPACE__ . '\\concat_all');

/**
 * Returns a string made by inserting the separator between each element and concatenating all the elements
 * into a single string.
 *
 * ```
 * join('|', [1, 2, 3]); // '1|2|3'
 * ```
 *
 * @param string $separator
 * @param array|\Traversable $list
 * @return string|callable
 * @no-named-arguments
 */
function join($separator, $list = null)
{
    InvalidArgumentException::assertString($separator, __FUNCTION__, 1);

    if (\func_num_args() < 2) {
        return partial(join, $separator);
    }

    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    $list = $list instanceof \Traversable ? \iterator_to_array($list) : $list;

    return \implode($separator, $list);
}

define('Basko\Functional\join', __NAMESPACE__ . '\\join');

/**
 * Performs an `if/else` condition over a value using functions as statements.
 *
 * ```
 * $ifFoo = if_else(eq('foo'), always('bar'), always('baz'));
 * $ifFoo('foo'); // 'bar'
 * $ifFoo('qux'); // 'baz'
 * ```
 *
 * @param callable $if the condition function
 * @param callable $then function to call if condition is true
 * @param callable $else function to call if condition is false
 * @return callable The return value of the given $then or $else functions
 * @no-named-arguments
 */
function if_else(callable $if, $then = null, $else = null)
{
    $n = \func_num_args();
    if ($n === 1) {
        return partial(if_else, $if);
    } elseif ($n === 2) {
        return partial(if_else, $if, $then);
    }

    InvalidArgumentException::assertCallable($then, __FUNCTION__, 2);
    InvalidArgumentException::assertCallable($else, __FUNCTION__, 3);

    return function () use ($if, $then, $else) {
        $args = \func_get_args();

        /**
         * @var callable $then
         * @var callable $else
         */
        return \call_user_func_array($if, $args)
            ? \call_user_func_array($then, $args)
            : \call_user_func_array($else, $args);
    };
}

define('Basko\Functional\if_else', __NAMESPACE__ . '\\if_else');

/**
 * Creates a function that can be used to repeat the execution of `$f`.
 *
 * ```
 * repeat(thunkify('print_r')('Hello'))(3); // Print 'Hello' 3 times
 * ```
 *
 * @param callable $f
 * @return callable
 * @no-named-arguments
 */
function repeat(callable $f)
{
    $pfn = __FUNCTION__;

    return function ($times) use ($f, $pfn) {
        InvalidArgumentException::assertInteger($times, concat('Callable created by ', $pfn), 1);

        for ($i = 0; $i < $times; $i++) {
            \call_user_func($f);
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
 * ```
 * try_catch(function () {
 *      throw new \Exception();
 * }, always('val'))(); // 'val'
 * ```
 *
 * @param callable $tryer
 * @param callable $catcher
 * @return callable
 * @no-named-arguments
 */
function try_catch(callable $tryer, $catcher = null)
{
    if (\func_num_args() < 2) {
        return partial(try_catch, $tryer);
    }

    InvalidArgumentException::assertCallable($catcher, __FUNCTION__, 2);

    return function () use ($tryer, $catcher) {
        $args = \func_get_args();

        \set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            $errLvl = \error_reporting();
            $okLvl = 0; // Prior to PHP 8.0.0 https://www.php.net/manual/en/language.operators.errorcontrol.php
            if (PHP_VERSION_ID >= 80000) {
                $okLvl = E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR | E_PARSE;
            }
            // error was suppressed with the @-operator
            if ($errLvl === $okLvl) {
                return false;
            }

            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        });

        if (PHP_VERSION_ID >= 70000) {
            try {
                $result = \call_user_func_array($tryer, $args);
            } catch (\Throwable $exception) {
                $result = \call_user_func_array($catcher, [$exception]);
            }
        } else {
            try {
                $result = \call_user_func_array($tryer, $args);
            } catch (\Exception $exception) {
                $result = \call_user_func_array($catcher, [$exception]);
            }
        }

        \restore_error_handler();

        return $result;
    };
}

define('Basko\Functional\try_catch', __NAMESPACE__ . '\\try_catch');

/**
 * Returns a function that invokes method `$method` with arguments `$methodArguments` on the object.
 *
 * ```
 * array_filter([$user1, $user2], invoker('isActive')); // only active users
 * ```
 *
 * @param string $methodName
 * @param array $arguments
 * @return callable
 * @no-named-arguments
 */
function invoker($methodName, array $arguments = [])
{
    InvalidArgumentException::assertMethodName($methodName, __FUNCTION__, 1);

    $pfn = 'Function created by ' . __FUNCTION__;

    return static function ($object) use ($methodName, $arguments, $pfn) {
        InvalidArgumentException::assertObject($object, $pfn, 1);

        return \call_user_func_array([$object, $methodName], $arguments);
    };
}

define('Basko\Functional\invoker', __NAMESPACE__ . '\\invoker');

/**
 * Count length of string or number of elements in the array.
 *
 * ```
 * len('foo'); // 3
 * len(['a', 'b']); // 2
 * ```
 *
 * @param string|array|\Traversable $a
 * @return int
 * @no-named-arguments
 */
function len($a)
{
    InvalidArgumentException::assertStringOrList($a, __FUNCTION__, 1);

    if (\is_string($a)) {
        return \strlen($a);
    }

    if ($a instanceof \Countable) {
        return $a->count();
    }

    $a = $a instanceof \Traversable ? \iterator_to_array($a) : $a;

    return \count($a);
}

define('Basko\Functional\len', __NAMESPACE__ . '\\len');

/**
 * Returns a function that when supplied an object returns the indicated property of that object, if it exists.
 *
 * ```
 * prop(0, [99]); // 99
 * prop('x', ['x' => 100]); // 100
 * $object = new \stdClass();
 * $object->x = 101;
 * prop('x', $object); // 101
 * ```
 *
 * @param string|int $property
 * @param array|object $object
 * @return mixed|callable
 * @no-named-arguments
 */
function prop($property, $object = null)
{
    InvalidArgumentException::assertString($property, __FUNCTION__, 1);

    if (\func_num_args() < 2) {
        return partial(prop, $property);
    }

    if (\is_object($object) && \property_exists($object, $property)) {
        return $object->{$property};
    }

    if ($object instanceof \ArrayAccess) {
        return $object->offsetGet($property);
    }

    if (\is_array($object) && \array_key_exists($property, $object)) {
        return $object[$property];
    }

    return null;
}

define('Basko\Functional\prop', __NAMESPACE__ . '\\prop');

/**
 * Thunkified version of `prop` function, for more easily composition with `either` for example.
 *
 * ```
 * prop_thunk(0, [99])(); // 99
 * ```
 *
 * @param string|int $property
 * @param array|object $object
 * @return callable
 * @no-named-arguments
 */
function prop_thunk($property, $object)
{
    InvalidArgumentException::assertString($property, __FUNCTION__, 1);

    $propThunk = _thunkify_n(prop, 2);

    return $propThunk($property, $object);
}

define('Basko\Functional\prop_thunk', __NAMESPACE__ . '\\prop_thunk');

/**
 * Nested version of `prop` function.
 *
 * ```
 * prop_path(['b', 'c'], [
 *      'a' => 1,
 *      'b' => [
 *          'c' => 2
 *      ],
 * ]); // 2
 * ```
 *
 * @param array $path
 * @param array|object $object
 * @return mixed|callable
 * @no-named-arguments
 */
function prop_path(array $path, $object = null)
{
    if (\func_num_args() < 2) {
        return partial(prop_path, $path);
    }

    foreach ($path as $pathItem) {
        $object = prop($pathItem, $object);
    }

    return $object;
}

define('Basko\Functional\prop_path', __NAMESPACE__ . '\\prop_path');

/**
 * Acts as multiple `prop`: array of keys in, array of values out. Preserves order.
 *
 * ```
 * props(['c', 'a', 'b'], ['b' => 2, 'a' => 1]); // [null, 1, 2]
 * ```
 *
 * @param array $properties
 * @param array|object $object
 * @return array|callable
 * @no-named-arguments
 */
function props(array $properties, $object = null)
{
    if (\func_num_args() < 2) {
        return partial(props, $properties);
    }

    $accumulator = [];

    foreach ($properties as $property) {
        $accumulator[] = prop($property, $object);
    }

    return $accumulator;
}

define('Basko\Functional\props', __NAMESPACE__ . '\\props');

/**
 * Creates a shallow clone of a list with an overwritten value at a specified index.
 *
 * ```
 * assoc('bar', 42, ['foo' => 'foo', 'bar' => 'bar']); // ['foo' => 'foo', 'bar' => 42]
 *
 * assoc(
 *      'full_name',
 *      compose(join(' '), props(['first_name', 'last_name'])),
 *      [
 *          'first_name' => 'Slava',
 *          'last_name' => 'Basko'
 *      ]
 * ); // ['first_name' => 'Slava', 'last_name' => 'Basko', 'full_name' => 'Slava Basko']
 * ```
 *
 * @param string|int $key
 * @param mixed|callable $val
 * @param array|\Traversable|object $list
 * @return array|object|callable
 * @no-named-arguments
 */
function assoc($key, $val = null, $list = null)
{
    InvalidArgumentException::assertString($key, __FUNCTION__, 1);

    $n = \func_num_args();
    if ($n === 1) {
        return partial(assoc, $key);
    } elseif ($n === 2) {
        return partial(assoc, $key, $val);
    }

    InvalidArgumentException::assertList($list, __FUNCTION__, 3);

    $list = $list instanceof \Traversable ? \iterator_to_array($list) : $list;

    if (\is_object($list)) {
        $newList = cp($list);
        $newList->{$key} = \is_callable($val) ? $val($newList) : $val;

        return $newList;
    } else {
        $list[$key] = \is_callable($val) ? $val($list) : $val;
    }

    return $list;
}

define('Basko\Functional\assoc', __NAMESPACE__ . '\\assoc');

/**
 * Same as `assoc`, but it allows to specify element by its number rather than named key.
 *
 * ```
 * assoc_element(1, 999, [10, 20, 30]); // [999, 20, 30]
 * assoc_element(-1, 999, [10, 20, 30]); // [10, 20, 999]
 * ```
 *
 * @param integer $key
 * @param mixed|callable $val
 * @param array|\Traversable $list
 * @return array|object|callable
 * @no-named-arguments
 */
function assoc_element($key, $val = null, $list = null)
{
    InvalidArgumentException::assertInteger($key, __FUNCTION__, 1);

    $n = \func_num_args();
    if ($n === 1) {
        return partial(assoc_element, $key);
    } elseif ($n === 2) {
        return partial(assoc_element, $key, $val);
    }

    InvalidArgumentException::assertList($list, __FUNCTION__, 3);

    $list = $list instanceof \Traversable ? \iterator_to_array($list) : $list;

    if (\is_object($list)) {
        throw new InvalidArgumentException(
            \sprintf('%s() expects parameter 3 to be array or Iterator', __FUNCTION__)
        );
    }

    if ($key < 0) {
        $key = \count($list) - \abs($key);
    } else {
        $key = $key - 1;
    }

    return assoc($key, $val, $list);
}

define('Basko\Functional\assoc_element', __NAMESPACE__ . '\\assoc_element');

/**
 * Nested version of `assoc` function.
 *
 * ```
 * assoc_path(['bar', 'baz'], 42, ['foo' => 'foo', 'bar' => ['baz' => 41]]); // ['foo' => 'foo', 'bar' => ['baz' => 42]]
 * ```
 *
 * @param array $path
 * @param mixed|callable $val
 * @param array|\Traversable|object $list
 * @return array|object|callable
 * @no-named-arguments
 */
function assoc_path(array $path, $val = null, $list = null)
{
    $n = \func_num_args();
    if ($n === 1) {
        return partial(assoc_path, $path);
    } elseif ($n === 2) {
        return partial(assoc_path, $path, $val);
    }

    InvalidArgumentException::assertList($list, __FUNCTION__, 3);

    $pathLen = \count($path);
    if ($pathLen == 0) {
        return $list;
    }

    $property = head($path);
    if ($pathLen > 1) {
        $next = prop($property, $list);

        if (\is_object($next) || \is_array($next)) {
            \array_shift($path);
            $val = assoc_path($path, $val, $next);
        }
    }

    return assoc($property, $val, $list);
}

define('Basko\Functional\assoc_path', __NAMESPACE__ . '\\assoc_path');

/**
 * Returns a function that invokes `$method` with arguments `$arguments` on the $object.
 *
 * ```
 * to_fn($obj, 'someMethod', ['arg'])(); // Equal to $obj->someMethod('arg');
 * ```
 *
 * @param object $object
 * @param string $methodName
 * @param array|null $arguments
 * @return callable
 * @no-named-arguments
 */
function to_fn($object, $methodName = null, $arguments = null)
{
    $n = \func_num_args();
    if ($n === 1) {
        return partial(to_fn, $object);
    } elseif ($n === 2) {
        return partial(to_fn, $object, $methodName);
    }

//    $args = \func_get_args();
//    \array_shift($args);
//    \array_shift($args);
//    $arguments = flatten($args);

    InvalidArgumentException::assertString($methodName, __FUNCTION__, 2);
    InvalidArgumentException::assertArray($arguments, __FUNCTION__, 3);

    $pfn = __FUNCTION__;
    return function () use ($pfn, $object, $methodName, $arguments) {
        if (\is_null($arguments)) {
            $arguments = [];
        }
        if (\is_callable([$object, $methodName])) {
            return \call_user_func_array([$object, $methodName], $arguments);
        }

        throw new InvalidArgumentException(
            \sprintf(
                'Combination of arguments 1 and 2 passed to %s is not callable, %s given',
                $pfn,
                \gettype([$object, $methodName])
            )
        );
    };
}

define('Basko\Functional\to_fn', __NAMESPACE__ . '\\to_fn');

/**
 * Takes two arguments, `$fst` and `$snd`, and returns `[$fst, $snd]`.
 *
 * ```
 * pair('foo', 'bar'); // ['foo', 'bar']
 * ```
 *
 * @param mixed $fst
 * @param mixed $snd
 * @return array|callable
 * @no-named-arguments
 */
function pair($fst, $snd = null)
{
    if (\func_num_args() < 2) {
        return function ($snd) use ($fst) {
            return [$fst, $snd];
        };
    }

    return [$fst, $snd];
}

define('Basko\Functional\pair', __NAMESPACE__ . '\\pair');

/**
 * @return callable
 * @internal
 */
function _either()
{
    $arguments = \func_get_args();
    $strict = (bool)\array_shift($arguments);
    $callee = \array_shift($arguments);
    $functions = $arguments;

    InvalidArgumentException::assertListOfCallables(
        $functions,
        $callee,
        InvalidArgumentException::ALL
    );

    return function () use ($strict, $functions) {
        $args = \func_get_args();
        foreach ($functions as $function) {
            $res = \call_user_func_array($function, $args);
            if ($strict && !\is_null($res)) {
                return $res;
            } elseif ($res) {
                return $res;
            }
        }

        return $strict ? null : $res;
    };
}

/**
 * A function wrapping calls to the functions in an `||` operation, returning the result of the first function
 * if it is truth-y and the result of the next function otherwise.
 * Note: Will return result of the last function if all fail.
 *
 * ```
 * $value = either(prop('prop1'), prop('prop2'), prop('prop3'));
 * $value([
 *      'prop2' => 'some value'
 * ]); // 'some value'
 * ```
 *
 * @return callable
 * @no-named-arguments
 */
function either()
{
    $functions = \func_get_args();
    InvalidArgumentException::assertListOfCallables($functions, __FUNCTION__, InvalidArgumentException::ALL);

    return \call_user_func_array('Basko\Functional\_either', \array_merge([false, __FUNCTION__], $functions));
}

define('Basko\Functional\either', __NAMESPACE__ . '\\either');

/**
 * The same as `either`, but returning the result of the first function
 * if it is not NULL and the result of the next function otherwise.
 * Note: Will return NULL if all fail.
 *
 * @return callable
 * @no-named-arguments
 */
function either_strict()
{
    $functions = \func_get_args();
    InvalidArgumentException::assertListOfCallables($functions, __FUNCTION__, InvalidArgumentException::ALL);

    return \call_user_func_array('Basko\Functional\_either', \array_merge([true, __FUNCTION__], $functions));
}

define('Basko\Functional\either_strict', __NAMESPACE__ . '\\either_strict');

/**
 * Quote given string.
 *
 * ```
 * quote('foo'); // "foo"
 * map(quote, ['foo', 'bar']); // ['"foo"', '"bar"']
 * ```
 *
 * @param string $value
 * @return string
 */
function quote($value)
{
    InvalidArgumentException::assertString($value, __FUNCTION__, 1);

    return '"' . $value . '"';
}

define('Basko\Functional\quote', __NAMESPACE__ . '\\quote');

/**
 * Same as `quote`, but with `addslashes` before.
 *
 * @param string $value
 * @return string
 */
function safe_quote($value)
{
    InvalidArgumentException::assertString($value, __FUNCTION__, 1);

    return quote(\addslashes($value));
}

define('Basko\Functional\safe_quote', __NAMESPACE__ . '\\safe_quote');

/**
 * Returns an array only with the specified keys.
 *
 * ```
 * only_keys(['bar', 'baz'], ['foo' => 1, 'bar' => 2, 'baz' => 3]); // ['bar' => 2, 'baz' => 3]
 * ```
 *
 * @param array $keys
 * @param array|\Traversable|object $object
 * @return array|callable
 * @no-named-arguments
 */
function only_keys(array $keys, $object = null)
{
    if (\func_num_args() < 2) {
        return partial(only_keys, $keys);
    }
    InvalidArgumentException::assertList($object, __FUNCTION__, 2);

    $object = $object instanceof \Traversable ? \iterator_to_array($object) : $object;

    $aggregation = [];
    foreach ($keys as $key) {
        if (\is_array($object) && \array_key_exists($key, $object)) {
            $aggregation[$key] = $object[$key];
        } elseif (\is_object($object) && \is_string($key) && \property_exists($object, $key)) {
            $aggregation[$key] = $object->{$key};
        }
    }

    return $aggregation;
}

define('Basko\Functional\only_keys', __NAMESPACE__ . '\\only_keys');

/**
 * Drops specified keys.
 *
 * ```
 * omit_keys(['baz'], ['foo' => 1, 'bar' => 2, 'baz' => 3]); // ['foo' => 1, 'bar' => 2]
 * ```
 *
 * @param array $keys
 * @param array|\Traversable|object $object
 * @return array|callable
 * @no-named-arguments
 */
function omit_keys(array $keys, $object = null)
{
    if (\func_num_args() < 2) {
        return partial(omit_keys, $keys);
    }
    InvalidArgumentException::assertList($object, __FUNCTION__, 2);

    if ($object instanceof \Traversable) {
        $object = \iterator_to_array($object);
    } elseif (\is_object($object)) {
        $object = \get_object_vars($object);
    }

    return \array_diff_key($object, \array_flip($keys));
}

define('Basko\Functional\omit_keys', __NAMESPACE__ . '\\omit_keys');

/**
 * Applies provided function to specified keys.
 *
 * ```
 * map_keys('strtoupper', ['foo'], ['foo' => 'val1', 'bar' => 'val2']); // ['foo' => 'VAL1', 'bar' => 'val2']
 * ```
 *
 * @param callable $f
 * @param array $keys
 * @param array|\Traversable|object $list
 * @return array|object|callable
 * @no-named-arguments
 */
function map_keys(callable $f, $keys = null, $list = null)
{
    $n = \func_num_args();
    if ($n === 1) {
        return partial(map_keys, $f);
    } elseif ($n === 2) {
        return partial(map_keys, $f, $keys);
    }

    InvalidArgumentException::assertList($keys, __FUNCTION__, 2);
    InvalidArgumentException::assertList($list, __FUNCTION__, 3);

    $list = $list instanceof \Traversable ? \iterator_to_array($list) : $list;

    foreach ($keys as $key) {
        if (\is_object($list)) {
            $list->{$key} = \call_user_func_array($f, [$list->{$key}]);
        } else {
            $list[$key] = \call_user_func_array($f, [$list[$key]]);
        }
    }

    return $list;
}

define('Basko\Functional\map_keys', __NAMESPACE__ . '\\map_keys');

/**
 * Applies provided function to N-th elements of an array.
 * First element is first, but not zero (similar to `nth` function).
 *
 * ```
 * map_elements('strtoupper', [1], ['foo' => 'val1', 'bar' => 'val2']); // ['foo' => 'VAL1', 'bar' => 'val2']
 * ```
 *
 * @param callable $f
 * @param array $elementsNumbers
 * @param array|\Traversable $list
 * @return array|callable
 * @no-named-arguments
 */
function map_elements(callable $f, $elementsNumbers = null, $list = null)
{
    $n = \func_num_args();
    if ($n === 1) {
        return partial(map_elements, $f);
    } elseif ($n === 2) {
        return partial(map_elements, $f, $elementsNumbers);
    }

    InvalidArgumentException::assertList($elementsNumbers, __FUNCTION__, 2);
    InvalidArgumentException::assertArrayAccess($list, __FUNCTION__, 3);

    $list = $list instanceof \Traversable ? \iterator_to_array($list) : $list;

    foreach ($elementsNumbers as $elementNumber) {
        if ($elementNumber < 0) {
            $internalElementNumber = len($list) - \abs($elementNumber);
        } else {
            $internalElementNumber = $elementNumber - 1;
        }

        $list[$internalElementNumber] = \call_user_func_array($f, [nth($elementNumber, $list)]);
    }

    return $list;
}

define('Basko\Functional\map_elements', __NAMESPACE__ . '\\map_elements');

/**
 * Finds if a given array has all of the required keys set.
 *
 * ```
 * find_missing_keys(
 *      ['login', 'email'],
 *      ['login' => 'admin']
 * ); // ['email']
 * ```
 *
 * @param array $required
 * @param array|\Traversable $array
 * @return array|callable
 * @no-named-arguments
 */
function find_missing_keys(array $required, $array = null)
{
    if (\func_num_args() < 2) {
        return partial(find_missing_keys, $required);
    }

    InvalidArgumentException::assertList($array, __FUNCTION__, 2);

    $array = $array instanceof \Traversable ? \iterator_to_array($array) : $array;

    return \array_keys(\array_diff_key(\array_flip($required), $array));
}

define('Basko\Functional\find_missing_keys', __NAMESPACE__ . '\\find_missing_keys');

/**
 * Creates copy of provided value. `clone` will be called for objects.
 * You can overwrite `clone` and provide your specific function, just define `CLONE_FUNCTION` constant.
 *
 * ```
 * $obj = new \stdClass();  // object hash: 00000000000000030000000000000000
 * cp($obj);                // object hash: 00000000000000070000000000000000
 * ```
 *
 * @param mixed $object
 * @return mixed
 * @no-named-arguments
 */
function cp($object)
{
    $cond = cond([
        ['is_object', function ($obj) {
            if (\defined('CLONE_FUNCTION')) {
                return \call_user_func_array(\constant('CLONE_FUNCTION'), [$obj]);
            }

            return clone $obj;
        }], // TODO: what todo with Traversable?
        ['is_array', identity],
        [T, identity],
    ]);

    return $cond($object);
}

define('Basko\Functional\cp', __NAMESPACE__ . '\\cp');

/**
 * Return random value from list.
 *
 * ```
 * pick_random_value(['sword', 'gold', 'ring', 'jewel']); // 'gold'
 * ```
 *
 * @param array|\Traversable $list
 * @return mixed
 */
function pick_random_value($list)
{
    InvalidArgumentException::assertList($list, __FUNCTION__, 1);

    $list = $list instanceof \Traversable ? \iterator_to_array($list) : $list;

    if (\class_exists('Random\Randomizer')) {
        $randomizer = new \Random\Randomizer();
        $randomKeys = $randomizer->pickArrayKeys($list, 1);

        return $list[$randomKeys[0]];
    }

    $fN = \function_exists('mt_rand') ? 'mt_rand' : 'rand';

    $randomElement = nth(
        \call_user_func_array($fN, [1, \count($list)]),
        $list
    );

    return $randomElement;
}

define('Basko\Functional\pick_random_value', __NAMESPACE__ . '\\pick_random_value');

/**
 * Creates an associative array using a `$keyProp` as the path to build its keys,
 * and `$valueProp` as path to get the values.
 *
 * ```
 * combine('alpha2', 'name', [
 *      [
 *          'name' => 'Netherlands',
 *          'alpha2' => 'NL',
 *          'alpha3' => 'NLD',
 *          'numeric' => '528',
 *      ],
 *      [
 *          'name' => 'Ukraine',
 *          'alpha2' => 'UA',
 *          'alpha3' => 'UKR',
 *          'numeric' => '804',
 *      ],
 * ]); // ['NL' => 'Netherlands', 'UA' => 'Ukraine']
 * ```
 *
 * @param string $keyProp
 * @param string $valueProp
 * @param array|\Traversable $list
 * @return array|callable
 * @no-named-arguments
 */
function combine($keyProp, $valueProp = null, $list = null)
{
    InvalidArgumentException::assertString($keyProp, __FUNCTION__, 1);

    $n = \func_num_args();
    if ($n === 1) {
        return partial(combine, $keyProp);
    } elseif ($n === 2) {
        return partial(combine, $keyProp, $valueProp);
    }

    InvalidArgumentException::assertString($valueProp, __FUNCTION__, 2);
    InvalidArgumentException::assertList($list, __FUNCTION__, 3);

    return \array_combine(pluck($keyProp, $list), pluck($valueProp, $list));
}

define('Basko\Functional\combine', __NAMESPACE__ . '\\combine');

/**
 * Returns an infinite, traversable sequence of constant values.
 *
 * @param int $value
 * @return \Traversable
 */
function sequence_constant($value)
{
    if (!\is_int($value) || $value < 0) {
        throw new \InvalidArgumentException(
            'sequence_constant() expects $value argument to be an integer, greater than or equal to 0'
        );
    }

    return new \InfiniteIterator(new \ArrayIterator([$value]));
}

define('Basko\Functional\sequence_constant', __NAMESPACE__ . '\\sequence_constant');

/**
 * Returns an infinite, traversable sequence that linearly grows by given amount.
 *
 * @param int $start
 * @param int $amount
 * @return LinearSequence
 * @throws \InvalidArgumentException
 * @no-named-arguments
 */
function sequence_linear($start, $amount)
{
    return new LinearSequence($start, $amount);
}

define('Basko\Functional\sequence_linear', __NAMESPACE__ . '\\sequence_linear');

/**
 * Returns an infinite, traversable sequence that exponentially grows by given percentage.
 *
 * @param int $start
 * @param int $percentage Integer between 1 and 100
 * @return ExponentialSequence
 * @throws \InvalidArgumentException
 * @no-named-arguments
 */
function sequence_exponential($start, $percentage)
{
    return new ExponentialSequence($start, $percentage);
}

define('Basko\Functional\sequence_exponential', __NAMESPACE__ . '\\sequence_exponential');

/**
 * Returns an infinite, traversable sequence of 0.
 * This helper mostly to use with `retry`.
 *
 * @return \Traversable
 */
function no_delay()
{
    return sequence_constant(0);
}

define('Basko\Functional\no_delay', __NAMESPACE__ . '\\no_delay');

/**
 * Retry a function until the number of retries are reached or the function does no longer throw an exception.
 *
 * ```
 * retry(3, no_delay, [$db, 'connect']); // Runs `$db->connect()` 3 times without delay (if method throw exception)
 * retry(3, sequence_linear(1, 5), [$ftp, 'upload']); // Runs `$ftp->upload()` 3 times with a linear back-off
 * ```
 *
 * @param int $retries
 * @param \Iterator $delaySequence
 * @param callable $f
 * @return callable|mixed Return value of the function
 * @throws InvalidArgumentException
 * @throws \Exception Any exception thrown by the callback
 * @no-named-arguments
 */
function retry($retries, $delaySequence = null, $f = null)
{
    $n = \func_num_args();
    if ($n === 1) {
        return partial(retry, $retries);
    } elseif ($n === 2) {
        return partial(retry, $retries, $delaySequence);
    }

    InvalidArgumentException::assertIntegerGreaterThanOrEqual($retries, 1, __FUNCTION__, 1);
    InvalidArgumentException::assertList($delaySequence, __FUNCTION__, 2);
    InvalidArgumentException::assertCallable($f, __FUNCTION__, 3);

    $delays = new \AppendIterator();
    $delays->append(new \InfiniteIterator($delaySequence));
    $delays->append(new \InfiniteIterator(new \ArrayIterator([0])));
    $delays = new \LimitIterator($delays, 0, $retries);

    $retry = 0;
    foreach ($delays as $delay) {
        try {
            return \call_user_func_array($f, [$retry, $delay]);
        } catch (\Exception $e) {
            if ($retry === $retries - 1) {
                throw $e;
            }
        }

        if ($delay > 0) {
            \usleep($delay);
        }

        $retry++;
    }
}

define('Basko\Functional\retry', __NAMESPACE__ . '\\retry');

/**
 * Creates instance of given class.
 *
 * ```
 * construct('stdClass'); // object(stdClass)
 * ```
 *
 * @param string $class
 * @return object
 */
function construct($class)
{
    InvalidArgumentException::assertClass($class, __FUNCTION__, 1);

    return new $class();
}

define('Basko\Functional\construct', __NAMESPACE__ . '\\construct');

/**
 * Creates instance of given class with arguments passed to `__construct` method.
 *
 * ```
 * $user = construct_with_args(User::class, ['first_name' => 'Slava', 'last_name' => 'Basko']);
 * echo $user->first_name; // Slava
 * ```
 *
 * @param string $class
 * @param mixed $constructArguments
 * @return object|callable
 * @no-named-arguments
 */
function construct_with_args($class, $constructArguments = null)
{
    $args = \func_get_args();

    if (\count($args) < 2) {
        return partial(construct_with_args, $class);
    }

    InvalidArgumentException::assertClass($class, __FUNCTION__, 1);

    $constructArgs = \array_slice($args, 1);
    return (new \ReflectionClass($class))->newInstanceArgs($constructArgs);
}

define('Basko\Functional\construct_with_args', __NAMESPACE__ . '\\construct_with_args');

/**
 * Swaps the values of keys `a` and `b`.
 *
 * ```
 * flip_values('key1', 'key2', ['key1' => 'val1', 'key2' => 'val2']); // ['key1' => 'val2', 'key2' => 'val1']
 * ```
 *
 * @param string $keyA
 * @param string $keyB
 * @param array|object $object
 * @return array|object|callable
 */
function flip_values($keyA, $keyB = null, $object = null)
{
    InvalidArgumentException::assertString($keyA, __FUNCTION__, 1);

    $n = \func_num_args();
    if ($n === 1) {
        return partial(flip_values, $keyA);
    } elseif ($n === 2) {
        return partial(flip_values, $keyA, $keyB);
    }

    InvalidArgumentException::assertString($keyB, __FUNCTION__, 2);
    InvalidArgumentException::assertList($object, __FUNCTION__, 3);

    $valueA = prop($keyA, $object);
    $valueB = prop($keyB, $object);

    return assoc($keyB, $valueA, assoc($keyA, $valueB, $object));
}

define('Basko\Functional\flip_values', __NAMESPACE__ . '\\flip_values');

/**
 * Function that helps you determine every Nth iteration of a loop.
 *
 * ```
 * $is10thIteration = is_nth(10);
 *
 * for ($i = 1; $i <= 20; $i++) {
 *      if ($is10thIteration($i)) {
 *          // do something on each 10th iteration (when $i is 10 and 20 in this case)
 *      }
 * }
 * ```
 *
 * @param int $n
 * @param int $i
 * @return bool|callable
 */
function is_nth($n, $i = null)
{
    InvalidArgumentException::assertPositiveInteger($n, __FUNCTION__, 1);

    if (\func_num_args() < 2) {
        return partial(is_nth, $n);
    }

    InvalidArgumentException::assertInteger($i, __FUNCTION__, 2);

    return modulo($i, $n) === 0;
}

define('Basko\Functional\is_nth', __NAMESPACE__ . '\\is_nth');

/**
 * Publishes any private method.
 *
 * ```
 * class Collection
 * {
 *      public function filterNumbers(array $collection) {
 *          return select([$this, 'isInt'], $collection); // This will throw an exception
 *      }
 *
 *      private function isInt($n) {
 *          return is_int($n);
 *      }
 * }
 *
 * // The above will generate an error because `isInt` is a private method.
 *
 * // This will work.
 *
 * public function filterNumbers(array $collection)
 * {
 *      return select(publish('isInt', $this), $collection);
 * }
 * ```
 *
 * @param string $method
 * @param object $context Used for both "newscope" and "newthis"
 * @return callable
 * @no-named-arguments
 */
function publish($method, $context = null)
{
    InvalidArgumentException::assertString($method, __FUNCTION__, 1);

    if (\func_num_args() < 2) {
        return partial(publish, $method);
    }

    InvalidArgumentException::assertObject($context, __FUNCTION__, 2);

    $caller = function () use ($method) {
        $args = \func_get_args();

        return \call_user_func_array([$this, $method], $args);
    };

    return $caller->bindTo($context, $context);
}

define('Basko\Functional\publish', __NAMESPACE__ . '\\publish');
