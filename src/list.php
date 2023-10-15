<?php

namespace Basko\Functional;

use Basko\Functional\Exception\InvalidArgumentException;
use Traversable;

/**
 * Returns a new list containing the contents of the given list, followed by the given element.
 *
 * ```php
 * append('three', ['one', 'two']); // ['one', 'two', 'three']
 * ```
 *
 * @param mixed $element
 * @param \Traversable|array|null $list
 * @return array|callable
 * @no-named-arguments
 */
function append($element, $list = null)
{
    $args = func_get_args();

    if (count($args) < 2) {
        return partial(append, $element);
    }

    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    $aggregation = [];

    foreach ($list as $listElement) {
        $aggregation[] = $listElement;
    }
    $aggregation[] = $element;

    return $aggregation;
}

define('Basko\Functional\append', __NAMESPACE__ . '\\append', false);

/**
 * Returns a new list with the given element at the front, followed by the contents of the list.
 *
 * ```php
 * prepend('three', ['one', 'two']); // ['three', 'one', 'two']
 * ```
 *
 * @param mixed $element
 * @param \Traversable|array|null $list
 * @return array|callable
 * @no-named-arguments
 */
function prepend($element, $list = null)
{
    $args = func_get_args();

    if (count($args) < 2) {
        return partial(prepend, $element);
    }

    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    $aggregation = [$element];

    foreach ($list as $listElement) {
        $aggregation[] = $listElement;
    }

    return $aggregation;
}

define('Basko\Functional\prepend', __NAMESPACE__ . '\\prepend', false);

/**
 * Extract a property from a list of objects.
 *
 * ```php
 * pluck('qty', [['qty' => 2], ['qty' => 1]]); // [2, 1]
 * ```
 *
 * @param string $property
 * @param \Traversable|array $list
 * @return callable|array
 * @no-named-arguments
 */
function pluck($property, $list = null)
{
    InvalidArgumentException::assertString($property, __FUNCTION__, 1);

    $args = func_get_args();

    if (count($args) < 2) {
        return partial(pluck, $property);
    }
    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    return map(prop($property), $list);
}

define('Basko\Functional\pluck', __NAMESPACE__ . '\\pluck', false);

/**
 * Looks through each element in the list, returning the first one.
 *
 * ```php
 * head([
 *      ['name' => 'jack', 'score' => 1],
 *      ['name' => 'mark', 'score' => 9],
 *      ['name' => 'john', 'score' => 1],
 * ]); // ['name' => 'jack', 'score' => 1]
 * ```
 *
 * @param \Traversable|array $list
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

define('Basko\Functional\head', __NAMESPACE__ . '\\head', false);

/**
 * Looks through each element in the list, returning the first one that passes a truthy test (function). The
 * function returns as soon as it finds an acceptable element, and doesn't traverse the entire list. Function
 * arguments will be `element`, `index`, `list`
 *
 * @param callable $f
 * @param \Traversable|array $list
 * @return callable|mixed
 * @no-named-arguments
 */
function head_by(callable $f, $list = null)
{
    $args = func_get_args();

    if (count($args) < 2) {
        return partial(head_by, $f);
    }
    InvalidArgumentException::assertList($list, __FUNCTION__, 2);


    foreach ($list as $index => $element) {
        if (call_user_func_array($f, [$element, $index, $list])) {
            return $element;
        }
    }

    return null;
}

define('Basko\Functional\head_by', __NAMESPACE__ . '\\head_by', false);

/**
 * Returns all items from `$list` except first element (head). Preserves `$list` keys.
 *
 * ```php
 * tail([
 *      ['name' => 'jack', 'score' => 1],
 *      ['name' => 'mark', 'score' => 9],
 *      ['name' => 'john', 'score' => 1],
 * ]); // [1 => ['name' => 'mark', 'score' => 9], 2 => ['name' => 'john', 'score' => 1]]
 * ```
 *
 * @param \Traversable|array $list
 * @return array
 * @no-named-arguments
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

define('Basko\Functional\tail', __NAMESPACE__ . '\\tail', false);

/**
 * @param callable $f
 * @param \Traversable|array $list
 * @return callable|array
 * @no-named-arguments
 */
function tail_by(callable $f, $list = null)
{
    $args = func_get_args();

    if (count($args) < 2) {
        return partial(tail_by, $f);
    }
    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    $tail = [];
    $isHead = true;

    foreach ($list as $index => $element) {
        if ($isHead) {
            $isHead = false;
            continue;
        }

        if (call_user_func_array($f, [$element, $index, $list])) {
            $tail[$index] = $element;
        }
    }

    return $tail;
}

define('Basko\Functional\tail_by', __NAMESPACE__ . '\\tail_by', false);

/**
 * Looks through each element in the list, returning an array of all the elements that pass a test (function).
 * Opposite is `reject()`. Function arguments will be `element`, `index`, `list`.
 *
 * ```php
 * $activeUsers = select(invoker('isActive'), [$user1, $user2, $user3]);
 * ```
 *
 * @param callable $f
 * @param \Traversable|array $list
 * @return callable|array
 * @no-named-arguments
 */
function select(callable $f, $list = null)
{
    $args = func_get_args();

    if (count($args) < 2) {
        return partial(select, $f);
    }
    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    $aggregation = [];

    foreach ($list as $index => $element) {
        if (call_user_func_array($f, [$element, $index, $list])) {
            $aggregation[$index] = $element;
        }
    }

    return $aggregation;
}

define('Basko\Functional\select', __NAMESPACE__ . '\\select', false);

/**
 * Returns the elements in list without the elements that the test (function) passes.
 * The opposite of `select()`. Function arguments will be `element`, `index`, `list`.
 *
 * ```php
 * $inactiveUsers = reject(invoker('isActive'), [$user1, $user2, $user3]);
 * ```
 *
 * @param callable $f
 * @param \Traversable|array $list
 * @return callable|array
 * @no-named-arguments
 */
function reject(callable $f, $list = null)
{
    $args = func_get_args();

    if (count($args) < 2) {
        return partial(reject, $f);
    }
    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    $aggregation = [];

    foreach ($list as $index => $element) {
        if (!call_user_func_array($f, [$element, $index, $list])) {
            $aggregation[$index] = $element;
        }
    }

    return $aggregation;
}

define('Basko\Functional\reject', __NAMESPACE__ . '\\reject', false);

/**
 * Returns true if the list contains the given value. If the third parameter is
 * true values will be compared in strict mode.
 *
 * ```php
 * contains('foo', ['foo', 'bar']); // true
 * contains('foo', 'foo and bar'); // true
 * ```
 *
 * @param mixed $needle
 * @param string|\Traversable|array $haystack
 * @return callable|bool
 * @no-named-arguments
 */
function contains($needle, $haystack = null)
{
    $args = func_get_args();

    if (count($args) < 2) {
        return partial(contains, $needle);
    }

    if (is_string($haystack)) {
        return '' === $needle || false !== strpos($haystack, $needle);
    }

    InvalidArgumentException::assertStringOrList($haystack, __FUNCTION__, 2);

    foreach ($haystack as $element) {
        if ($needle === $element) {
            return true;
        }
    }

    return false;
}

define('Basko\Functional\contains', __NAMESPACE__ . '\\contains', false);

/**
 * Creates a slice of `$list` with `$count` elements taken from the beginning. If the list has less than `$count`,
 * the whole list will be returned as an array.
 * For strings its works like `substr`.
 *
 * ```php
 * take(2, [1, 2, 3]); // [0 => 1, 1 => 2]
 * take(4, 'Slava'); // 'Slav'
 * ```
 *
 * @param \Traversable|array|string $list
 * @param int $count
 * @return callable|array|string
 * @no-named-arguments
 */
function take($count, $list = null)
{
    InvalidArgumentException::assertInteger($count, __FUNCTION__, 1);

    $args = func_get_args();

    if (count($args) < 2) {
        return partial(take, $count);
    }
    InvalidArgumentException::assertStringOrList($list, __FUNCTION__, 2);

    if (is_string($list)) {
        return substr($list, 0, $count);
    }

    return array_slice(
        is_array($list) ? $list : iterator_to_array($list),
        0,
        $count
    );
}

define('Basko\Functional\take', __NAMESPACE__ . '\\take', false);

/**
 * Creates a slice of `$list` with `$count` elements taken from the end. If the list has less than `$count`,
 * the whole list will be returned as an array.
 * For strings its works like `substr`.
 *
 * ```php
 * take_r(2, [1, 2, 3]); // [1 => 2, 2 => 3]
 * take_r(4, 'Slava'); // 'lava'
 * ```
 *
 * @param \Traversable|array|string $list
 * @param int $count
 * @return callable|array|string
 * @no-named-arguments
 */
function take_r($count, $list = null)
{
    InvalidArgumentException::assertInteger($count, __FUNCTION__, 1);

    $args = func_get_args();

    if (count($args) < 2) {
        return partial(take_r, $count);
    }
    InvalidArgumentException::assertStringOrList($list, __FUNCTION__, 2);

    if (is_string($list)) {
        return substr($list, -$count);
    }

    return array_slice(
        is_array($list) ? $list : iterator_to_array($list),
        0 - $count,
        $count,
        true
    );
}

define('Basko\Functional\take_r', __NAMESPACE__ . '\\take_r', false);

/**
 * Return N-th element of an array or string.
 * First element is first, but not zero. So you need to write `nth(1, ['one', 'two']); // one` if you want first item.
 *
 * ```php
 * nth(1, ['foo', 'bar', 'baz', 'qwe']); // 'foo'
 * nth(-1, ['foo', 'bar', 'baz', 'qwe']); // 'qwe'
 * nth(1, 'Slava'); // 'S'
 * nth(-2, 'Slava'); // 'v'
 * ```
 *
 * @param int $elementNumber
 * @param \Traversable|array|string $list
 * @return callable|mixed
 * @no-named-arguments
 */
function nth($elementNumber, $list = null)
{
    InvalidArgumentException::assertInteger($elementNumber, __FUNCTION__, 1);

    $args = func_get_args();

    if (count($args) < 2) {
        return partial(nth, $elementNumber);
    }
    InvalidArgumentException::assertStringOrList($list, __FUNCTION__, 2);

    if ($list instanceof Traversable) {
        $list = array_values(iterator_to_array($list));
    }

    if ($elementNumber < 0) {
        $elementNumber = len($list) - abs($elementNumber);
    } else {
        $elementNumber = $elementNumber - 1;
    }

    if (is_array($list)) {
        for ($i = 1; $i <= len($list); $i++) {
            if ($i == $elementNumber) {
                return $list[$elementNumber];
            }
        }
    }

    return isset($list[$elementNumber]) ? $list[$elementNumber] : null;
}

define('Basko\Functional\nth', __NAMESPACE__ . '\\nth', false);

/**
 * Groups a list by index returned by function.
 *
 * ```php
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
 * @param callable $f
 * @param \Traversable|array $list
 * @return callable|array
 * @no-named-arguments
 */
function group(callable $f, $list = null)
{
    $args = func_get_args();

    if (count($args) < 2) {
        return partial(group, $f);
    }
    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    $groups = [];

    foreach ($list as $index => $element) {
        $groupKey = call_user_func_array($f, [$element, $index, $list]);

        if (!isset($groups[$groupKey])) {
            $groups[$groupKey] = [];
        }

        $groups[$groupKey][$index] = $element;
    }

    return $groups;
}

define('Basko\Functional\group', __NAMESPACE__ . '\\group', false);

/**
 * Partitions a list by function predicate results. Returns an
 * array of partition arrays, one for each predicate, and one for
 * elements which don't pass any predicate. Elements are placed in the
 * partition for the first predicate they pass.
 *
 * Elements are not re-ordered and have the same index they had in the
 * original array.
 *
 * ```php
 * list($best, $good_students, $others) = partition(
 *      [
 *          compose(gte(9), prop('score')),
 *          compose(both(gt(6), lt(9)), prop('score'))
 *      ],
 *      $students
 * );
 * ```
 *
 * @param callable[] $functions
 * @param \Traversable|array $list
 * @return callable|array
 * @no-named-arguments
 */
function partition($functions, $list = null)
{
    InvalidArgumentException::assertListOfCallables($functions, __FUNCTION__, 1);

    $args = func_get_args();

    if (count($args) < 2) {
        return partial(partition, $functions);
    }
    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    $lastPartition = count($functions);
    $partitions = array_fill(0, $lastPartition + 1, []);

    foreach ($list as $index => $element) {
        foreach ($functions as $partition => $fn) {
            if (call_user_func_array($fn, [$element, $index, $list])) {
                $partitions[$partition][$index] = $element;
                continue 2;
            }
        }

        $partitions[$lastPartition][$index] = $element;
    }

    return $partitions;
}

define('Basko\Functional\partition', __NAMESPACE__ . '\\partition', false);

/**
 * Takes a nested combination of list and returns their contents as a single, flat list.
 *
 * ```php
 * flatten([1, 2, [3, 4], 5, [6, [7, 8, [9, [10, 11], 12]]]]); // [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
 * flatten([1 => 1, 'foo' => '2', 3 => '3', ['foo' => 5]]); // [1, "2", "3", 5]
 * ```
 *
 * @param \Traversable|array $list
 * @return array
 * @no-named-arguments
 */
function flatten($list)
{
    InvalidArgumentException::assertList($list, __FUNCTION__, 1);

    $result = [];
    foreach ($list as $value) {
        if (is_array($value) || $value instanceof Traversable) {
            $result = array_merge($result, flatten($value));
        } else {
            $result[] = $value;
        }
    }

    return $result;
}

define('Basko\Functional\flatten', __NAMESPACE__ . '\\flatten', false);

/**
 * Takes a nested combination of list and returns their contents as a single, flat list.
 * Keys concatenated by `.` and element index.
 *
 * ```php
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
 * @param \Traversable|array $list
 * @return array
 * @no-named-arguments
 */
function flatten_with_keys($list)
{
    $args = func_get_args();
    $prefix = (!array_key_exists(1, $args) || is_null($args[1])) ? '' : $args[1];

    InvalidArgumentException::assertList($list, __FUNCTION__, 1);

    $result = [];
    foreach ($list as $key => $value) {
        if (is_array($value) || $value instanceof Traversable) {
            $result = array_merge($result, flatten_with_keys($value, $prefix . $key . '.'));
        } else {
            $result[$prefix . $key] = $value;
        }
    }

    return $result;
}

define('Basko\Functional\flatten_with_keys', __NAMESPACE__ . '\\flatten_with_keys', false);

/**
 * Insert a given value between each element of a collection.
 * Indexes are not preserved.
 *
 * ```php
 * intersperse('a', ['b', 'n', 'n', 's']); // ['b', 'a', 'n', 'a', 'n', 'a', 's']
 * ```
 *
 * @param mixed $separator
 * @param \Traversable|array $list
 * @return callable|array
 * @no-named-arguments
 */
function intersperse($separator, $list = null)
{
    $args = func_get_args();

    if (count($args) < 2) {
        return partial(intersperse, $separator);
    }
    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    $aggregation = [];

    foreach ($list as $element) {
        $aggregation[] = $element;
        $aggregation[] = $separator;
    }

    array_pop($aggregation);

    return $aggregation;
}

define('Basko\Functional\intersperse', __NAMESPACE__ . '\\intersperse', false);

/**
 * Sorts a list with a user-defined function.
 *
 * ```php
 * sort(binary('strcmp'), ['cat', 'bear', 'aardvark'])); // [2 => 'aardvark', 1 => 'bear', 0 => 'cat']
 * ```
 *
 * @param callable $f
 * @param \Traversable|array $list
 * @return array|callable
 * @no-named-arguments
 */
function sort(callable $f, $list = null)
{
    $args = func_get_args();

    if (count($args) < 2) {
        return partial(sort, $f);
    }

    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    if ($list instanceof Traversable) {
        $array = iterator_to_array($list);
    } else {
        $array = $list;
    }

    uasort(
        $array,
        /**
         * @param mixed $left
         * @param mixed $right
         * @return int
         */
        function ($left, $right) use ($f, $list) {
            return call_user_func_array($f, [$left, $right, $list]);
        }
    );

    return $array;
}

define('Basko\Functional\sort', __NAMESPACE__ . '\\sort', false);

/**
 * Makes a comparator function out of a function that reports whether the first
 * element is less than the second.
 *
 * ```php
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
 * @param callable(T, T):bool $f
 * @return callable(T, T):int
 */
function comparator(callable $f)
{
    return function ($a, $b) use ($f) {
        return call_user_func_array($f, [$a, $b]) ? -1 : (call_user_func_array($f, [$b, $a]) ? 1 : 0);
    };
}

define('Basko\Functional\comparator', __NAMESPACE__ . '\\comparator', false);

/**
 * Makes an ascending comparator function out of a function that returns a value that can be compared with `<` and `>`.
 *
 * ```php
 * sort(ascend(prop('age')), [
 *      ['name' => 'Emma', 'age' => 70],
 *      ['name' => 'Peter', 'age' => 78],
 *      ['name' => 'Mikhail', 'age' => 62],
 * ]); // [['name' => 'Mikhail', 'age' => 62], ['name' => 'Emma', 'age' => 70], ['name' => 'Peter', 'age' => 78]]
 * ```
 *
 * @param callable $f
 * @param numeric $a
 * @param numeric $b
 * @return int|callable
 */
function ascend(callable $f, $a = null, $b = null)
{
    $args = func_get_args();

    if (count($args) === 1) {
        return partial(ascend, $f);
    } elseif (count($args) === 2) {
        return partial(ascend, $f, $a);
    }

    $aa = call_user_func_array($f, [$a]);
    $bb = call_user_func_array($f, [$b]);

    return $aa < $bb ? -1 : ($aa > $bb ? 1 : 0);
}

define('Basko\Functional\ascend', __NAMESPACE__ . '\\ascend', false);

/**
 * Makes a descending comparator function out of a function that returns a value that can be compared with `<` and `>`.
 *
 * ```php
 * sort(descend(prop('age')), [
 *      ['name' => 'Emma', 'age' => 70],
 *      ['name' => 'Peter', 'age' => 78],
 *      ['name' => 'Mikhail', 'age' => 62],
 * ]); // [['name' => 'Peter', 'age' => 78], ['name' => 'Emma', 'age' => 70], ['name' => 'Mikhail', 'age' => 62]]
 * ```
 *
 * @param callable $f
 * @param numeric $a
 * @param numeric $b
 * @return int|callable
 */
function descend(callable $f, $a = null, $b = null)
{
    $args = func_get_args();

    if (count($args) === 1) {
        return partial(descend, $f);
    } elseif (count($args) === 2) {
        return partial(descend, $f, $a);
    }

    $aa = call_user_func_array($f, [$a]);
    $bb = call_user_func_array($f, [$b]);

    return $aa > $bb ? -1 : ($aa < $bb ? 1 : 0);
}

define('Basko\Functional\descend', __NAMESPACE__ . '\\descend', false);

/**
 * Returns a new list containing only one copy of each element in the original list,
 * based upon the value returned by applying the supplied function to each list element.
 * Prefers the first item if the supplied function produces the same value on two items.
 *
 * ```php
 * uniq_by('abs', [-1, -5, 2, 10, 1, 2]); // [-1, -5, 2, 10]
 * ```
 *
 * @param callable $f
 * @param \Traversable|array $list
 * @return array|callable
 * @no-named-arguments
 */
function uniq_by(callable $f, $list = null)
{
    $args = func_get_args();

    if (count($args) < 2) {
        return partial(uniq_by, $f);
    }

    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    $_aggregation = [];
    $aggregation = [];

    foreach ($list as $element) {
        $appliedItem = call_user_func_array($f, [$element]);
        if (!in_array($appliedItem, $_aggregation, true)) {
            $_aggregation[] = $appliedItem;
            $aggregation[] = $element;
        }
    }

    return $aggregation;
}

define('Basko\Functional\uniq_by', __NAMESPACE__ . '\\uniq_by', false);

/**
 * Returns a new list containing only one copy of each element in the original list.
 *
 * ```php
 * uniq([1, 1, 2, 1]); // [1, 2]
 * uniq([1, '1']); // [1, '1']
 * ```
 *
 * @param \Traversable|array $list
 * @return array|callable
 * @no-named-arguments
 */
function uniq($list)
{
    InvalidArgumentException::assertList($list, __FUNCTION__, 1);

    return uniq_by(identity, $list);
}

define('Basko\Functional\uniq', __NAMESPACE__ . '\\uniq', false);
