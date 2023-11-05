<?php

namespace Basko\Functional;

use Basko\Functional\Exception\InvalidArgumentException;
use Basko\Functional\Functor\Either;
use Basko\Functional\Functor\Identity;
use Basko\Functional\Functor\Maybe;
use Basko\Functional\Functor\Monad;
use Basko\Functional\Functor\Optional;

/**
 * Function that do nothing.
 *
 * ```php
 * noop(); // nothing happen
 * noop('some string'); // nothing happen
 * ```
 *
 * @return void
 */
function noop()
{
}

define('Basko\Functional\noop', __NAMESPACE__ . '\\noop');

/**
 * Return the parameter supplied to it.
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

define('Basko\Functional\identity', __NAMESPACE__ . '\\identity');

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

define('Basko\Functional\T', __NAMESPACE__ . '\\T');

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

define('Basko\Functional\F', __NAMESPACE__ . '\\F');

/**
 * Always return `null`.
 *
 * ```php
 * N(); // null
 * ```
 *
 * @return null
 */
function N()
{
    return null;
}

define('Basko\Functional\N', __NAMESPACE__ . '\\N');

/**
 * Runs PHP comparison operator `==`.
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
    if (\func_num_args() < 2) {
        return function ($b) use ($a) {
            return $a == $b;
        };
    }

    return $a == $b;
}

define('Basko\Functional\eq', __NAMESPACE__ . '\\eq');

/**
 * Runs PHP comparison operator `===`.
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
    if (\func_num_args() < 2) {
        return function ($b) use ($a) {
            return $a === $b;
        };
    }

    return $a === $b;
}

define('Basko\Functional\identical', __NAMESPACE__ . '\\identical');

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
    if (\func_num_args() < 2) {
        return function ($b) use ($a) {
            return $a < $b;
        };
    }

    return $a < $b;
}

define('Basko\Functional\lt', __NAMESPACE__ . '\\lt');

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
    if (\func_num_args() < 2) {
        return function ($b) use ($a) {
            return $a <= $b;
        };
    }

    return $a <= $b;
}

define('Basko\Functional\lte', __NAMESPACE__ . '\\lte');

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
    if (\func_num_args() < 2) {
        return function ($b) use ($a) {
            return $a > $b;
        };
    }

    return $a > $b;
}

define('Basko\Functional\gt', __NAMESPACE__ . '\\gt');

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
    if (\func_num_args() < 2) {
        return function ($b) use ($a) {
            return $a >= $b;
        };
    }

    return $a >= $b;
}

define('Basko\Functional\gte', __NAMESPACE__ . '\\gte');

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
        $queue[] = \func_get_args();
        if (!$underCall) {
            $underCall = true;
            while ($head = \array_shift($queue)) {
                $result = \call_user_func_array($f, $head);
            }
            $underCall = false;
        }

        return $result;
    };
}

define('Basko\Functional\tail_recursion', __NAMESPACE__ . '\\tail_recursion');

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

define('Basko\Functional\not', __NAMESPACE__ . '\\not');

/**
 * Logical negation of the given function `$f`.
 *
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
        return !\call_user_func_array($f, \func_get_args());
    };
}

define('Basko\Functional\complement', __NAMESPACE__ . '\\complement');

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
    if (\func_num_args() < 2) {
        return function ($value) use ($f) {
            \call_user_func_array($f, [cp($value)]);

            return $value;
        };
    }

    \call_user_func_array($f, [cp($value)]);

    return $value;
}

define('Basko\Functional\tap', __NAMESPACE__ . '\\tap');

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

define('Basko\Functional\always', __NAMESPACE__ . '\\always');

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
    $functions = \func_get_args();

    /**
     * @return mixed|Maybe|Either
     */
    return function () use ($functions) {
        $args = \func_get_args();
        $index = \count($functions);
        while ($index) {
            $args = [\call_user_func_array($functions[--$index], $args)];
        }

        return \current($args);
    };
}

define('Basko\Functional\compose', __NAMESPACE__ . '\\compose');

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
    $functions = \func_get_args();

    /**
     * @return mixed|Maybe|Either
     */
    return function () use ($functions) {
        $args = \func_get_args();
        foreach ($functions as $function) {
            $args = [\call_user_func_array($function, $args)];
        }

        return \current($args);
    };
}

define('Basko\Functional\pipe', __NAMESPACE__ . '\\pipe');

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
 * $average = converge(div, ['array_sum', 'count']);
 * $average([1, 2, 3, 4]); // 2.5
 * ```
 *
 * @param callable $convergingFunction Will be invoked with the return values of all branching functions
 *                                     as its arguments
 * @param callable[] $branchingFunctions A list of functions
 * @return callable(mixed):mixed
 * @no-named-arguments
 */
function converge(callable $convergingFunction, array $branchingFunctions = null)
{
    if (\func_num_args() < 2) {
        return partial(converge, [$convergingFunction]);
    }

    InvalidArgumentException::assertListOfCallables($branchingFunctions, __FUNCTION__, 2);

    return function () use ($convergingFunction, $branchingFunctions) {
        $values = \func_get_args();

        $result = [];

        foreach ($branchingFunctions as $branchingFunction) {
            $result[] = \call_user_func_array($branchingFunction, $values);
        }

        return \call_user_func_array($convergingFunction, $result);
    };
}

define('Basko\Functional\converge', __NAMESPACE__ . '\\converge');

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
    $args = \func_get_args();

    if (count($args) < 2) {
        return function () use ($f) {
            return \call_user_func_array($f, flatten(\func_get_args()));
        };
    }

    return \call_user_func_array(head($args), flatten(tail($args)));
}

define('Basko\Functional\call', __NAMESPACE__ . '\\call');

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
    $args = \func_get_args();

    if (\count($args) < 2) {
        return function (callable $f) use ($arg) {
            return \call_user_func($f, $arg);
        };
    }

    $function = \array_pop($args);
    InvalidArgumentException::assertCallable($function, __FUNCTION__, 2);

    return \call_user_func_array($function, $args);
}

define('Basko\Functional\apply_to', __NAMESPACE__ . '\\apply_to');

/**
 * Performs an operation checking for the given conditions.
 * Returns a new function that behaves like a match operator. Encapsulates `if/elseif,elseif, ...` logic.
 *
 * ```php
 * $cond = cond([
 *      [eq(0), always('water freezes')],
 *      [partial_r(gte, 100), always('water boils')],
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

        list($if, $then) = \array_shift($conditions);

        return \call_user_func($if, $value)
            ? \call_user_func($then, $value)
            : \call_user_func(cond($conditions), $value);
    };
}

define('Basko\Functional\cond', __NAMESPACE__ . '\\cond');

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
        return \call_user_func_array($f, \array_reverse(\func_get_args()));
    };
}

define('Basko\Functional\flipped', __NAMESPACE__ . '\\flipped');

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
    if (\func_num_args() < 2) {
        return function ($g) use ($f) {
            return function ($a, $b) use ($f, $g) {
                return \call_user_func_array($f, [\call_user_func_array($g, [$a]), \call_user_func_array($g, [$b])]);
            };
        };
    }

    return function ($a, $b) use ($f, $g) {
        return \call_user_func_array($f, [\call_user_func_array($g, [$a]), \call_user_func_array($g, [$b])]);
    };
}

define('Basko\Functional\on', __NAMESPACE__ . '\\on');

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
        return \call_user_func($f, function () use ($fn) {
            return \call_user_func_array(\call_user_func($fn, $fn), \func_get_args());
        });
    };

    return \call_user_func($g, $g);
}

define('Basko\Functional\y', __NAMESPACE__ . '\\y');

/**
 * Acts as the boolean `and` statement.
 *
 * ```php
 * both(T(), T()); // true
 * both(F(), T()); // false
 * $between6And9 = both(partial_r(gt, [6]), partial_r(lt, [9]));
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
    if (\func_num_args() < 2) {
        return function ($b) use ($a) {
            if (\is_callable($a) && \is_callable($b)) {
                return function ($value) use ($a, $b) {
                    return \call_user_func_array($a, [$value]) && \call_user_func_array($b, [$value]);
                };
            }

            return $a && $b;
        };
    }

    if (\is_callable($a) && \is_callable($b)) {
        return function ($value) use ($a, $b) {
            return \call_user_func_array($a, [$value]) && \call_user_func_array($b, [$value]);
        };
    }

    return $a && $b;
}

define('Basko\Functional\both', __NAMESPACE__ . '\\both');

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

    if (\func_num_args() < 2) {
        return function ($value) use ($functions) {
            foreach ($functions as $f) {
                if (!\call_user_func($f, $value)) {
                    return false;
                }
            }

            return true;
        };
    }

    foreach ($functions as $f) {
        if (!\call_user_func($f, $value)) {
            return false;
        }
    }

    return true;
}

define('Basko\Functional\all_pass', __NAMESPACE__ . '\\all_pass');

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

    if (\func_num_args() < 2) {
        return function ($value) use ($functions) {
            foreach ($functions as $f) {
                if (\call_user_func($f, $value)) {
                    return true;
                }
            }

            return false;
        };
    }

    foreach ($functions as $f) {
        if (\call_user_func($f, $value)) {
            return true;
        }
    }

    return false;
}

define('Basko\Functional\any_pass', __NAMESPACE__ . '\\any_pass');

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

    if (\func_num_args() < 2) {
        $pfn = __FUNCTION__;

        return function ($list) use ($flist, $pfn) {
            InvalidArgumentException::assertList($list, $pfn, 2);

            $aggregation = [];

            foreach ($flist as $f) {
                $aggregation = \array_merge($aggregation, map($f, $list));
            }

            return $aggregation;
        };
    }

    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    $aggregation = [];

    foreach ($flist as $f) {
        $aggregation = \array_merge($aggregation, map($f, $list));
    }

    return $aggregation;
}

define('Basko\Functional\ap', __NAMESPACE__ . '\\ap');

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
            if (is_type_of(Monad::class, $possibleM)) {
                $ofFunc = call_user_func(cond([
                    [eq(Maybe::class), always(Maybe::just)],
                    [eq(Optional::class), always(Optional::just)],
                    [eq(Either::class), always(Either::right)],
                    [T, function ($type) {
                        return $type::of;
                    }],
                ]), \get_class($possibleM));

                return $possibleM->extract();
            }

            return $possibleM;
        }, \func_get_args());

        $toM = if_else(is_type_of(Monad::class), identity, $ofFunc);

        return $toM(\call_user_func_array($f, $extractedArgs));
    };
}

define('Basko\Functional\lift_m', __NAMESPACE__ . '\\lift_m');

/**
 * Create memoized versions of `$f` function.
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
 * ```php
 * $randAndSalt = function ($salt) {
 *      return rand(1, 100) . $salt;
 * };
 * $memoizedRandAndSalt = memoized($randAndSalt);
 * $memoizedRandAndSalt('x'); // 42x
 * $memoizedRandAndSalt('x'); // 42x
 * ```
 *
 * @param callable $f
 * @return callable
 * @no-named-arguments
 */
function memoized(callable $f)
{
    return function () use ($f) {
        static $cache = [];

        $args = \func_get_args();
        $key = _value_to_key(\array_merge([$f], $args));

        if (!isset($cache[$key]) || !\array_key_exists($key, $cache)) {
            $cache[$key] = \call_user_func_array($f, $args);
        }

        return $cache[$key];
    };
}

define('Basko\Functional\memoize', __NAMESPACE__ . '\\memoize');
