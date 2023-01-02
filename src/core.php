<?php

namespace Basko\Functional;

use Basko\Functional\Exception\InvalidArgumentException;
use Basko\Functional\Functor\Either;
use Basko\Functional\Functor\Maybe;
use Basko\Functional\Functor\Monad;

/**
 * @param mixed $value
 * @return mixed
 * @no-named-arguments
 */
function identity($value)
{
    return $value;
}

define('Basko\Functional\identity', __NAMESPACE__ . '\\identity');

/**
 * @return true
 */
function T()
{
    return true;
}

define('Basko\Functional\T', __NAMESPACE__ . '\\T');

/**
 * @return false
 */
function F()
{
    return false;
}

define('Basko\Functional\F', __NAMESPACE__ . '\\F');

/**
 * @return null
 */
function N()
{
    return null;
}

define('Basko\Functional\N', __NAMESPACE__ . '\\N');

/**
 * @param mixed $a
 * @param mixed $b
 * @return bool|callable
 * @no-named-arguments
 */
function eq($a, $b = null)
{
    if (is_null($b)) {
        return partial(eq, $a);
    }

    return $a == $b;
}

define('Basko\Functional\eq', __NAMESPACE__ . '\\eq');

/**
 * @param mixed $a
 * @param mixed $b
 * @return bool|callable
 * @no-named-arguments
 */
function identical($a, $b = null)
{
    if (is_null($b)) {
        return partial(identical, $a);
    }

    return $a === $b;
}

define('Basko\Functional\identical', __NAMESPACE__ . '\\identical');

/**
 * @param mixed $a
 * @param mixed $b
 * @return bool|callable
 * @no-named-arguments
 */
function lt($a, $b = null)
{
    if (is_null($b)) {
        return partial(flipped(lt), $a);
    }

    return $a < $b;
}

define('Basko\Functional\lt', __NAMESPACE__ . '\\lt');

/**
 * @param mixed $a
 * @param mixed $b
 * @return bool|callable
 * @no-named-arguments
 */
function lte($a, $b = null)
{
    if (is_null($b)) {
        return partial(flipped(lte), $a);
    }

    return $a <= $b;
}

define('Basko\Functional\lte', __NAMESPACE__ . '\\lte');

/**
 * @param mixed $a
 * @param mixed $b
 * @return bool|callable
 * @no-named-arguments
 */
function gt($a, $b = null)
{
    if (is_null($b)) {
        return partial(flipped(gt), $a);
    }

    return $a > $b;
}

define('Basko\Functional\gt', __NAMESPACE__ . '\\gt');

/**
 * @param mixed $a
 * @param mixed $b
 * @return bool|callable
 * @no-named-arguments
 */
function gte($a, $b = null)
{
    if (is_null($b)) {
        return partial(flipped(gte), $a);
    }

    return $a >= $b;
}

define('Basko\Functional\gte', __NAMESPACE__ . '\\gte');

/**
 * Decorates given function with tail recursion optimization.
 *
 * @param callable $f
 * @return callable
 * @no-named-arguments
 */
function tail_recursion(callable $f)
{
    $underCall = false;
    $queue = [];

    return function () use (&$f, &$underCall, &$queue) {
        $result = null;
        $queue[] = func_get_args();
        if (!$underCall) {
            $underCall = true;
            while ($head = array_shift($queue)) {
                $result = call_user_func_array($f, $head);
            }
            $underCall = false;
        }

        return $result;
    };
}

define('Basko\Functional\tail_recursion', __NAMESPACE__ . '\\tail_recursion');

/**
 * Produces a new list of elements by mapping each element in list through a transformation function.
 * Function arguments will be element, index, list
 *
 * @param callable $f
 * @param \Traversable|array|null $list
 * @return callable|array
 * @no-named-arguments
 */
function map(callable $f, $list = null)
{
    if (is_null($list)) {
        return partial(map, $f);
    }
    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    $aggregation = [];

    foreach ($list as $index => $element) {
        $aggregation[$index] = call_user_func_array($f, [$element, $index, $list]);
    }

    return $aggregation;
}

define('Basko\Functional\map', __NAMESPACE__ . '\\map');

/**
 * `flat_map` works applying `$f` that returns a sequence for each element in a list,
 * and flattening the results into the resulting array.
 *
 * flat_map(...) differs from flatten(map(...)) because it only flattens one level of nesting,
 * whereas flatten will recursively flatten nested collections.
 *
 * @param callable $f
 * @param $list
 * @return array|callable
 * @no-named-arguments
 */
function flat_map(callable $f, $list = null)
{
    if (is_null($list)) {
        return partial(flat_map, $f);
    }
    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    $flattened = [];

    foreach ($list as $index => $element) {
        $result = call_user_func_array($f, [$element, $index, $list]);

        if (\is_array($result) || $result instanceof \Traversable) {
            foreach ($result as $item) {
                $flattened[] = $item;
            }
        } elseif ($result !== null) {
            $flattened[] = $result;
        }
    }

    return $flattened;
}

define('Basko\Functional\flat_map', __NAMESPACE__ . '\\flat_map');

/**
 * Calls `$f` on each element in list. Returns origin `$list`.
 * Function arguments will be element, index, list
 *
 * @param callable $f
 * @param \Traversable|array|null $list
 * @return callable|array
 * @no-named-arguments
 */
function each(callable $f, $list = null)
{
    if (is_null($list)) {
        return partial(each, $f);
    }
    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    foreach ($list as $index => $element) {
        call_user_func_array($f, [$element, $index, $list]);
    }

    return $list;
}

define('Basko\Functional\each', __NAMESPACE__ . '\\each');

/**
 * Logical negation of the given $function
 *
 * @param callable $f The function to run value against
 * @return callable A negation version on the given $function
 * @no-named-arguments
 */
function not(callable $f)
{
    return function ($value) use ($f) {
        return !call_user_func_array($f, [$value]);
    };
}

define('Basko\Functional\not', __NAMESPACE__ . '\\not');

/**
 * Call the given function with the given value, then return the value.
 *
 * @param callable $f
 * @param mixed $value
 * @return callable|mixed
 * @no-named-arguments
 */
function tap(callable $f, $value = null)
{
    if (is_null($value)) {
        return partial(tap, $f);
    }

    call_user_func_array($f, [copy($value)]);

    return $value;
}

define('Basko\Functional\tap', __NAMESPACE__ . '\\tap');

/**
 * Applies a function to each element in the list and reduces it to a single value.
 *
 * @param callable $f
 * @param mixed $accumulator
 * @param iterable|null $list
 * @return callable|scalar
 * @no-named-arguments
 */
function fold(callable $f, $accumulator = null, $list = null)
{
    if (is_null($accumulator) && is_null($list)) {
        return partial(fold, $f);
    } elseif (is_null($list)) {
        return partial(fold, $f, $accumulator);
    }
    InvalidArgumentException::assertList($list, __FUNCTION__, 3);

    foreach ($list as $index => $value) {
        $accumulator = call_user_func_array($f, [$accumulator, $value, $index, $list]);
    }

    return $accumulator;
}

define('Basko\Functional\fold', __NAMESPACE__ . '\\fold');

/**
 * @param callable $f
 * @param mixed $accumulator
 * @param iterable|null $list
 * @return callable|scalar
 * @no-named-arguments
 */
function fold_r(callable $f, $accumulator = null, $list = null)
{
    if (is_null($accumulator) && is_null($list)) {
        return partial(fold, $f);
    } elseif (is_null($list)) {
        return partial(fold, $f, $accumulator);
    }
    InvalidArgumentException::assertList($list, __FUNCTION__, 3);

    $data = [];
    foreach ($list as $index => $value) {
        $data[] = [$index, $value];
    }

    for ($i = count($data) - 1; $i >= 0; $i--) {
        list($index, $value) = $data[$i];
        $accumulator = call_user_func_array($f, [$value, $accumulator, $index, $list]);
    }

    return $accumulator;
}

define('Basko\Functional\fold_r', __NAMESPACE__ . '\\fold_r');

/**
 * Wrap value within a function, which will return it, without any modifications. Kinda constant function.
 *
 * @param mixed $value
 * @return callable
 * @no-named-arguments
 */
function always($value)
{
    return function () use ($value) {
        return $value;
    };
}

define('Basko\Functional\always', __NAMESPACE__ . '\\always');

/**
 * Returns new function which applies each given function to the result of another from right to left.
 * compose(f, g, h) is the same as f(g(h(x)))
 *
 * @param callable $f
 * @param callable $g
 * @param mixed ...
 * @return callable
 * @no-named-arguments
 */
function compose(callable $f, callable $g)
{
    $functions = func_get_args();

    /**
     * @return mixed|Maybe|Either
     */
    return function () use ($functions) {
        $args = func_get_args();
        foreach (array_reverse($functions) as $function) {
            $args = [call_user_func_array($function, $args)];
        }

        return current($args);
    };
}

define('Basko\Functional\compose', __NAMESPACE__ . '\\compose');

/**
 * Performs left to right function composition.
 * pipe(f, g, h) is the same as h(g(f(x)))
 *
 * @param callable $f
 * @param callable $g
 * @param callable ...
 * @return callable
 * @no-named-arguments
 */
function pipe(callable $f, callable $g)
{
    $f = call_user_func_array(compose, array_reverse(func_get_args()));

    /**
     * @return mixed|Maybe|Either
     */
    return function () use ($f) {
        $args = func_get_args();

        return call_user_func_array($f, $args);
    };
}

define('Basko\Functional\pipe', __NAMESPACE__ . '\\pipe');

/**
 * Accepts a converging function and a list of branching functions and returns a new function.
 *
 * The results of each branching function are passed as arguments
 * to the converging function to produce the return value.
 *
 * @param callable $convergingFunction Will be invoked with the return values of all branching functions
 *                                     as its arguments
 * @param callable[] $branchingFunctions A list of functions
 * @return callable A flipped version of the given function
 * @no-named-arguments
 */
function converge(callable $convergingFunction, $branchingFunctions = null)
{
    if (!is_array($branchingFunctions)) {
        return partial(converge, $convergingFunction);
    }

    InvalidArgumentException::assertListOfCallables($branchingFunctions, __FUNCTION__, 2);

    return function () use ($convergingFunction, $branchingFunctions) {
        $values = func_get_args();

        $result = [];

        foreach ($branchingFunctions as $branchingFunction) {
            $result[] = call_user_func_array($branchingFunction, $values);
        }

        return call_user_func_array($convergingFunction, $result);
    };
}

define('Basko\Functional\converge', __NAMESPACE__ . '\\converge');

/**
 * @param callable $f
 * @param mixed $args
 * @return mixed
 * @no-named-arguments
 */
function call(callable $f, $args = null)
{
    if (is_null($args)) {
        return partial(call, $f);
    }

    $args = func_get_args();
    return call_user_func_array(head($args), flatten(tail($args)));
}

define('Basko\Functional\call', __NAMESPACE__ . '\\call');

/**
 * @param $arg
 * @param callable|null $f
 * @return callable
 * @no-named-arguments
 */
function apply_to($arg, callable $f = null)
{
    if (is_null($f)) {
        return partial(apply_to, $arg);
    }

    $args = func_get_args();
    return call_user_func_array(array_pop($args), $args);
}

define('Basko\Functional\apply_to', __NAMESPACE__ . '\\apply_to');

/**
 * Performs an operation checking for the given conditions
 *
 * @param callable[][] $conditions the conditions to check against
 *
 * @return callable|null the function that calls the callable of the first truthy condition
 * @no-named-arguments
 */
function cond(array $conditions)
{
    return static function ($value) use ($conditions) {
        if (empty($conditions)) {
            return null;
        }

        list($if, $then) = head($conditions);

        $cond = if_else($if, $then, cond(tail($conditions)));

        return $cond($value);
    };
}

define('Basko\Functional\cond', __NAMESPACE__ . '\\cond');

/**
 * Returns function which accepts arguments in the reversed order.
 *
 * Note, that you cannot use curry on a flipped function. curry uses reflection to get the number of function arguments,
 * but this is not possible on the function returned from flip. Instead, use curry_n on flipped functions.
 *
 * @param callable $f
 * @return callable
 * @no-named-arguments
 */
function flipped(callable $f)
{
    return function () use ($f) {
        return call_user_func_array($f, array_reverse(func_get_args()));
    };
}

define('Basko\Functional\flipped', __NAMESPACE__ . '\\flipped');

/**
 * Takes a binary function f, and unary function g, and two values. Applies g to each value,
 * then applies the result of each to f.
 * Also known as the P combinator.
 *
 * @param callable $f
 * @param callable $g
 * @return callable
 * @no-named-arguments
 */
function on(callable $f, callable $g = null)
{
    if (is_null($g)) {
        return partial(on, $f);
    }

    return function ($a, $b) use ($f, $g) {
        return call_user_func_array($f, [call_user_func_array($g, [$a]), call_user_func_array($g, [$b])]);
    };
}

define('Basko\Functional\on', __NAMESPACE__ . '\\on');

/**
 * Acts as the boolean and statement.
 *
 * @param mixed $a
 * @param mixed $b
 * @return callable|bool
 * @no-named-arguments
 */
function both($a, $b = null)
{
    if (is_null($b)) {
        return partial(both, $a);
    }

    if (is_callable($a) && is_callable($b)) {
        return function ($value) use ($a, $b) {
            return call_user_func_array($a, [$value]) && call_user_func_array($b, [$value]);
        };
    }

    return $a && $b;
}

define('Basko\Functional\both', __NAMESPACE__ . '\\both');

/**
 * @param callable[] $flist
 * @param $list
 * @return array|callable
 * @no-named-arguments
 */
function ap($flist, $list = null)
{
    InvalidArgumentException::assertListOfCallables($flist, __FUNCTION__, 1);

    if (is_null($list)) {
        return partial(ap, $flist);
    }

    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    $aggregation = [];

    foreach ($flist as $f) {
        $aggregation = array_merge($aggregation, map($f, $list));
    }

    return $aggregation;
}

define('Basko\Functional\ap', __NAMESPACE__ . '\\ap');

/**
 * Lift a function so that it accepts Monad as parameters. Lifted function returns Monad.
 *
 * Note, that you cannot use curry on a lifted function.
 *
 * @param callable $f
 * @return callable
 * @no-named-arguments
 */
function liftm(callable $f)
{
    return function () use ($f) {
        $cond = if_else(is_instance_of(Monad::class), identity, Maybe::of);

        return $cond(call_user_func_array($f, map(function ($m) {
            if (instance_of(Monad::class, $m)) {
                return $m->extract();
            }

            return $m;
        }, func_get_args())));
    };
}

define('Basko\Functional\liftm', __NAMESPACE__ . '\\liftm');
