<?php

namespace Basko\Functional;

use ArrayIterator;
use Basko\Functional\Exception\InvalidArgumentException;
use Basko\Functional\Sequences\ExponentialSequence;
use Basko\Functional\Sequences\LinearSequence;
use Exception;
use InfiniteIterator;
use Iterator;
use Traversable;

/**
 * Internal function.
 *
 * @param object $value
 * @return string
 * @internal
 */
function _object_to_ref($value)
{
    /** @var object[]|\WeakReference[] $objectReferences */
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
 * @return string
 * @internal
 */
function _value_to_ref($value, $key = null)
{
    $type = \gettype($value);
    if ($type === 'array') {
        $ref = '[' . \implode(':', map(_value_to_ref, $value)) . ']';
    } elseif ($value instanceof Traversable) {
        $ref = _object_to_ref($value) . '[' . \implode(':', map(_value_to_ref, $value)) . ']';
    } elseif ($type === 'object') {
        $ref = _object_to_ref($value);
    } elseif ($type === 'resource') {
        throw new InvalidArgumentException(
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
 * Internal function.
 *
 * @param mixed $value
 * @return string
 */
function _value_to_key($value)
{
    return _value_to_ref($value);
}

/**
 * Returns arguments as a list.
 *
 * ```php
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
        return \array_unique(\array_filter(\array_map('trim', \explode(',', $args)), 'strlen'));
    }

    return \func_get_args();
}

define('Basko\Functional\to_list', __NAMESPACE__ . '\\to_list');

/**
 * Concatenates `$a` with `$b`.
 *
 * ```php
 * concat('foo', 'bar'); // 'foobar'
 * ```
 *
 * @param string $a
 * @param string $b
 * @return ($b is null ? callable(string):string : string)
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
 * ```php
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
 * ```php
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
        $pfn = __FUNCTION__;
        return function ($list) use ($separator, $pfn) {
            InvalidArgumentException::assertList($list, $pfn, 2);

            if ($list instanceof Traversable) {
                $list = \iterator_to_array($list);
            }

            return \implode($separator, $list);
        };
    }
    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    if ($list instanceof Traversable) {
        $list = \iterator_to_array($list);
    }

    return \implode($separator, $list);
}

define('Basko\Functional\join', __NAMESPACE__ . '\\join');

/**
 * Performs an `if/else` condition over a value using functions as statements.
 *
 * ```php
 * $ifFoo = if_else(eq('foo'), always('bar'), always('baz'));
 * $ifFoo('foo'); // 'bar'
 * $ifFoo('qux'); // 'baz'
 * ```
 *
 * @param callable $if the condition function
 * @param callable $then function to call if condition is true
 * @param callable $else function to call if condition is false
 * @return callable(mixed):mixed The return value of the given $then or $else functions
 * @no-named-arguments
 */
function if_else(callable $if, callable $then = null, callable $else = null)
{
    if (\func_num_args() === 1) {
        return partial(if_else, [$if]);
    } elseif (\func_num_args() === 2) {
        return partial(if_else, [$if, $then]);
    }

    InvalidArgumentException::assertCallable($then, __FUNCTION__, 2);
    InvalidArgumentException::assertCallable($else, __FUNCTION__, 3);

    return function () use ($if, $then, $else) {
        $args = \func_get_args();

        return \call_user_func_array($if, $args)
            ? \call_user_func_array($then, $args)
            : \call_user_func_array($else, $args);
    };
}

define('Basko\Functional\if_else', __NAMESPACE__ . '\\if_else');

/**
 * Creates a function that can be used to repeat the execution of `$f`.
 *
 * ```php
 * repeat(thunkify('print_r')('Hello'))(3); // Print 'Hello' 3 times
 * ```
 *
 * @param callable $f
 * @return callable(int):void
 * @no-named-arguments
 */
function repeat(callable $f)
{
    $pfn = __FUNCTION__;

    return function ($times) use ($f, $pfn) {
        InvalidArgumentException::assertInteger($times, concat('Callable created by ', $pfn), 1);

        for ($i = 0; $i < $times; ++$i) {
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
 * ```php
 * try_catch(function () {
 *      throw new \Exception();
 * }, always('val'))(); // 'val'
 * ```
 *
 * @param callable $tryer
 * @param callable $catcher
 * @return ($catcher is null ? callable(callable):callable : callable():mixed)
 * @no-named-arguments
 */
function try_catch(callable $tryer, callable $catcher = null)
{
    if (\func_num_args() < 2) {
        return partial(try_catch, [$tryer]);
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
            } catch (Exception $exception) {
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
 * ```php
 * array_filter([$user1, $user2], invoker('isActive')); // only active users
 * ```
 *
 * @param string $methodName
 * @param array $arguments
 * @return callable(object):mixed
 * @no-named-arguments
 */
function invoker($methodName, array $arguments = [])
{
    InvalidArgumentException::assertMethodName($methodName, __FUNCTION__, 1);

    $pfn = 'Function created by' . __FUNCTION__;

    return static function ($object) use ($methodName, $arguments, $pfn) {
        InvalidArgumentException::assertObject($object, $pfn, 1);

        return \call_user_func_array([$object, $methodName], $arguments);
    };
}

define('Basko\Functional\invoker', __NAMESPACE__ . '\\invoker');

/**
 * Count length of string or number of elements in the array.
 *
 * ```php
 * len('foo'); // 3
 * len(['a', 'b']); // 2
 * ```
 *
 * @param string|\Traversable|array $a
 * @return int
 * @no-named-arguments
 */
function len($a)
{
    InvalidArgumentException::assertStringOrList($a, __FUNCTION__, 1);

    if (\is_string($a)) {
        return \strlen($a);
    }

    if ($a instanceof Traversable) {
        $a = \iterator_to_array($a);
    }

    return \count($a);
}

define('Basko\Functional\len', __NAMESPACE__ . '\\len');

/**
 * Returns a function that when supplied an object returns the indicated property of that object, if it exists.
 *
 * ```php
 * prop(0, [99]); // 99
 * prop('x', ['x' => 100]); // 100
 * $object = new \stdClass();
 * $object->x = 101;
 * prop('x', $object); // 101
 * ```
 *
 * @param string|int $property
 * @param array|object $object
 * @return ($object is null ? callable : mixed)
 * @no-named-arguments
 */
function prop($property, $object = null)
{
    InvalidArgumentException::assertString($property, __FUNCTION__, 1);

    if (\func_num_args() < 2) {
        return function ($object) use ($property) {
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
        };
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
 * ```php
 * prop_thunk(0, [99])(); // 99
 * ```
 *
 * @param string $property
 * @param \Traversable|array|null $object
 * @return callable
 */
function prop_thunk($property, $object = null)
{
    InvalidArgumentException::assertString($property, __FUNCTION__, 1);

    $propThunk = _thunkify_n(prop, 2);

    return $propThunk($property, $object);
}

define('Basko\Functional\prop_thunk', __NAMESPACE__ . '\\prop_thunk');

/**
 * Nested version of `prop` function.
 *
 * ```php
 * prop_path(['b', 'c'], [
 *      'a' => 1,
 *      'b' => [
 *          'c' => 2
 *      ],
 * ]); // 2
 * ```
 *
 * @param array $path
 * @param \Traversable|array|null $object
 * @return mixed
 * @no-named-arguments
 */
function prop_path(array $path, $object = null)
{
    if (\func_num_args() < 2) {
        return function ($object) use ($path) {
            return fold(function ($object, $property) {
                return prop($property, $object);
            }, $object, $path);
        };
    }

    return fold(function ($object, $property) {
        return prop($property, $object);
    }, $object, $path);
}

define('Basko\Functional\prop_path', __NAMESPACE__ . '\\prop_path');

/**
 * Acts as multiple `prop`: array of keys in, array of values out. Preserves order.
 *
 * ```php
 * props(['c', 'a', 'b'], ['b' => 2, 'a' => 1]); // [null, 1, 2]
 * ```
 *
 * @param array $properties
 * @param array|object $object
 * @return callable(mixed):mixed|array
 * @no-named-arguments
 */
function props(array $properties, $object = null)
{
    if (\func_num_args() < 2) {
        return function ($object) use ($properties) {
            return fold(function ($accumulator, $property) use ($object) {
                $accumulator[] = prop($property, $object);

                return $accumulator;
            }, [], $properties);
        };
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
 * ```php
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
 * @param string $key
 * @param mixed|callable $val
 * @param \Traversable|array|object|null $list
 * @return mixed
 * @no-named-arguments
 */
function assoc($key, $val = null, $list = null)
{
    InvalidArgumentException::assertString($key, __FUNCTION__, 1);

    if (\func_num_args() === 1) {
        return partial(assoc, [$key]);
    } elseif (\func_num_args() === 2) {
        return partial(assoc, [$key, $val]);
    }

    InvalidArgumentException::assertList($list, __FUNCTION__, 3);

    $possibleCopy = if_else(unary('is_object'), cp, identity);

    return fold(function ($accumulator, $entry, $index) use ($key, $val) {
        if (\is_object($accumulator)) {
            if ($key == $index) {
                $accumulator->{$index} = $entry;
            }

            $accumulator->{$key} = \is_callable($val) ? $val($accumulator) : $val;
        } elseif (\is_array($accumulator)) {
            if ($key == $index) {
                $accumulator[$index] = $entry;
            }

            $accumulator[$key] = \is_callable($val) ? $val($accumulator) : $val;
        }

        return $accumulator;
    }, $possibleCopy($list), $list);
}

define('Basko\Functional\assoc', __NAMESPACE__ . '\\assoc');

/**
 * Nested version of `assoc` function.
 *
 * ```php
 * assoc_path(['bar', 'baz'], 42, ['foo' => 'foo', 'bar' => ['baz' => 41]]); // ['foo' => 'foo', 'bar' => ['baz' => 42]]
 * ```
 *
 * @param array $path
 * @param mixed|callable $val
 * @param \Traversable|array|object|null $list
 * @return mixed
 * @no-named-arguments
 */
function assoc_path(array $path, $val = null, $list = null)
{
    if (\func_num_args() === 1) {
        return partial(assoc_path, [$path]);
    } elseif (\func_num_args() === 2) {
        return partial(assoc_path, [$path, $val]);
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
 * ```php
 * to_fn($obj, 'someMethod', ['arg'])(); // Equal to $obj->someMethod('arg');
 * ```
 *
 * @param object $object
 * @param string $methodName
 * @param array $arguments
 * @return callable():mixed
 * @no-named-arguments
 */
function to_fn($object, $methodName = null, array $arguments = null)
{
    $args = \func_get_args();
    \array_shift($args);
    \array_shift($args);
    $arguments = flatten($args);

    return function () use ($object, $methodName, $arguments) {
        return \call_user_func_array([$object, $methodName], $arguments);
    };
}

define('Basko\Functional\to_fn', __NAMESPACE__ . '\\to_fn');

/**
 * Takes two arguments, `$fst` and `$snd`, and returns `[$fst, $snd]`.
 *
 * ```php
 * pair('foo', 'bar'); // ['foo', 'bar']
 * ```
 *
 * @param mixed $fst
 * @param mixed $snd
 * @return callable|array
 * @no-named-arguments
 */
function pair($fst, $snd = null)
{
    if (\func_num_args() < 2) {
        return partial(pair, [$fst]);
    }

    return [$fst, $snd];
}

define('Basko\Functional\pair', __NAMESPACE__ . '\\pair');

/**
 * @return \Closure|mixed|null
 */
function _either()
{
    $arguments = \func_get_args();
    $strict = (bool)\array_shift($arguments);
    $allFunctions = $functions = $arguments;
    $arg = \array_pop($functions);
    if (\is_callable($arg)) {
        InvalidArgumentException::assertListOfCallables(
            $allFunctions,
            __FUNCTION__,
            InvalidArgumentException::ALL
        );

        return function () use ($allFunctions, $strict) {
            $args = \func_get_args();
            foreach ($allFunctions as $function) {
                $res = \call_user_func_array($function, $args);
                if ($strict && !\is_null($res)) {
                    return $res;
                } elseif ($res) {
                    return $res;
                }
            }

            return null;
        };
    }

    InvalidArgumentException::assertListOfCallables(
        $functions,
        __FUNCTION__,
        InvalidArgumentException::ALL
    );

    foreach ($functions as $function) {
        $res = \call_user_func_array($function, [$arg]);
        if ($strict && !\is_null($res)) {
            return $res;
        } elseif ($res) {
            return $res;
        }
    }

    return null;
}

/**
 * A function wrapping calls to the functions in an `||` operation, returning the result of the first function
 * if it is truth-y and the result of the next function otherwise.
 *
 * ```php
 * either(gt(10), is_even, 101); // true
 * $value = either(prop('prop1'), prop('prop2'), prop('prop3'));
 * $value([
 *      'prop2' => 'some value'
 * ]); // 'some value'
 * ```
 *
 * @return callable|mixed
 * @no-named-arguments
 */
function either()
{
    return \call_user_func_array('Basko\Functional\_either', \array_merge([false], \func_get_args()));
}

define('Basko\Functional\either', __NAMESPACE__ . '\\either');

/**
 * The same as `either`, but returning the result of the first function
 * if it is not NULL and the result of the next function otherwise.
 *
 * @return callable|mixed
 * @no-named-arguments
 */
function either_strict()
{
    return \call_user_func_array('Basko\Functional\_either', \array_merge([true], \func_get_args()));
}

define('Basko\Functional\either_strict', __NAMESPACE__ . '\\either_strict');

/**
 * Quote given string.
 *
 * ```php
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
 * Select the specified keys from the array.
 *
 * ```php
 * select_keys(['bar', 'baz'], ['foo' => 1, 'bar' => 2, 'baz' => 3]); // ['bar' => 2, 'baz' => 3]
 * ```
 *
 * @param array $keys
 * @param Traversable|array|object $object
 * @return callable|array
 * @no-named-arguments
 */
function select_keys(array $keys, $object = null)
{
    if (\func_num_args() < 2) {
        return partial(select_keys, [$keys]);
    }
    InvalidArgumentException::assertList($object, __FUNCTION__, 2);

    if ($object instanceof Traversable) {
        $object = \iterator_to_array($object);
    }

    $aggregation = [];
    foreach ($keys as $key) {
        if (\is_array($object) && \array_key_exists($key, $object)) {
            $aggregation[$key] = $object[$key];
        } elseif (\is_object($object) && \property_exists($object, $key)) {
            $aggregation[$key] = $object->{$key};
        }
    }

    return $aggregation;
}

define('Basko\Functional\select_keys', __NAMESPACE__ . '\\select_keys');

/**
 * Returns an array with the specified keys omitted from the array.
 *
 * ```php
 * omit_keys(['baz'], ['foo' => 1, 'bar' => 2, 'baz' => 3]); // ['foo' => 1, 'bar' => 2]
 * ```
 *
 * @param array $keys
 * @param Traversable|array|object $object
 * @return callable|array
 * @no-named-arguments
 */
function omit_keys(array $keys, $object = null)
{
    if (\func_num_args() < 2) {
        return partial(omit_keys, [$keys]);
    }
    InvalidArgumentException::assertList($object, __FUNCTION__, 2);

    if ($object instanceof Traversable) {
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
 * ```php
 * map_keys('strtoupper', ['foo'], ['foo' => 'val1', 'bar' => 'val2']); // ['foo' => 'VAL1', 'bar' => 'val2']
 * ```
 *
 * @param callable $f
 * @param array $keys
 * @param \Traversable|array|null $list
 * @return callable|\Traversable|array
 * @no-named-arguments
 */
function map_keys(callable $f, array $keys = null, $list = null)
{
    if (\func_num_args() === 1) {
        return partial(map_keys, [$f]);
    } elseif (\func_num_args() === 2) {
        return partial(map_keys, [$f, $keys]);
    }

    InvalidArgumentException::assertList($keys, __FUNCTION__, 2);
    InvalidArgumentException::assertList($list, __FUNCTION__, 3);

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
 * @param callable $f
 * @param array $elementsNumbers
 * @param array|\ArrayAccess $list
 * @return callable|array|\ArrayAccess
 * @no-named-arguments
 */
function map_elements(callable $f, array $elementsNumbers = null, $list = null)
{
    if (\func_num_args() === 1) {
        return partial(map_elements, [$f]);
    } elseif (\func_num_args() === 2) {
        return partial(map_elements, [$f, $elementsNumbers]);
    }

    InvalidArgumentException::assertList($elementsNumbers, __FUNCTION__, 2);
    InvalidArgumentException::assertArrayAccess($list, __FUNCTION__, 3);

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
 * ```php
 * find_missing_keys(
 *      ['login', 'email'],
 *      ['login' => 'admin']
 * ); // ['email']
 * ```
 *
 * @param array $keys
 * @param \Traversable|array|null $array
 * @return callable|int[]|string[]
 * @no-named-arguments
 */
function find_missing_keys(array $keys, $array = null)
{
    if (\func_num_args() < 2) {
        return partial(find_missing_keys, [$keys]);
    }

    InvalidArgumentException::assertList($array, __FUNCTION__, 2);

    $array = $array instanceof Traversable ? \iterator_to_array($array) : $array;

    return \array_keys(\array_diff_key(\array_flip($keys), $array));
}

define('Basko\Functional\find_missing_keys', __NAMESPACE__ . '\\find_missing_keys');

/**
 * Creates copy of provided value. `clone` will be called for objects.
 * You can overwrite `clone` and provide your specific function, just define `CLONE_FUNCTION` constant.
 *
 * ```php
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
 * ```php
 * pick_random_value(['sword', 'gold', 'ring', 'jewel']); // 'gold'
 * ```
 *
 * @param \Traversable|array $list
 * @return mixed
 * @no-named-arguments
 */
function pick_random_value($list)
{
    InvalidArgumentException::assertList($list, __FUNCTION__, 1);

    if ($list instanceof Traversable) {
        $list = \iterator_to_array($list);
    }

    if (\class_exists('Random\Randomizer')) {
        $randomizer = new \Random\Randomizer();
        $randomKeys = $randomizer->pickArrayKeys($list, 1);

        return $list[$randomKeys[0]];
    }

    $fN = \function_exists('mt_rand') ? 'mt_rand' : 'rand';

    return nth(\call_user_func_array($fN, [1, \count($list)]), $list);
}

define('Basko\Functional\pick_random_value', __NAMESPACE__ . '\\pick_random_value');

/**
 * Creates an associative array using a `$keyProp` as the path to build its keys,
 * and `$valueProp` as path to get the values.
 *
 * ```php
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
 * @param \Traversable|array|null $list
 * @return array|callable
 * @no-named-arguments
 */
function combine($keyProp, $valueProp = null, $list = null)
{
    InvalidArgumentException::assertString($keyProp, __FUNCTION__, 1);

    if (\func_num_args() === 1) {
        return partial(combine, [$keyProp]);
    } elseif (\func_num_args() === 2) {
        return partial(combine, [$keyProp, $valueProp]);
    }

    InvalidArgumentException::assertString($valueProp, __FUNCTION__, 2);
    InvalidArgumentException::assertList($list, __FUNCTION__, 3);

    $combineFunction = converge('array_combine', [
        pluck($keyProp),
        pluck($valueProp),
    ]);

    return $combineFunction($list);
}

define('Basko\Functional\combine', __NAMESPACE__ . '\\combine');

/**
 * Returns an infinite, traversable sequence of constant values.
 *
 * @param int $value
 * @return \Traversable
 * @no-named-arguments
 */
function sequence_constant($value)
{
    if (!\is_int($value) || $value < 0) {
        throw new \InvalidArgumentException(
            'sequence_constant() expects $value argument to be an integer, greater than or equal to 0'
        );
    }

    return new InfiniteIterator(new ArrayIterator([$value]));
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
 * ```php
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
function retry($retries, Iterator $delaySequence = null, $f = null)
{
    if (\func_num_args() === 1) {
        return partial(retry, [$retries]);
    } elseif (\func_num_args() === 2) {
        return partial(retry, [$retries, $delaySequence]);
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
        } catch (Exception $e) {
            if ($retry === $retries - 1) {
                throw $e;
            }
        }

        if ($delay > 0) {
            \usleep($delay);
        }

        ++$retry;
    }
}

define('Basko\Functional\retry', __NAMESPACE__ . '\\retry');

/**
 * Creates instance of given class.
 *
 * ```php
 * construct('stdClass'); // object(stdClass)
 * ```
 *
 * @param class-string $class
 * @return mixed
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
 * ```php
 * $user = construct_with_args(User::class, ['first_name' => 'Slava', 'last_name' => 'Basko']);
 * echo $user->first_name; // Slava
 * ```
 *
 * @param class-string $class
 * @param mixed $constructArguments
 * @return callable|mixed
 */
function construct_with_args($class, $constructArguments = null)
{
    if (\func_num_args() < 2) {
        return partial(construct_with_args, [$class]);
    }

    InvalidArgumentException::assertClass($class, __FUNCTION__, 1);

    return new $class($constructArguments);
}

define('Basko\Functional\construct_with_args', __NAMESPACE__ . '\\construct_with_args');

/**
 * Swaps the values of keys `a` and `b`.
 *
 * ```php
 * flip_values('key1', 'key2', ['key1' => 'val1', 'key2' => 'val2']); // ['key1' => 'val2', 'key2' => 'val1']
 * ```
 *
 * @template T of array|object
 * @param string $keyA
 * @param string|null $keyB
 * @param T $object
 * @return callable|T
 */
function flip_values($keyA, $keyB = null, $object = null)
{
    InvalidArgumentException::assertString($keyA, __FUNCTION__, 1);

    if (\func_num_args() === 1) {
        return partial(flip_values, [$keyA]);
    } elseif (\func_num_args() === 2) {
        return partial(flip_values, [$keyA, $keyB]);
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
 * ```php
 * $is10thIteration = is_nth(10);
 *
 * for ($i = 1; $i <= 20; $i++) {
 *      if ($is10thIteration($i)) {
 *          // do something on each 10th iteration (when $i is 10 and 20 in this case)
 *      }
 * }
 * ```
 *
 * @template T of int
 * @param T $n
 * @param T $i
 * @return ($i is null ? callable(T $i):T : bool)
 */
function is_nth($n, $i = null)
{
    InvalidArgumentException::assertPositiveInteger($n, __FUNCTION__, 1);

    if (\func_num_args() < 2) {
        return function ($i) use ($n) {
            InvalidArgumentException::assertInteger($i, __FUNCTION__, 2);

            return modulo($i, $n) === 0;
        };
    }

    InvalidArgumentException::assertInteger($i, __FUNCTION__, 2);

    return modulo($i, $n) === 0;
}

define('Basko\Functional\is_nth', __NAMESPACE__ . '\\is_nth');

/**
 * Publishes any private method.
 *
 * ```php
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
 * ```
 * The above will generate an error because `isInt` is a private method.
 *
 * This will work.
 * ```php
 * public function filterNumbers(array $collection)
 * {
 *      return select(publish('isInt', $this), $collection);
 * }
 * ```
 *
 * @param string $method
 * @param object $context Used for both "newscope" and "newthis"
 * @return ($context is null ? callable(object):callable : callable)
 */
function publish($method, $context = null)
{
    InvalidArgumentException::assertString($method, __FUNCTION__, 1);

    if (\func_num_args() < 2) {
        return partial(publish, [$method]);
    }

    InvalidArgumentException::assertObject($context, __FUNCTION__, 2);

    $caller = function () use ($method) {
        $args = \func_get_args();

        return \call_user_func_array([$this, $method], $args);
    };

    return $caller->bindTo($context, $context);
}

define('Basko\Functional\publish', __NAMESPACE__ . '\\publish');
