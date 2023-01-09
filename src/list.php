<?php

namespace Basko\Functional;

use Basko\Functional\Exception\InvalidArgumentException;
use Traversable;

/**
 * Returns a new list containing the contents of the given list, followed by the given element.
 *
 * @param $element
 * @param $list
 * @return array|callable
 * @no-named-arguments
 */
function append($element, $list = null)
{
    if (is_null($list)) {
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
 * @param $element
 * @param $list
 * @return array|callable
 * @no-named-arguments
 */
function prepend($element, $list = null)
{
    if (is_null($list)) {
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
 * @param string $property
 * @param \Traversable|array $list
 * @return callable|array
 * @no-named-arguments
 */
function pluck($property, $list = null)
{
    InvalidArgumentException::assertString($property, __FUNCTION__, 1);

    if (is_null($list)) {
        return partial(pluck, $property);
    }
    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    return map(prop($property), $list);
}

define('Basko\Functional\pluck', __NAMESPACE__ . '\\pluck', false);

/**
 * Looks through each element in the list, returning the first one.
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

define('Basko\Functional\first', __NAMESPACE__ . '\\first', false);

/**
 * Looks through each element in the list, returning the first one that passes a truthy test (function). The
 * function returns as soon as it finds an acceptable element, and doesn't traverse the entire list. Function
 * arguments will be element, index, list
 *
 * @param callable $f
 * @param \Traversable|array $list
 * @return callable|mixed
 * @no-named-arguments
 */
function head_by(callable $f, $list = null)
{
    if (is_null($list)) {
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
 * Returns all items from $list except first element (head). Preserves $list keys.
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
    if (is_null($list)) {
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
 * Opposite is Functional\reject(). Function arguments will be element, index, list
 *
 * @param callable $f
 * @param \Traversable|array $list
 * @return callable|array
 * @no-named-arguments
 */
function select(callable $f, $list = null)
{
    if (is_null($list)) {
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
 * The opposite of Functional\select(). Function arguments will be element, index, list
 *
 * @param callable $f
 * @param \Traversable|array $list
 * @return callable|array
 * @no-named-arguments
 */
function reject(callable $f, $list = null)
{
    if (is_null($list)) {
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
 * true values will be compared in strict mode
 *
 * @param mixed $needle
 * @param string|\Traversable|array $haystack
 * @return callable|bool
 * @no-named-arguments
 */
function contains($needle, $haystack = null)
{
    if (is_null($haystack)) {
        return partial(contains, $needle);
    }

    if (is_string($haystack)) {
        return '' === $needle || false !== strpos($haystack, $needle);
    }

    InvalidArgumentException::assertList($haystack, __FUNCTION__, 2);

    foreach ($haystack as $element) {
        if ($needle === $element) {
            return true;
        }
    }

    return false;
}

define('Basko\Functional\contains', __NAMESPACE__ . '\\contains', false);

/**
 * Creates a slice of $list with $count elements taken from the beginning. If the list has less than $count,
 * the whole list will be returned as an array.
 *
 * @param \Traversable|array $list
 * @param int $count
 *
 * @return callable|array
 * @no-named-arguments
 */
function take($count, $list = null)
{
    if (is_null($list)) {
        return partial(take, $count);
    }
    InvalidArgumentException::assertList($list, __FUNCTION__, 2);


    return array_slice(
        is_array($list) ? $list : iterator_to_array($list),
        0,
        $count
    );
}

define('Basko\Functional\take', __NAMESPACE__ . '\\take', false);

/**
 * Creates a slice of $list with $count elements taken from the end. If the list has less than $count,
 * the whole list will be returned as an array.
 *
 * @param \Traversable|array $list
 * @param int $count
 *
 * @return callable|array
 * @no-named-arguments
 */
function take_r($count, $list = null)
{
    if (is_null($list)) {
        return partial(take_r, $count);
    }
    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    return array_slice(
        is_array($list) ? $list : iterator_to_array($list),
        0 - $count,
        $count,
        true
    );
}

define('Basko\Functional\take_r', __NAMESPACE__ . '\\take_r', false);

/**
 * Groups a list by index returned by function.
 *
 * @param callable $f
 * @param \Traversable|array $list
 * @return callable|array
 * @no-named-arguments
 */
function group(callable $f, $list = null)
{
    if (is_null($list)) {
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
 * @param callable[] $functions
 * @param \Traversable|array $list
 * @return callable|array
 * @no-named-arguments
 */
function partition($functions, $list = null)
{
    InvalidArgumentException::assertListOfCallables($functions, __FUNCTION__, 1);

    if (is_null($list)) {
        return partial(partition, $functions);
    }
    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    $partition = 0;
    $partitions = array_fill(0, count($functions) + 1, []);

    foreach ($list as $index => $element) {
        foreach ($functions as $partition => $fn) {
            if (call_user_func_array($fn, [$element, $index, $list])) {
                $partitions[$partition][$index] = $element;
                continue 2;
            }
        }
        ++$partition;
        $partitions[$partition][$index] = $element;
    }

    return $partitions;
}

define('Basko\Functional\partition', __NAMESPACE__ . '\\partition', false);

/**
 * Takes a nested combination of list and returns their contents as a single, flat list.
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
        if (is_array($value)) {
            $result = array_merge($result, flatten($value));
        } else {
            $result[] = $value;
        }
    }

    return $result;
}

define('Basko\Functional\flatten', __NAMESPACE__ . '\\flatten', false);

/**
 * Insert a given value between each element of a collection.
 * Indexes are not preserved.
 *
 * @param mixed $separator
 * @param \Traversable|array $list
 * @return callable|array
 * @no-named-arguments
 */
function intersperse($separator, $list = null)
{
    if (is_null($list)) {
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
 * @param callable $f
 * @param \Traversable|array $list
 * @return array|callable
 * @no-named-arguments
 */
function sort(callable $f, $list = null)
{
    if (is_null($list)) {
        return partial(sort, $f);
    }

    InvalidArgumentException::assertList($list, __FUNCTION__, 2);

    if ($list instanceof Traversable) {
        $array = iterator_to_array($list);
    } else {
        $array = $list;
    }

    uasort($array, function ($left, $right) use ($f, $list) {
        return call_user_func_array($f, [$left, $right, $list]);
    });

    return $array;
}

define('Basko\Functional\sort', __NAMESPACE__ . '\\sort', false);

/**
 * Makes a comparator function out of a function that reports whether the first
 * element is less than the second.
 *
 * @param callable $f
 * @return callable
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
 * @param callable $f
 * @param $a
 * @param $b
 * @return int|callable
 */
function ascend(callable $f, $a = null, $b = null)
{
    if (is_null($a) && is_null($b)) {
        return partial(ascend, $f);
    } elseif (is_null($b)) {
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
 * @param callable $f
 * @param $a
 * @param $b
 * @return int|callable
 */
function descend(callable $f, $a = null, $b = null)
{
    if (is_null($a) && is_null($b)) {
        return partial(descend, $f);
    } elseif (is_null($b)) {
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
 * @param callable $f
 * @param \Traversable|array $list
 * @return array|callable
 * @no-named-arguments
 */
function uniq_by(callable $f, $list = null)
{
    if (is_null($list)) {
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
