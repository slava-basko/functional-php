<?php

namespace Basko\Functional;

use Basko\Functional\Exception\InvalidArgumentException;
use Basko\Functional\Functor\Either;
use Basko\Functional\Functor\Identity;
use Basko\Functional\Functor\Maybe;
use Basko\Functional\Functor\Monad;
use Basko\Functional\Functor\Optional;
use Traversable;

/**
 * Function that do nothing.
 *
 * ```php
 * noop(); // nothing happen
 * noop('some string'); // nothing happen
 * ```
 *
 * @return callable():void
 */
function noop()
{
    return function () {
    };
}

define('Basko\Functional\noop', __NAMESPACE__ . '\\noop', false);

/**
 * Does nothing, return the parameter supplied to it.
 *
 * ```php
 * identity(1); // 1
 *
 * $obj = new \stdClass;
 * identity($obj) === $obj; // true
 * ```
 *
 * @template T
 * @param T $value
 * @return T
 * @no-named-arguments
 */
function identity($value)
{
    return $value;
}

define('Basko\Functional\identity', __NAMESPACE__ . '\\identity', false);

/**
 * Always return `true`.
 *
 * ```php
 * T(); // true
 * ```
 *
 * @return true
 */
function T()
{
    return true;
}

define('Basko\Functional\T', __NAMESPACE__ . '\\T', false);

/**
 * Always return `false`.
 *
 * ```php
 * F(); // false
 * ```
 *
 * @return false
 */
function F()
{
    return false;
}

define('Basko\Functional\F', __NAMESPACE__ . '\\F', false);

/**
 * Always return `null`.
 *
 * ```php
 * NULL(); // null
 * ```
 *
 * @return null
 */
function N()
{
    return null;
}

define('Basko\Functional\N', __NAMESPACE__ . '\\N', false);

/**
 * Run PHP comparison operator `==`.
 *
 * ```php
 * eq(1, 1); // true
 * eq(1, '1'); // true
 * eq(1, 2); // false
 * ```
 *
 * @param mixed $a
 * @param mixed $b
 * @return ($b is null ? callable(mixed $b):bool : bool)
 * @no-named-arguments
 */
function eq($a, $b = null)
{
    if (is_null($b)) {
        return partial(eq, $a);
    }

    return $a == $b;
}

define('Basko\Functional\eq', __NAMESPACE__ . '\\eq', false);

/**
 * Run PHP comparison operator `===`.
 *
 * ```php
 * identical(1, 1); // true
 * identical(1, '1'); // false
 * ```
 *
 * @param mixed $a
 * @param mixed $b
 * @return ($b is null ? callable(mixed $b):bool : bool)
 * @no-named-arguments
 */
function identical($a, $b = null)
{
    if (is_null($b)) {
        return partial(identical, $a);
    }

    return $a === $b;
}

define('Basko\Functional\identical', __NAMESPACE__ . '\\identical', false);

/**
 * Returns true if the first argument is less than the second; false otherwise.
 *
 * ```php
 * lt(2, 1); // false
 * lt(2, 2); // false
 * lt(2, 3); // true
 * lt('a', 'z'); // true
 * lt('z', 'a'); // false
 * ```
 *
 * @param mixed $a
 * @param mixed $b
 * @return ($b is null ? callable(mixed $b):bool : bool)
 * @no-named-arguments
 */
function lt($a, $b = null)
{
    if (is_null($b)) {
        return partial(flipped(lt), $a);
    }

    return $a < $b;
}

define('Basko\Functional\lt', __NAMESPACE__ . '\\lt', false);

/**
 * Returns true if the first argument is less than or equal to the second; false otherwise.
 *
 * ```php
 * lte(2, 1); // false
 * lte(2, 2); // true
 * lte(2, 3); // true
 * lte('a', 'z'); // true
 * lte('z', 'a'); // false
 * ```
 *
 * @param mixed $a
 * @param mixed $b
 * @return ($b is null ? callable(mixed $b):bool : bool)
 * @no-named-arguments
 */
function lte($a, $b = null)
{
    if (is_null($b)) {
        return partial(flipped(lte), $a);
    }

    return $a <= $b;
}

define('Basko\Functional\lte', __NAMESPACE__ . '\\lte', false);

/**
 * Returns true if the first argument is greater than the second; false otherwise.
 *
 * ```php
 * gt(2, 1); // true
 * gt(2, 2); // false
 * gt(2, 3); // false
 * gt('a', 'z'); // false
 * gt('z', 'a'); // true
 * ```
 *
 * @param mixed $a
 * @param mixed $b
 * @return ($b is null ? callable(mixed $b):bool : bool)
 * @no-named-arguments
 */
function gt($a, $b = null)
{
    if (is_null($b)) {
        return partial(flipped(gt), $a);
    }

    return $a > $b;
}

define('Basko\Functional\gt', __NAMESPACE__ . '\\gt', false);

/**
 * Returns true if the first argument is greater than or equal to the second; false otherwise.
 *
 * ```php
 * gte(2, 1); // true
 * gte(2, 2); // true
 * gte(2, 3); // false
 * gte('a', 'z'); // false
 * gte('z', 'a'); // true
 * ```
 *
 * @param mixed $a
 * @param mixed $b
 * @return ($b is null ? callable(mixed $b):bool : bool)
 * @no-named-arguments
 */
function gte($a, $b = null)
{
    if (is_null($b)) {
        return partial(flipped(gte), $a);
    }

    return $a >= $b;
}

define('Basko\Functional\gte', __NAMESPACE__ . '\\gte', false);

/**
 * Decorates given function with tail recursion optimization using trampoline.
 *
 * ```php
 * $fact = tail_recursion(function ($n, $acc = 1) use (&$fact) {
 *      if ($n == 0) {
 *          return $acc;
 *      }
 *
 *      return $fact($n - 1, $acc * $n);
 * });
 * $fact(10); // 3628800
 * ```
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

define('Basko\Functional\tail_recursion', __NAMESPACE__ . '\\tail_recursion', false);

/**
 * Produces a new list of elements by mapping each element in list through a transformation function.
 * Function arguments will be `element`, `index`, `list`.
 *
 * ```php
 * map(plus(1), [1, 2, 3]); // [2, 3, 4]
 * ```
 *
 * @template T of \Traversable|array|null
 * @param callable(mixed $element, mixed $index, T $list):mixed $f
 * @param T $list
 * @return ($list is null ? callable(T):array : array)
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

define('Basko\Functional\map', __NAMESPACE__ . '\\map', false);

/**
 * `flat_map` works applying `$f` that returns a sequence for each element in a list,
 * and flattening the results into the resulting array.
 *
 * `flat_map(...)` differs from `flatten(map(...))` because it only flattens one level of nesting,
 * whereas flatten will recursively flatten nested collections. Indexes will not preserve.
 *
 * ```php
 * $items = [
 *      [
 *          'id' => 1,
 *          'type' => 'train',
 *          'users' => [
 *              ['id' => 1, 'name' => 'Jimmy Page'],
 *              ['id' => 5, 'name' => 'Roy Harper'],
 *          ],
 *      ],
 *      [
 *          'id' => 421,
 *          'type' => 'hotel',
 *          'users' => [
 *              ['id' => 1, 'name' => 'Jimmy Page'],
 *              ['id' => 2, 'name' => 'Robert Plant'],
 *          ],
 *      ],
 * ];
 *
 * $result = flat_map(prop('users'), $items);
 *
 * //$result is [
 * //    ['id' => 1, 'name' => 'Jimmy Page'],
 * //    ['id' => 5, 'name' => 'Roy Harper'],
 * //    ['id' => 1, 'name' => 'Jimmy Page'],
 * //    ['id' => 2, 'name' => 'Robert Plant'],
 * //];
 * ```
 *
 * @template T of \Traversable|array|null
 * @param callable(mixed $element, mixed $index, T $list):mixed $f
 * @param T $list
 * @return ($list is null ? callable(T):array : array)
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

        if (is_array($result) || $result instanceof Traversable) {
            foreach ($result as $item) {
                $flattened[] = $item;
            }
        } elseif ($result !== null) {
            $flattened[] = $result;
        }
    }

    return $flattened;
}

define('Basko\Functional\flat_map', __NAMESPACE__ . '\\flat_map', false);

/**
 * Calls `$f` on each element in list. Returns origin `$list`.
 * Function arguments will be `element`, `index`, `list`.
 *
 * ```php
 * each(unary('print_r'), [1, 2, 3]); // Print: 123
 * ```
 *
 * @template T of \Traversable|array|null
 * @param callable(mixed $element, mixed $index, T $list):mixed $f
 * @param T $list
 * @return callable(T):T|T
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

define('Basko\Functional\each', __NAMESPACE__ . '\\each', false);

/**
 * Returns the `!` of its argument.
 *
 * ```php
 * not(true); // false
 * not(false); // true
 * not(0); // true
 * not(1); // false
 * ```
 *
 * @param mixed $a The value
 * @return bool
 * @no-named-arguments
 */
function not($a)
{
    return !$a;
}

define('Basko\Functional\not', __NAMESPACE__ . '\\not', false);

/**
 * Logical negation of the given function `$f`.
 * ```php
 * $notString = complement('is_string');
 * $notString(1); // true
 * ```
 *
 * @param callable $f The function to run value against
 * @return callable(?mixed):bool A negation version on the given $function
 * @no-named-arguments
 */
function complement(callable $f)
{
    return function ($value) use ($f) {
        return !call_user_func_array($f, func_get_args());
    };
}

define('Basko\Functional\complement', __NAMESPACE__ . '\\complement', false);

/**
 * Call the given function with the given value, then return the value.
 *
 * ```php
 * $input = new \stdClass();
 * $input->property = 'foo';
 * tap(function ($o) {
 *      $o->property = 'bar';
 * }, $input);
 * $input->property; // 'foo'
 * ```
 *
 * Also, this function useful as a debug in the `pipe`.
 *
 * ```php
 * pipe(
 *      'strrev',
 *      tap('var_dump'),
 *      concat('Basko ')
 * )('avalS'); //string(5) "Slava"
 * ```
 *
 * @template T
 * @param callable(T):void $f
 * @param T|null $value
 * @return ($value is null ? callable(T):T : T)
 * @no-named-arguments
 */
function tap(callable $f, $value = null)
{
    if (is_null($value)) {
        return partial(tap, $f);
    }

    call_user_func_array($f, [cp($value)]);

    return $value;
}

define('Basko\Functional\tap', __NAMESPACE__ . '\\tap', false);

/**
 * Applies a function to each element in the list and reduces it to a single value.
 *
 * ```php
 * fold(concat, '4', [5, 1]); // 451
 *
 * function sc($a, $b)
 * {
 *      return "($a+$b)";
 * }
 *
 * fold('sc', '0', range(1, 13)); // (((((((((((((0+1)+2)+3)+4)+5)+6)+7)+8)+9)+10)+11)+12)+13)
 * ```
 *
 * @template Ta
 * @template Tl of \Traversable|array
 * @param callable(Ta $accumulator, mixed $value, mixed $index, Tl $list):mixed $f
 * @param Ta|null $accumulator
 * @param Tl|null $list
 * @return callable|Ta
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

define('Basko\Functional\fold', __NAMESPACE__ . '\\fold', false);

/**
 * The same as `fold` but accumulator on the right.
 *
 * ```php
 * fold_r(concat, '4', [5, 1]); // 514
 *
 * function sc($a, $b)
 * {
 *      return "($a+$b)";
 * }
 *
 * fold_r('sc', '0', range(1, 13)); // (1+(2+(3+(4+(5+(6+(7+(8+(9+(10+(11+(12+(13+0)))))))))))))
 * ```
 *
 * @template Ta
 * @template Tl of \Traversable|array
 * @param callable(mixed $value, Ta $accumulator, mixed $index, Tl $list):mixed $f
 * @param Ta|null $accumulator
 * @param Tl|null $list
 * @return callable|Ta
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

define('Basko\Functional\fold_r', __NAMESPACE__ . '\\fold_r', false);

/**
 * Wrap value within a function, which will return it, without any modifications. Kinda constant function.
 *
 * ```php
 * $constA = always('a');
 * $constA(); // 'a'
 * $constA(); // 'a'
 * ```
 *
 * @template T
 * @param T $value
 * @return callable():T
 * @no-named-arguments
 */
function always($value)
{
    return function () use ($value) {
        return $value;
    };
}

define('Basko\Functional\always', __NAMESPACE__ . '\\always', false);

/**
 * Returns new function which applies each given function to the result of another from right to left.
 * `compose(f, g, h)` is the same as `f(g(h(x)))`.
 *
 * ```php
 * $powerPlus1 = compose(plus(1), power);
 * $powerPlus1(3); // 10
 * ```
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

define('Basko\Functional\compose', __NAMESPACE__ . '\\compose', false);

/**
 * Performs left to right function composition.
 * `pipe(f, g, h)` is the same as `h(g(f(x)))`.
 *
 * ```php
 * $plus1AndPower = pipe(plus(1), power);
 * $plus1AndPower(3); // 16
 * ```
 *
 * @param callable $f
 * @param callable $g
 * @param callable ...
 * @return callable(mixed):mixed
 * @no-named-arguments
 */
function pipe(callable $f, callable $g)
{
    $f = call_user_func_array(compose, array_reverse(func_get_args()));

    return function () use ($f) {
        $args = func_get_args();

        return call_user_func_array($f, $args);
    };
}

define('Basko\Functional\pipe', __NAMESPACE__ . '\\pipe', false);

/**
 * Accepts a converging function and a list of branching functions and returns a new function.
 *
 * The results of each branching function are passed as arguments
 * to the converging function to produce the return value.
 *
 * ```php
 * function div($dividend, $divisor) {
 *      return $dividend / $divisor;
 * }
 *
 * $average = converge('div', ['array_sum', 'count']);
 * $average([1, 2, 3, 4]); // 2.5
 * ```
 *
 * @param callable $convergingFunction Will be invoked with the return values of all branching functions
 *                                     as its arguments
 * @param callable[] $branchingFunctions A list of functions
 * @return callable(mixed):mixed
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

define('Basko\Functional\converge', __NAMESPACE__ . '\\converge', false);

/**
 * Calls function `$f` with provided argument(s).
 *
 * ```php
 * call('strtoupper', 'slava'); // SLAVA
 * ```
 *
 * @param callable $f
 * @param mixed $args
 * @return ($args is null ? callable(...$args):mixed : mixed)
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

define('Basko\Functional\call', __NAMESPACE__ . '\\call', false);

/**
 * Create a function that will pass arguments to a given function.
 *
 * ```php
 * $fiveAndThree = apply_to([5, 3]);
 * $fiveAndThree(sum); // 8
 * ```
 *
 * @template T
 * @param T $arg
 * @param callable(T):mixed|null $f
 * @return ($f is null ? callable(callable(T):mixed):mixed : mixed)
 * @no-named-arguments
 */
function apply_to($arg, callable $f = null)
{
    if (is_null($f)) {
        return partial(apply_to, $arg);
    }

    $args = func_get_args();

    $function = array_pop($args);
    InvalidArgumentException::assertCallable($function, __FUNCTION__, 2);

    return call_user_func_array($function, $args);
}

define('Basko\Functional\apply_to', __NAMESPACE__ . '\\apply_to', false);

/**
 * Performs an operation checking for the given conditions.
 * Returns a new function that behaves like a match operator. Encapsulates `if/elseif,elseif, ...` logic.
 *
 * ```php
 * $cond = cond([
 *      [eq(0), always('water freezes')],
 *      [gte(100), always('water boils')],
 *      [T, function ($t) {
 *          return "nothing special happens at $t °C";
 *      }],
 * ]);
 *
 * $cond(0); // 'water freezes'
 * $cond(100); // 'water boils'
 * $cond(50) // 'nothing special happens at 50 °C'
 * ```
 *
 * @param callable[][] $conditions the conditions to check against
 *
 * @return callable(mixed):mixed The function that calls the callable of the first truthy condition
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

define('Basko\Functional\cond', __NAMESPACE__ . '\\cond', false);

/**
 * Returns function which accepts arguments in the reversed order.
 *
 * Note, that you cannot use curry on a flipped function.
 * `curry` uses reflection to get the number of function arguments,
 * but this is not possible on the function returned from flip. Instead, use `curry_n` on flipped functions.
 *
 * ```php
 * $mergeStrings = function ($head, $tail) {
 *      return $head . $tail;
 * };
 * $flippedMergeStrings = flipped($mergeStrings);
 * $flippedMergeStrings('two', 'one'); // 'onetwo'
 * ```
 *
 * @param callable $f
 * @return callable(mixed):mixed
 * @no-named-arguments
 */
function flipped(callable $f)
{
    return function () use ($f) {
        return call_user_func_array($f, array_reverse(func_get_args()));
    };
}

define('Basko\Functional\flipped', __NAMESPACE__ . '\\flipped', false);

/**
 * Takes a binary function `$f`, and unary function `$g`, and two values. Applies `$g` to each value,
 * then applies the result of each to `$f`.
 * Also known as the P combinator.
 *
 * ```php
 * $containsInsensitive = on(contains, 'strtolower');
 * $containsInsensitive('o', 'FOO'); // true
 * ```
 *
 * @param callable $f
 * @param callable $g
 * @return ($g is null ? callable(mixed):mixed : callable(mixed, mixed):mixed)
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

define('Basko\Functional\on', __NAMESPACE__ . '\\on', false);

/**
 * Accepts function `$f` that isn't recursive and returns function `$g` which is recursive.
 * Also known as the Y combinator.
 *
 * ```php
 * function factorial($n) {
 *      return ($n <= 1) ? 1 : $n * factorial($n - 1);
 * }
 *
 * echo factorial(5); // 120, no problem here
 *
 * $factorial = function ($n) {
 *      return ($n <= 1) ? 1 : $n * call_user_func(__FUNCTION__, $n - 1);
 * };
 *
 * echo $factorial(5); // Exception will be thrown
 * ```
 *
 * You can't call anonymous function recursively. But you can use `y` to make it possible.
 * ```php
 * $factorial = y(function ($fact) {
 *      return function ($n) use ($fact) {
 *          return ($n <= 1) ? 1 : $n * $fact($n - 1);
 *      };
 * });
 *
 * echo $factorial(5); // 120
 * ```
 *
 * @param callable $f
 * @return mixed
 */
function y(callable $f)
{
    $g = function ($fn) use ($f) {
        return call_user_func($f, function () use ($fn) {
            return call_user_func_array(call_user_func($fn, $fn), func_get_args());
        });
    };

    return call_user_func($g, $g);
}

define('Basko\Functional\y', __NAMESPACE__ . '\\y', false);

/**
 * Acts as the boolean `and` statement.
 *
 * ```php
 * both(T(), T()); // true
 * both(F(), T()); // false
 * $between6And9 = both(gt(6), lt(9));
 * $between6And9(7); // true
 * $between6And9(10); // false
 * ```
 *
 * @param mixed $a
 * @param mixed $b
 * @return ($b is null ? callable(mixed $b):bool : bool)
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

define('Basko\Functional\both', __NAMESPACE__ . '\\both', false);

/**
 * Takes a list of predicates and returns a predicate that returns true for a given list of arguments
 * if every one of the provided predicates is satisfied by those arguments.
 *
 * ```php
 * $isQueen = pipe(prop('rank'), eq('Q'));
 * $isSpade = pipe(prop('suit'), eq('♠︎'));
 * $isQueenOfSpades = all_pass([$isQueen, $isSpade]);
 *
 * $isQueenOfSpades(['rank' => 'Q', 'suit' => '♣︎']); // false
 * $isQueenOfSpades(['rank' => 'Q', 'suit' => '♠︎']); // true
 * ```
 *
 * @template T
 * @param callable[] $functions
 * @param T|null $value
 * @return ($value is null ? callable(T $value):bool : bool)
 * @no-named-arguments
 */
function all_pass(array $functions, $value = null)
{
    InvalidArgumentException::assertListOfCallables($functions, __FUNCTION__, 1);

    if (is_null($value)) {
        return partial(all_pass, $functions);
    }

    foreach ($functions as $f) {
        if (!call_user_func($f, $value)) {
            return false;
        }
    }

    return true;
}

define('Basko\Functional\all_pass', __NAMESPACE__ . '\\all_pass', false);

/**
 * Takes a list of predicates and returns a predicate that returns true for a given list of arguments
 * if at least one of the provided predicates is satisfied by those arguments.
 *
 * ```php
 * $isClub = pipe(prop('suit'), eq('♣'));
 * $isSpade = pipe(prop('suit'), eq('♠'));;
 * $isBlackCard = any_pass([$isClub, $isSpade]);
 *
 * $isBlackCard(['rank' => '10', 'suit' => '♣']); // true
 * $isBlackCard(['rank' => 'Q', 'suit' => '♠']); // true
 * $isBlackCard(['rank' => 'Q', 'suit' => '♦']); // false
 * ```
 *
 * @template T
 * @param callable[] $functions
 * @param T|null $value
 * @return ($value is null ? callable(T $value):bool : bool)
 * @no-named-arguments
 */
function any_pass(array $functions, $value = null)
{
    InvalidArgumentException::assertListOfCallables($functions, __FUNCTION__, 1);

    if (is_null($value)) {
        return partial(any_pass, $functions);
    }

    foreach ($functions as $f) {
        if (call_user_func($f, $value)) {
            return true;
        }
    }

    return false;
}

define('Basko\Functional\any_pass', __NAMESPACE__ . '\\any_pass', false);

/**
 * Applies a list of functions to a list of values.
 *
 * ```php
 * ap([multiply(2), plus(3)], [1,2,3]); // [2, 4, 6, 4, 5, 6]
 * ```
 *
 * @template T of \Traversable|array
 * @param callable[] $flist
 * @param T|null $list
 * @return ($list is null ? callable(T $list):mixed : array)
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

define('Basko\Functional\ap', __NAMESPACE__ . '\\ap', false);

/**
 * Lift a function so that it accepts `Monad` as parameters. Lifted function returns `Monad`.
 *
 * Note, that you cannot use curry on a lifted function.
 *
 * @template T of mixed
 * @template Tm of Monad
 * @param callable(T):mixed $f
 * @return callable(Tm):Tm
 * @no-named-arguments
 */
function lift_m(callable $f)
{
    return function () use ($f) {
        $ofFunc = Identity::of;

        $extractedArgs = map(function ($possibleM) use (&$ofFunc) {
            if (instance_of(Monad::class, $possibleM)) {
                $ofFunc = call(cond([
                    [eq(Maybe::class), always(Maybe::just)],
                    [eq(Optional::class), always(Optional::just)],
                    [eq(Either::class), always(Either::right)],
                    [T, function ($type) {
                        return $type::of;
                    }],
                ]), get_class($possibleM));

                return $possibleM->extract();
            }

            return $possibleM;
        }, func_get_args());

        $toM = if_else(is_instance_of(Monad::class), identity, $ofFunc);

        return $toM(call_user_func_array($f, $extractedArgs));
    };
}

define('Basko\Functional\lift_m', __NAMESPACE__ . '\\lift_m', false);

function _zip()
{
    $arrays = func_get_args();
    $functionName = array_shift($arrays);
    $callback = array_shift($arrays);

    foreach ($arrays as $position => $arr) {
        InvalidArgumentException::assertList($arr, $functionName, $position + 1);
    }

    $resultKeys = [];
    foreach ($arrays as $arg) {
        foreach ($arg as $index => $value) {
            $resultKeys[] = $index;
        }
    }

    $resultKeys = array_unique($resultKeys);

    $result = [];

    foreach ($resultKeys as $key) {
        $zipped = [];

        foreach ($arrays as $arg) {
            $zipped[] = isset($arg[$key]) ? $arg[$key] : null;
        }

        $result[$key] = call_user_func($callback, $zipped);
    }

    return $result;
}

/**
 * Zips two or more sequences.
 *
 * Note: This function is not curried because of no fixed arity.
 *
 * ```php
 * zip([1, 2], ['a', 'b']); // [[1, 'a'], [2, 'b']]
 * ```
 *
 * @param \Traversable|array $sequence1
 * @param \Traversable|array $sequence2
 * @return array
 * @no-named-arguments
 */
function zip($sequence1, $sequence2)
{
    return call_user_func_array('Basko\Functional\_zip', array_merge([__FUNCTION__, identity], func_get_args()));
}

define('Basko\Functional\zip', __NAMESPACE__ . '\\zip', false);

/**
 * Zips two or more sequences with given function `$f`.
 *
 * Note: `$f` signature is `callable(array $arg):mixed`.
 * As a result: `zip_with(plus, [1, 2], [3, 4])` equals to `plus([$arg1, $arg2])`.
 * But `zip_with(call(plus), [1, 2], [3, 4])` equals to `plus($arg1, $arg2)`.
 *
 * ```php
 * zip_with(call(plus), [1, 2], [3, 4]); // [4, 6]
 * ```
 *
 * @param callable $f
 * @param \Traversable|array $sequence1
 * @param \Traversable|array $sequence2
 * @return array|callable(...\Traversable|array):array
 * @no-named-arguments
 */
function zip_with(callable $f, $sequence1 = null, $sequence2 = null)
{
    $args = func_get_args();
    $f = array_shift($args);

    if (empty($args)) {
        return partial(zip_with, $f);
    }

    return call_user_func_array('Basko\Functional\_zip', array_merge([__FUNCTION__, $f], $args));
}

define('Basko\Functional\zip_with', __NAMESPACE__ . '\\zip_with', false);
