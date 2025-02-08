<?php

namespace Basko\Functional;

use Basko\Functional\Exception\InvalidArgumentException;

/**
 * Produces a new list of elements by mapping each element in list through a transformation function.
 * Function arguments will be `element`, `index`, `list`.
 *
 * ```
 * map(plus(1), [1, 2, 3]); // [2, 3, 4]
 * ```
 *
 * @template TKey of array-key
 * @template TValue
 * @template TNewValue
 * @template T of array<TKey, TValue>|\Traversable<TKey, TValue>
 * @template Tn of array<TKey, TNewValue>
 * @param callable(TValue $element, TKey $index, T $list):TNewValue $f
 * @param T $list
 * @return ($list is null ? callable(T $list):Tn : Tn)
 * @no-named-arguments
 */
function map(callable $f, $list = null)
{
    if (\func_num_args() < 2) {
        return partial(map, $f);
    }
    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    $aggregation = [];

    /** @var T $list */
    foreach ($list as $index => $element) {
        $aggregation[$index] = \call_user_func_array($f, [$element, $index, $list]);
    }

    /** @var Tn $aggregation */
    return $aggregation;
}

define('Basko\Functional\map', __NAMESPACE__ . '\\map');

/**
 * `flat_map` works applying `$f` that returns a sequence for each element in a list,
 * and flattening the results into the resulting array.
 *
 * `flat_map($data)` differs from `flatten(map($data))` because it only flattens one level of nesting,
 * whereas flatten will recursively flatten nested collections. Indexes will not preserve.
 *
 * ```
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
 * @template T of array<mixed>|\Traversable<mixed>
 * @param callable(mixed $element, mixed $index, T $list):mixed $f
 * @param T $list
 * @return ($list is null ? callable(T $list):array<mixed> : array<mixed>)
 * @no-named-arguments
 */
function flat_map(callable $f, $list = null)
{
    if (\func_num_args() < 2) {
        return partial(flat_map, $f);
    }
    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    $flattened = [];

    /** @var T $list */
    foreach ($list as $index => $element) {
        $result = \call_user_func_array($f, [$element, $index, $list]);

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
 * Function arguments will be `element`, `index`, `list`.
 *
 * ```
 * each(unary('print_r'), [1, 2, 3]); // Print: 123
 * ```
 *
 *
 * @template TKey of array-key
 * @template TValue
 * @template T of array<TKey, TValue>|\Traversable<TKey, TValue>
 * @param callable(TValue $element, TKey $index, T $list):void $f
 * @param T $list
 * @return ($list is null ? callable(T $list):T : T)
 * @no-named-arguments
 */
function each(callable $f, $list = null)
{
    if (\func_num_args() < 2) {
        return partial(each, $f);
    }
    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    /** @var T $list */
    foreach ($list as $index => $element) {
        \call_user_func_array($f, [$element, $index, $list]);
    }

    return $list;
}

define('Basko\Functional\each', __NAMESPACE__ . '\\each');

/**
 * Applies a function to each element in the list and reduces it to a single value.
 *
 * ```
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
 * @template Ta of scalar
 * @template Tl of array<mixed>|\Traversable<mixed>
 * @param callable(Ta $accumulator, mixed $value, mixed $index, Tl $list):mixed $f
 * @param Ta $accumulator
 * @param Tl $list
 * @return callable|Ta
 * @phpstan-return ($accumulator is null ? (callable(Ta $accumulator, Tl $list=):($list is null ? callable(Tl $list):Ta : Ta|Ta)) : ($list is null ? callable(Tl $list):Ta : Ta))
 * @no-named-arguments
 */
function fold(callable $f, $accumulator = null, $list = null)
{
    $n = \func_num_args();
    if ($n === 1) {
        return partial(fold, $f);
    } elseif ($n === 2) {
        return partial(fold, $f, $accumulator);
    }
    InvalidArgumentException::assertList($list, __FUNCTION__, 3);

    /** @var Tl $list */
    foreach ($list as $index => $value) {
        $accumulator = \call_user_func_array($f, [$accumulator, $value, $index, $list]);
    }

    return $accumulator;
}

define('Basko\Functional\fold', __NAMESPACE__ . '\\fold');

/**
 * The same as `fold` but accumulator on the right.
 *
 * ```
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
 * @template Ta of scalar
 * @template Tl of array<mixed>|\Traversable<mixed>
 * @param callable(mixed $value, Ta $accumulator, mixed $index, Tl $list):mixed $f
 * @param Ta $accumulator
 * @param Tl $list
 * @return callable|Ta
 * @phpstan-return ($accumulator is null ? (callable(Ta $accumulator, Tl $list=):($list is null ? callable(Tl $list):Ta : Ta|Ta)) : ($list is null ? callable(Tl $list):Ta : Ta))
 * @no-named-arguments
 */
function fold_r(callable $f, $accumulator = null, $list = null)
{
    $n = \func_num_args();
    if ($n === 1) {
        return partial(fold_r, $f);
    } elseif ($n === 2) {
        return partial(fold_r, $f, $accumulator);
    }
    InvalidArgumentException::assertList($list, __FUNCTION__, 3);

    $data = [];
    /** @var Tl $list */
    foreach ($list as $index => $value) {
        $data[] = [$index, $value];
    }

    for ($i = \count($data) - 1; $i >= 0; $i--) {
        list($index, $value) = $data[$i];
        $accumulator = \call_user_func_array($f, [$value, $accumulator, $index, $list]);
    }

    return $accumulator;
}

define('Basko\Functional\fold_r', __NAMESPACE__ . '\\fold_r');

/**
 * Returns a new list containing the contents of the given list, followed by the given element.
 *
 * ```
 * append('three', ['one', 'two']); // ['one', 'two', 'three']
 * ```
 *
 * @template T of array<mixed>|\Traversable
 * @param mixed $element
 * @param T $list
 * @return ($list is null ? callable(T $list):array<mixed> : array<mixed>)
 * @no-named-arguments
 */
function append($element, $list = null)
{
    if (\func_num_args() < 2) {
        return partial(append, $element);
    }

    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    $aggregation = [];

    /** @var T $list */
    foreach ($list as $listElement) {
        $aggregation[] = $listElement;
    }
    $aggregation[] = $element;

    return $aggregation;
}

define('Basko\Functional\append', __NAMESPACE__ . '\\append');

/**
 * Returns a new list with the given element at the front, followed by the contents of the list.
 *
 * ```
 * prepend('three', ['one', 'two']); // ['three', 'one', 'two']
 * ```
 *
 * @template T of array<mixed>|\Traversable<mixed>
 * @param mixed $element
 * @param T $list
 * @return ($list is null ? callable(T $list):array<mixed> : array<mixed>)
 * @no-named-arguments
 */
function prepend($element, $list = null)
{
    if (\func_num_args() < 2) {
        return partial(prepend, $element);
    }

    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    $aggregation = [$element];

    /** @var T $list */
    foreach ($list as $listElement) {
        $aggregation[] = $listElement;
    }

    return $aggregation;
}

define('Basko\Functional\prepend', __NAMESPACE__ . '\\prepend');

/**
 * Extract a property from a list of objects.
 *
 * ```
 * pluck('qty', [['qty' => 2], ['qty' => 1]]); // [2, 1]
 * ```
 *
 * @template T of array<mixed>|\Traversable<mixed>
 * @param string $property
 * @param T $list
 * @return ($list is null ? callable(T $list):array<mixed> : array<mixed>)
 * @no-named-arguments
 */
function pluck($property, $list = null)
{
    InvalidArgumentException::assertString($property, __FUNCTION__, 1);

    if (\func_num_args() < 2) {
        return partial(pluck, $property);
    }
    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    $aggregation = [];

    /** @var T $list */
    foreach ($list as $element) {
        $aggregation[] = prop($property, $element);
    }

    return $aggregation;
}

define('Basko\Functional\pluck', __NAMESPACE__ . '\\pluck');

/**
 * Looks through each element in the list, returning the first one.
 *
 * ```
 * head([
 *      ['name' => 'jack', 'score' => 1],
 *      ['name' => 'mark', 'score' => 9],
 *      ['name' => 'john', 'score' => 1],
 * ]); // ['name' => 'jack', 'score' => 1]
 * ```
 *
 * @param array<mixed>|\Traversable<mixed> $list
 * @return mixed
 * @no-named-arguments
 */
function head($list)
{
    InvalidArgumentException::assertList($list, __FUNCTION__, 1);

    foreach ($list as $element) {
        return $element;
    }

    return null;
}

define('Basko\Functional\head', __NAMESPACE__ . '\\head');

/**
 * Looks through each element in the list, returning the first one that passes a truthy test (function). The
 * function returns as soon as it finds an acceptable element, and doesn't traverse the entire list. Function
 * arguments will be `element`, `index`, `list`
 *
 * @template TKey of array-key
 * @template TValue
 * @template T of array<TKey, TValue>|\Traversable<TKey, TValue>
 * @param callable(TValue $element, TKey $index, T $list):bool $f
 * @param T $list
 * @return ($list is null ? callable(T $list):TValue|null : TValue|null)
 * @no-named-arguments
 */
function head_by(callable $f, $list = null)
{
    if (\func_num_args() < 2) {
        return partial(head_by, $f);
    }

    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    /** @var T $list */
    foreach ($list as $index => $element) {
        if (\call_user_func_array($f, [$element, $index, $list])) {
            return $element;
        }
    }

    return null;
}

define('Basko\Functional\head_by', __NAMESPACE__ . '\\head_by');

/**
 * Returns all items from `$list` except first element (head). Preserves `$list` keys.
 *
 * ```
 * tail([
 *      ['name' => 'jack', 'score' => 1],
 *      ['name' => 'mark', 'score' => 9],
 *      ['name' => 'john', 'score' => 1],
 * ]); // [1 => ['name' => 'mark', 'score' => 9], 2 => ['name' => 'john', 'score' => 1]]
 * ```
 *
 * @param array<mixed>|\Traversable<mixed> $list
 * @return array<mixed>
 */
function tail($list)
{
    InvalidArgumentException::assertList($list, __FUNCTION__, 1);

    $tail = [];
    $isHead = true;

    foreach ($list as $index => $element) {
        if ($isHead) {
            $isHead = false;
            continue;
        }

        $tail[$index] = $element;
    }

    return $tail;
}

define('Basko\Functional\tail', __NAMESPACE__ . '\\tail');

/**
 * Returns all items from `$list` except first element (head) if `$f` returns true. Preserves `$list` keys.
 * Can be considered as `tail` + `select`.
 *
 * ```
 * tail_by(f\compose(gt(8), prop('score')), [
 *      ['name' => 'jack', 'score' => 1],
 *      ['name' => 'mark', 'score' => 9],
 *      ['name' => 'john', 'score' => 1],
 * ]); // [1 => ['name' => 'mark', 'score' => 9]]
 * ```
 *
 * @template TKey of array-key
 * @template TValue
 * @template T of array<TKey, TValue>|\Traversable<TKey, TValue>
 * @param callable(TValue $element, TKey $index, T $list):bool $f
 * @param T $list
 * @return ($list is null ? callable(T $list):array<TKey, TValue> : array<TKey, TValue>)
 * @no-named-arguments
 */
function tail_by(callable $f, $list = null)
{
    if (\func_num_args() < 2) {
        return partial(tail_by, $f);
    }
    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    $tail = [];
    $isHead = true;

    /** @var T $list */
    foreach ($list as $index => $element) {
        if ($isHead) {
            $isHead = false;
            continue;
        }

        if (\call_user_func_array($f, [$element, $index, $list])) {
            $tail[$index] = $element;
        }
    }

    return $tail;
}

define('Basko\Functional\tail_by', __NAMESPACE__ . '\\tail_by');

/**
 * Looks through each element in the list, returning an array of all the elements that pass a test (function).
 * Opposite is `reject()`. Function arguments will be `element`, `index`, `list`.
 *
 * ```
 * $activeUsers = select(invoker('isActive'), [$user1, $user2, $user3]);
 * ```
 *
 * @template TKey of array-key
 * @template TValue
 * @template T of array<TKey, TValue>|\Traversable<TKey, TValue>
 * @param callable(TValue $element, TKey $index, T $list):bool $f
 * @param T $list
 * @return ($list is null ? callable(T $list):array<TKey, TValue> : array<TKey, TValue>)
 * @no-named-arguments
 */
function select(callable $f, $list = null)
{
    if (\func_num_args() < 2) {
        return partial(select, $f);
    }

    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    $aggregation = [];

    /** @var T $list */
    foreach ($list as $index => $element) {
        if (\call_user_func_array($f, [$element, $index, $list])) {
            $aggregation[$index] = $element;
        }
    }

    return $aggregation;
}

define('Basko\Functional\select', __NAMESPACE__ . '\\select');

/**
 * Returns the elements in list without the elements that the test (function) passes.
 * The opposite of `select()`. Function arguments will be `element`, `index`, `list`.
 *
 * ```
 * $inactiveUsers = reject(invoker('isActive'), [$user1, $user2, $user3]);
 * ```
 *
 * @template TKey of array-key
 * @template TValue
 * @template T of array<TKey, TValue>|\Traversable<TKey, TValue>
 * @param callable(TValue $element, TKey $index, T $list):bool $f
 * @param T $list
 * @return ($list is null ? callable(T $list):array<TKey, TValue> : array<TKey, TValue>)
 * @no-named-arguments
 */
function reject(callable $f, $list = null)
{
    if (\func_num_args() < 2) {
        return partial(reject, $f);
    }

    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    $aggregation = [];

    /** @var T $list */
    foreach ($list as $index => $element) {
        if (!\call_user_func_array($f, [$element, $index, $list])) {
            $aggregation[$index] = $element;
        }
    }

    return $aggregation;
}

define('Basko\Functional\reject', __NAMESPACE__ . '\\reject');

/**
 * Returns true if the list contains the given value. If the third parameter is
 * true values will be compared in strict mode.
 *
 * ```
 * contains('foo', ['foo', 'bar']); // true
 * contains('foo', 'foo and bar'); // true
 * ```
 *
 * @param mixed $needle
 * @param string|array<mixed>|\Traversable<mixed> $haystack
 * @return ($haystack is null ? callable(string|array<mixed>|\Traversable<mixed> $haystack):bool : bool)
 * @no-named-arguments
 */
function contains($needle, $haystack = null)
{
    if (\func_num_args() < 2) {
        return partial(contains, $needle);
    }

    InvalidArgumentException::assertStringOrList($haystack, __FUNCTION__, 2);

    if (\is_string($haystack)) {
        return $needle === '' || false !== \strpos($haystack, $needle);
    }

    /** @var array<mixed>|\Traversable<mixed> $haystack */
    foreach ($haystack as $element) {
        if ($needle === $element) {
            return true;
        }
    }

    return false;
}

define('Basko\Functional\contains', __NAMESPACE__ . '\\contains');

/**
 * Creates a slice of `$list` with `$count` elements taken from the beginning. If the list has less than `$count`,
 * the whole list will be returned as an array.
 * For strings its works like `substr`.
 *
 * ```
 * take(2, [1, 2, 3]); // [0 => 1, 1 => 2]
 * take(4, 'Slava'); // 'Slav'
 * ```
 *
 * @template T
 * @param string|array<T>|\Traversable<T> $list
 * @param int $count
 * @return callable|string|array
 * @phpstan-return ($list is null ? callable(string|array<T>|\Traversable<T> $list):($list is string ? string : array<T>) : ($list is string ? string : array<T>))
 * @no-named-arguments
 */
function take($count, $list = null)
{
    InvalidArgumentException::assertInteger($count, __FUNCTION__, 1);

    if (\func_num_args() < 2) {
        return partial(take, $count);
    }

    InvalidArgumentException::assertStringOrList($list, __FUNCTION__, 2);

    if (\is_string($list)) {
        return \substr($list, 0, $count);
    }

    /** @var array<T>|\Traversable<T> $list */
    // TODO: foreach?
    return \array_slice(
        \is_array($list) ? $list : \iterator_to_array($list),
        0,
        $count
    );
}

define('Basko\Functional\take', __NAMESPACE__ . '\\take');

/**
 * Creates a slice of `$list` with `$count` elements taken from the end. If the list has less than `$count`,
 * the whole list will be returned as an array.
 * For strings its works like `substr`.
 *
 * ```
 * take_r(2, [1, 2, 3]); // [1 => 2, 2 => 3]
 * take_r(4, 'Slava'); // 'lava'
 * ```
 *
 * @template T
 * @param string|array<T>|\Traversable<T> $list
 * @param int $count
 * @return callable|string|array
 * @phpstan-return ($list is null ? callable(string|array<T>|\Traversable<T> $list):($list is string ? string : array<T>) : ($list is string ? string : array<T>))
 * @no-named-arguments
 */
function take_r($count, $list = null)
{
    InvalidArgumentException::assertInteger($count, __FUNCTION__, 1);

    if (\func_num_args() < 2) {
        return partial(take_r, $count);
    }

    InvalidArgumentException::assertStringOrList($list, __FUNCTION__, 2);

    if (\is_string($list)) {
        return \substr($list, -$count);
    }

    /** @var array<mixed>|\Traversable<mixed> $list */
    // TODO: foreach?
    return \array_slice(
        \is_array($list) ? $list : \iterator_to_array($list),
        0 - $count,
        $count,
        true
    );
}

define('Basko\Functional\take_r', __NAMESPACE__ . '\\take_r');

/**
 * Return N-th element of an array or string.
 * First element is first, but not zero. So you need to write `nth(1, ['one', 'two']); // one` if you want first item.
 * Consider `$elementNumber` as a position but not index.
 *
 * ```
 * nth(1, ['foo', 'bar', 'baz', 'qwe']); // 'foo'
 * nth(-1, ['foo', 'bar', 'baz', 'qwe']); // 'qwe'
 * nth(1, 'Slava'); // 'S'
 * nth(-2, 'Slava'); // 'v'
 * ```
 *
 * @template E
 * @template T of string|array<E>|\Traversable<E>
 * @param int $elementNumber
 * @param T $list
 * @return callable|string|E
 * @phpstan-return ($list is null ? callable(T $list):($list is string ? string|null : E|null) : ($list is string ? string|null : E|null))
 * @no-named-arguments
 */
function nth($elementNumber, $list = null)
{
    InvalidArgumentException::assertInteger($elementNumber, __FUNCTION__, 1);

    if (\func_num_args() < 2) {
        return partial(nth, $elementNumber);
    }

    InvalidArgumentException::assertStringOrList($list, __FUNCTION__, 2);

    /** @var array<E>|\Traversable<E> $list */
    if ($list instanceof \Traversable) {
        $list = \array_values(\iterator_to_array($list));
    }

    $len = len($list);

    if ($elementNumber < 0) {
        $elementNumber = $len - \abs($elementNumber);
    } else {
        $elementNumber = $elementNumber - 1;
    }

    if (\is_array($list)) {
        for ($i = 1; $i <= $len; $i++) {
            if ($i == $elementNumber) {
                return $list[$elementNumber];
            }
        }
    }

    return isset($list[$elementNumber]) ? $list[$elementNumber] : null;
}

define('Basko\Functional\nth', __NAMESPACE__ . '\\nth');

/**
 * Groups a list by index returned by `$f` function.
 *
 * ```
 * group(prop('type'), [
 *      [
 *          'name' => 'john',
 *          'type' => 'admin'
 *      ],
 *      [
 *          'name' => 'mark',
 *          'type' => 'user'
 *      ],
 *      [
 *          'name' => 'bill',
 *          'type' => 'user'
 *      ],
 *      [
 *          'name' => 'jack',
 *          'type' => 'anonymous'
 *      ],
 * ]); // ['admin' => [...], 'user' => [...], 'anonymous' => [...]]
 * ```
 *
 * @template TKey of array-key
 * @template TValue
 * @template T of array<TKey, TValue>|\Traversable<TKey, TValue>
 * @param callable(TValue $element, TKey $index, T $list):string $f
 * @param T $list
 * @return ($list is null ? callable(T $list):array<array-key, array<TKey, TValue>> : array<array-key, array<TKey, TValue>>)
 * @no-named-arguments
 */
function group(callable $f, $list = null)
{
    if (\func_num_args() < 2) {
        return partial(group, $f);
    }

    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    $groups = [];

    /** @var T $list */
    foreach ($list as $index => $element) {
        $groupKey = \call_user_func_array($f, [$element, $index, $list]);

        if (!isset($groups[$groupKey])) {
            $groups[$groupKey] = [];
        }

        $groups[$groupKey][$index] = $element;
    }

    return $groups;
}

define('Basko\Functional\group', __NAMESPACE__ . '\\group');

/**
 * Partitions a list by function predicate results. Returns an
 * array of partition arrays, one for each predicate, and one for
 * elements which don't pass any predicate. Elements are placed in the
 * partition for the first predicate they pass.
 *
 * Elements are not re-ordered and have the same index they had in the
 * original array.
 *
 * ```
 * list($best, $good_students, $others) = partition(
 *      [
 *          compose(partial_r(gte, 9), prop('score')),
 *          compose(both(partial_r(gte, 6), partial_r(lt, 9)), prop('score'))
 *      ],
 *      $students
 * );
 * ```
 *
 * @template TKey of array-key
 * @template TValue
 * @template T of array<TKey, TValue>|\Traversable<TKey, TValue>
 * @param array<callable(TValue $element, TKey $index, T $list):bool> $functions
 * @param T $list
 * @return ($list is null ? callable(T $list):array<array-key, array<TKey, TValue>> : array<array-key, array<TKey, TValue>>)
 * @no-named-arguments
 */
function partition(array $functions, $list = null)
{
    InvalidArgumentException::assertListOfCallables($functions, __FUNCTION__, 1);

    if (\func_num_args() < 2) {
        return partial(partition, $functions);
    }
    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    $lastPartition = \count($functions);
    $partitions = \array_fill(0, $lastPartition + 1, []);

    /** @var T $list */
    foreach ($list as $index => $element) {
        foreach ($functions as $partition => $fn) {
            if (\call_user_func_array($fn, [$element, $index, $list])) {
                $partitions[$partition][$index] = $element;
                continue 2;
            }
        }

        $partitions[$lastPartition][$index] = $element;
    }

    return $partitions;
}

define('Basko\Functional\partition', __NAMESPACE__ . '\\partition');

/**
 * Takes a nested combination of list and returns their contents as a single, flat list.
 *
 * ```
 * flatten([1, 2, [3, 4], 5, [6, [7, 8, [9, [10, 11], 12]]]]); // [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
 * flatten([1 => 1, 'foo' => '2', 3 => '3', ['foo' => 5]]); // [1, "2", "3", 5]
 * ```
 *
 * @param array<mixed>|\Traversable<mixed> $list
 * @return array<mixed>
 * @no-named-arguments
 */
function flatten($list)
{
    InvalidArgumentException::assertList($list, __FUNCTION__, 1);

    $result = [];
    foreach ($list as $value) {
        if (\is_array($value) || $value instanceof \Traversable) {
            $result = \array_merge($result, flatten($value));
        } else {
            $result[] = $value;
        }
    }

    return $result;
}

define('Basko\Functional\flatten', __NAMESPACE__ . '\\flatten');

/**
 * Takes a nested combination of list and returns their contents as a single, flat list.
 * Keys concatenated by `.` and element index.
 *
 * ```
 * flatten_with_keys([
 *  'title' => 'Some title',
 *  'body' => 'content',
 *  'comments' => [
 *      [
 *          'author' => 'user1',
 *          'body' => 'comment body 1'
 *      ],
 *      [
 *          'author' => 'user2',
 *          'body' => 'comment body 2'
 *      ]
 *  ]
 * ]);
 *
 * //  [
 * //      'title' => 'Some title',
 * //      'body' => 'content',
 * //      'comments.0.author' => 'user1',
 * //      'comments.0.body' => 'comment body 1',
 * //      'comments.1.author' => 'user2',
 * //      'comments.1.body' => 'comment body 2',
 * //  ]
 * ```
 *
 * @param array<mixed>|\Traversable<mixed> $list
 * @return array<mixed>
 * @no-named-arguments
 */
function flatten_with_keys($list)
{
    $args = \func_get_args();
    $prefix = (!\array_key_exists(1, $args) || \is_null($args[1])) ? '' : $args[1];

    InvalidArgumentException::assertList($list, __FUNCTION__, 1);

    $result = [];
    foreach ($list as $key => $value) {
        if (\is_array($value) || $value instanceof \Traversable) {
            $result = \array_merge($result, flatten_with_keys($value, $prefix . $key . '.'));
        } else {
            $result[$prefix . $key] = $value;
        }
    }

    return $result;
}

define('Basko\Functional\flatten_with_keys', __NAMESPACE__ . '\\flatten_with_keys');

/**
 * Insert a given value between each element of a collection.
 * Indexes are not preserved.
 *
 * ```
 * intersperse('a', ['b', 'n', 'n', 's']); // ['b', 'a', 'n', 'a', 'n', 'a', 's']
 * ```
 *
 * @param mixed $separator
 * @param array<mixed>|\Traversable<mixed> $list
 * @return ($list is null ? callable(array<mixed>|\Traversable<mixed> $list):array<mixed> : array<mixed>)
 * @no-named-arguments
 */
function intersperse($separator, $list = null)
{
    if (\func_num_args() < 2) {
        return partial(intersperse, $separator);
    }
    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    $aggregation = [];

    /** @var array<mixed>|\Traversable<mixed> $list */
    foreach ($list as $element) {
        $aggregation[] = $element;
        $aggregation[] = $separator;
    }

    \array_pop($aggregation);

    return $aggregation;
}

define('Basko\Functional\intersperse', __NAMESPACE__ . '\\intersperse');

/**
 * Sorts a list with a user-defined function.
 *
 * ```
 * sort(binary('strcmp'), ['cat', 'bear', 'aardvark'])); // [2 => 'aardvark', 1 => 'bear', 0 => 'cat']
 * ```
 *
 * @template T
 * @param callable(T $a, T $b):int $f
 * @param array<mixed>|\Traversable<mixed> $list
 * @return ($list is null ? callable(array<mixed>|\Traversable<mixed> $list):array<mixed> : array<mixed>)
 * @no-named-arguments
 */
function sort(callable $f, $list = null)
{
    if (\func_num_args() < 2) {
        return partial(sort, $f);
    }

    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    if ($list instanceof \Traversable) {
        $array = \iterator_to_array($list);
    } else {
        $array = $list;
    }

    /** @var array<mixed> $array */
    \uasort(
        $array,
        /**
         * @param mixed $left
         * @param mixed $right
         * @return int
         */
        function ($left, $right) use ($f, $list) {
            return \call_user_func_array($f, [$left, $right, $list]);
        }
    );

    return $array;
}

define('Basko\Functional\sort', __NAMESPACE__ . '\\sort');

/**
 * Makes a comparator function out of a function that reports whether the first
 * element is less than the second.
 *
 * ```
 * $ar = [1, 1, 2, 3, 5, 8];
 * usort($ar, comparator(function ($a, $b) {
 *      return $a < $b;
 * })); // $ar = [1, 1, 2, 3, 5, 8]
 *
 * sort(
 *      comparator(function ($a, $b) {
 *          return prop('age', $a) < prop('age', $b);
 *      }),
 *      [
 *          ['name' => 'Emma', 'age' => 70],
 *          ['name' => 'Peter', 'age' => 78],
 *          ['name' => 'Mikhail', 'age' => 62],
 *      ]
 * ); // [['name' => 'Mikhail', 'age' => 62], ['name' => 'Emma', 'age' => 70], ['name' => 'Peter', 'age' => 78]]
 * ```
 *
 * @template T
 * @param callable(T $a, T $b):bool $f
 * @return callable(T $a, T $b):int
 */
function comparator(callable $f)
{
    return function ($a, $b) use ($f) {
        return \call_user_func_array($f, [$a, $b]) ? -1 : (\call_user_func_array($f, [$b, $a]) ? 1 : 0);
    };
}

define('Basko\Functional\comparator', __NAMESPACE__ . '\\comparator');

/**
 * Makes an ascending comparator function out of a function that returns a value that can be compared with `<` and `>`.
 *
 * ```
 * sort(ascend(prop('age')), [
 *      ['name' => 'Emma', 'age' => 70],
 *      ['name' => 'Peter', 'age' => 78],
 *      ['name' => 'Mikhail', 'age' => 62],
 * ]); // [['name' => 'Mikhail', 'age' => 62], ['name' => 'Emma', 'age' => 70], ['name' => 'Peter', 'age' => 78]]
 * ```
 *
 * @template T
 * @param callable(T):mixed $f
 * @param T $a
 * @param T $b
 * @return callable|int
 * @phpstan-return ($a is null ? (callable(T $a, T $b=):($b is null ? callable(T $b):int : int|int)) : ($b is null ? callable(T $b):int : int))
 * @no-named-arguments
 */
function ascend(callable $f, $a = null, $b = null)
{
    $n = \func_num_args();
    if ($n === 1) {
        return partial(ascend, $f);
    } elseif ($n === 2) {
        return partial(ascend, $f, $a);
    }

    $aa = \call_user_func_array($f, [$a]);
    $bb = \call_user_func_array($f, [$b]);

    return $aa < $bb ? -1 : ($aa > $bb ? 1 : 0);
}

define('Basko\Functional\ascend', __NAMESPACE__ . '\\ascend');

/**
 * Makes a descending comparator function out of a function that returns a value that can be compared with `<` and `>`.
 *
 * ```
 * sort(descend(prop('age')), [
 *      ['name' => 'Emma', 'age' => 70],
 *      ['name' => 'Peter', 'age' => 78],
 *      ['name' => 'Mikhail', 'age' => 62],
 * ]); // [['name' => 'Peter', 'age' => 78], ['name' => 'Emma', 'age' => 70], ['name' => 'Mikhail', 'age' => 62]]
 * ```
 *
 * @template T
 * @param callable(T):mixed $f
 * @param T $a
 * @param T $b
 * @return callable|int
 * @phpstan-return ($a is null ? (callable(T $a, T $b=):($b is null ? callable(T $b):int : int|int)) : ($b is null ? callable(T $b):int : int))
 * @no-named-arguments
 */
function descend(callable $f, $a = null, $b = null)
{
    $n = \func_num_args();
    if ($n === 1) {
        return partial(descend, $f);
    } elseif ($n === 2) {
        return partial(descend, $f, $a);
    }

    $aa = \call_user_func_array($f, [$a]);
    $bb = \call_user_func_array($f, [$b]);

    return $aa > $bb ? -1 : ($aa < $bb ? 1 : 0);
}

define('Basko\Functional\descend', __NAMESPACE__ . '\\descend');

/**
 * Returns a new list containing only one copy of each element in the original list,
 * based upon the value returned by applying the supplied function to each list element.
 * Prefers the first item if the supplied function produces the same value on two items.
 *
 * ```
 * uniq_by('abs', [-1, -5, 2, 10, 1, 2]); // [-1, -5, 2, 10]
 * ```
 *
 * @template T
 * @param callable(T):mixed $f
 * @param array<T>|\Traversable<T> $list
 * @return ($list is null ? callable(array<T>|\Traversable<T> $list):array<T> : array<T>)
 * @no-named-arguments
 */
function uniq_by(callable $f, $list = null)
{
    if (\func_num_args() < 2) {
        return partial(uniq_by, $f);
    }

    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    $_aggregation = [];
    $aggregation = [];

    /** @var T $list */
    foreach ($list as $element) {
        $appliedItem = \call_user_func_array($f, [$element]);
        if (!\in_array($appliedItem, $_aggregation, true)) {
            $_aggregation[] = $appliedItem;
            $aggregation[] = $element;
        }
    }

    return $aggregation;
}

define('Basko\Functional\uniq_by', __NAMESPACE__ . '\\uniq_by');

/**
 * Returns a new list containing only one copy of each element in the original list.
 *
 * ```
 * uniq([1, 1, 2, 1]); // [1, 2]
 * uniq([1, '1']); // [1, '1']
 * ```
 *
 * @template T
 * @param array<T>|\Traversable<T> $list
 * @return array<T>
 * @no-named-arguments
 */
function uniq($list)
{
    InvalidArgumentException::assertList($list, __FUNCTION__, 1);

    return uniq_by(identity, $list);
}

define('Basko\Functional\uniq', __NAMESPACE__ . '\\uniq');

/**
 * Internal function for `zip` and `zip_with`.
 *
 * @return array<mixed>
 * @internal
 */
function _zip()
{
    $arrays = \func_get_args();
    $functionName = \array_shift($arrays);
    $callback = \array_shift($arrays);

    foreach ($arrays as $position => $arr) {
        InvalidArgumentException::assertList($arr, $functionName, $position + 1);
    }

    $resultKeys = [];
    foreach ($arrays as $arg) {
        foreach ($arg as $index => $value) {
            $resultKeys[] = $index;
        }
    }

    $resultKeys = \array_unique($resultKeys);

    $result = [];

    foreach ($resultKeys as $key) {
        $zipped = [];

        foreach ($arrays as $arg) {
            $zipped[] = isset($arg[$key]) ? $arg[$key] : null;
        }

        $result[$key] = \call_user_func($callback, $zipped);
    }

    return $result;
}

/**
 * Zips two or more sequences.
 *
 * Note: This function is not curried because of no fixed arity.
 *
 * ```
 * zip([1, 2], ['a', 'b']); // [[1, 'a'], [2, 'b']]
 * ```
 *
 * @template T1
 * @template T2
 * @param array<T1>|\Traversable<T1> $list1
 * @param array<T2>|\Traversable<T2> $list2
 * @return array<array{T1, T2}>
 * @no-named-arguments
 */
function zip($list1, $list2)
{
    return \call_user_func_array('Basko\Functional\_zip', \array_merge([__FUNCTION__, identity], \func_get_args()));
}

define('Basko\Functional\zip', __NAMESPACE__ . '\\zip');

/**
 * Zips two or more sequences with given function `$f`.
 *
 * As a result: `zip_with(plus, [1, 2], [3, 4])` equals to `plus([$arg1, $arg2])`.
 * But `zip_with(call(plus), [1, 2], [3, 4])` equals to `plus($arg1, $arg2)`.
 * Note: This function is not curried because of no fixed arity.
 *
 * ```
 * zip_with(call(plus), [1, 2], [3, 4]); // [4, 6]
 * ```
 *
 * @template T1
 * @template T2
 * @param callable(array{T1, T2}):mixed $f
 * @param array<T1>|\Traversable<T1> $list1
 * @param array<T2>|\Traversable<T2> $list2
 * @return ($list1 is null ? callable(array<T1>|\Traversable<T1> $list1, array<T2>|\Traversable<T2> $list2):array<array{T1, T2}> : array<array{T1, T2}>)
 * @no-named-arguments
 */
function zip_with(callable $f, $list1 = null, $list2 = null)
{
    $args = \func_get_args();
    $f = \array_shift($args);

    if (empty($args)) {
        return partial(zip_with, $f);
    }

    return \call_user_func_array('Basko\Functional\_zip', \array_merge([__FUNCTION__, $f], $args));
}

define('Basko\Functional\zip_with', __NAMESPACE__ . '\\zip_with');

/**
 * Returns all possible permutations.
 *
 * ```
 * permute(['a', 'b']); // [['a', 'b'], ['b', 'a']]
 * ```
 *
 * @template T
 * @param array<T>|\Traversable<T> $list
 * @return array<array<T>>
 * @no-named-arguments
 */
function permute($list)
{
    InvalidArgumentException::assertList($list, __FUNCTION__, 1);

    $list = \is_array($list) ? $list : \iterator_to_array($list);
    $result = [];

    if (\count($list) <= 1) {
        $result[] = $list;
    } else {
        foreach (permute(\array_slice($list, 1)) as $permutation) {
            foreach (\range(0, \count($list) - 1) as $i) {
                $result[] = \array_merge(
                    \array_slice($permutation, 0, $i),
                    [$list[0]],
                    \array_slice($permutation, $i)
                );
            }
        }
    }

    return $result;
}

define('Basko\Functional\permute', __NAMESPACE__ . '\\permute');
