### noop
Function that do nothing.

### identity
Does nothing, return the parameter supplied to it.

```php
identity(1); // 1

$obj = new \stdClass;
identity($obj) === $obj; // true
```

### T
Always return `true`.

```php
T(); // true
```

### F
Always return `false`.

```php
F(); // false
```

### N
Always return `null`.

```php
NULL(); // null
```

### eq
Run PHP comparison operator `==`.

```php
eq(1, 1); // true
eq(1, '1'); // true
eq(1, 2); // false
```

### identical
Run PHP comparison operator `===`.

```php
identical(1, 1); // true
identical(1, '1'); // false
```

### lt
Returns true if the first argument is less than the second; false otherwise.

```php
lt(2, 1); // false
lt(2, 2); // false
lt(2, 3); // true
lt('a', 'z'); // true
lt('z', 'a'); // false
```

### lte
Returns true if the first argument is less than or equal to the second; false otherwise.

```php
lte(2, 1); // false
lte(2, 2); // true
lte(2, 3); // true
lte('a', 'z'); // true
lte('z', 'a'); // false
```

### gt
Returns true if the first argument is greater than the second; false otherwise.

```php
gt(2, 1); // true
gt(2, 2); // false
gt(2, 3); // false
gt('a', 'z'); // false
gt('z', 'a'); // true
```

### gte
Returns true if the first argument is greater than or equal to the second; false otherwise.

```php
gte(2, 1); // true
gte(2, 2); // true
gte(2, 3); // false
gte('a', 'z'); // false
gte('z', 'a'); // true
```

### tail_recursion
Decorates given function with tail recursion optimization using trampoline.

```php
$fact = tail_recursion(function ($n, $acc = 1) use (&$fact) {
     if ($n == 0) {
         return $acc;
     }

     return $fact($n - 1, $acc$n);
});
$fact(10); // 3628800
```

### map
Produces a new list of elements by mapping each element in list through a transformation function.
Function arguments will be element, index, list.

```php
map(plus(1), [1, 2, 3]); // [2, 3, 4]
```

### flat_map
`flat_map` works applying `$f` that returns a sequence for each element in a list,
and flattening the results into the resulting array.

flat_map(...) differs from flatten(map(...)) because it only flattens one level of nesting,
whereas flatten will recursively flatten nested collections. Indexes will not preserve.

### each
Calls `$f` on each element in list. Returns origin `$list`.
Function arguments will be element, index, list.

```php
each(unary('print_r'), [1, 2, 3]); // Print: 123
```

### not
Returns the `!` of its argument.

```php
not(true); // false
not(false); // true
not(0); // true
not(1); // false
```

### complement
Logical negation of the given function `$f`.
```php
$notString = complement('is_string');
$notString(1); // true
```

### tap
Call the given function with the given value, then return the value.

```php
$input = new \stdClass();
$input->property = 'foo';
tap(function ($o) {
     $o->property = 'bar';
}, $input);
$input->property; // 'foo'
```

### fold
Applies a function to each element in the list and reduces it to a single value.

```php
fold(concat, '4', [5, 1]); // 451
```

### fold_r
The same as `fold` but accumulator on the right.

```php
fold_r(concat, '4', [5, 1]); // 514
```

### always
Wrap value within a function, which will return it, without any modifications. Kinda constant function.

```php
$constA = always('a');
$constA(); // 'a'
$constA(); // 'a'
```

### compose
Returns new function which applies each given function to the result of another from right to left.
compose(f, g, h) is the same as f(g(h(x))).

```php
$powerPlus1 = compose(plus(1), power);
$powerPlus1(3); // 10
```

### pipe
Performs left to right function composition.
pipe(f, g, h) is the same as h(g(f(x))).

```php
$plus1AndPower = pipe(plus(1), power);
$plus1AndPower(3); // 16
```

### converge
Accepts a converging function and a list of branching functions and returns a new function.

The results of each branching function are passed as arguments
to the converging function to produce the return value.

```php
function div($dividend, $divisor) {
     return $dividend / $divisor;
}

$average = converge('div', ['array_sum', 'count']);
$average([1, 2, 3, 4]); // 2.5
```

### call


### apply_to
Create a function that will pass arguments to a given function.

```php
$fiveAndThree = apply_to([5, 3]);
$fiveAndThree(sum); // 8
```

### cond
Performs an operation checking for the given conditions.
Returns a new function that behaves like a match operator. Encapsulates `if/elseif,elseif, ...` logic.

```php
$cond = cond([
     [eq(0), always('water freezes')],
     [gte(100), always('water boils')],
     [T, function ($t) {
         return "nothing special happens at $t °C";
     }],
]);

$cond(0); // 'water freezes'
$cond(100); // 'water boils'
$cond(50) // 'nothing special happens at 50 °C'
```

### flipped
Returns function which accepts arguments in the reversed order.

Note, that you cannot use curry on a flipped function. curry uses reflection to get the number of function arguments,
but this is not possible on the function returned from flip. Instead, use curry_n on flipped functions.

```php
$mergeStrings = function ($head, $tail) {
     return $head . $tail;
};
$flippedMergeStrings = flipped($mergeStrings);
$flippedMergeStrings('two', 'one'); // 'onetwo'
```

### on
Takes a binary function f, and unary function g, and two values. Applies g to each value,
then applies the result of each to f.
Also known as the P combinator.

```php
$containsInsensitive = on(contains, 'strtolower');
$containsInsensitive('o', 'FOO'); // true
```

### both
Acts as the boolean `and` statement.

```php
both(T(), T()); // true
both(F(), T()); // false
$between6And9 = both(gt(6), lt(9));
$between6And9(7); // true
$between6And9(10); // false
```

### all_pass


### any_pass


### ap
Applies a list of functions to a list of values.

```php
ap([multiply(2), plus(3)], [1,2,3]); // [2, 4, 6, 4, 5, 6]
```

### lift_to
Lift a function so that it accepts Monad as parameters. Lifted function returns specified Monad type.

Note, that you cannot use curry on a lifted function.

### lift_m
Lift a function so that it accepts Maybe as parameters. Lifted function returns Maybe.

Note, that you cannot use curry on a lifted function.

```php
$plus = function ($a, $b) {
     if (!is_int($a) || !is_int($b)) {
         throw new \InvalidArgumentException('Params should INT');
     }

     return $a + $b;
};
$plus(3, Maybe::just(2)); // InvalidArgumentException: Params should INT

$plusm = liftm($plus);
$plusm(3, Maybe::just(2)); // Maybe::just(5)
```

### lift_e
Lift a function so that it accepts Either as parameters. Lifted function returns Either.

Note, that you cannot use curry on a lifted function.

### count_args
Return number of function arguments.

### curry_n
Return a version of the given function where the $count first arguments are curryied.

No check is made to verify that the given argument count is either too low or too high.
If you give a smaller number you will have an error when calling the given function. If
you give a higher number, arguments will simply be ignored.

### curry
Return a curried version of the given function. You can decide if you also
want to curry optional parameters or not.

```php
$add = function($a, $b, $c) {
     return $a + $b + $c;
};
$curryiedAdd = curry($add);
$addTen = $curryiedAdd(10);
$addEleven = $addTen(1);
$addEleven(4); // 15
```

### thunkify
Creates a thunk out of a function. A thunk delays a calculation until its result is needed,
providing lazy evaluation of arguments.

```php
$add = function($a, $b) {
     return $a + $b;
};
$curryiedAdd = thunkify($add);
$addTen = $curryiedAdd(10);
$eleven = $addTen(1);
$eleven(); // 11
```

### ary
Return function $f that will be called only with `abs($count)` arguments,
taken either from the left or right depending on the sign.

```php
$f = static function ($a = 0, $b = 0, $c = 0) {
     return $a + $b + $c;
};
ary($f, 2)([5, 5]); // 10
ary($f, 1)([5, 5]); // 5
ary($f, -1)([5, 6]); // 6
```

### unary
Wraps a function of any arity (including nullary) in a function that accepts exactly 1 parameter.
Any extraneous parameters will not be passed to the supplied function.

```php
$f = static function ($a = '', $b = '', $c = '') {
     return $a . $b . $c;
};
unary($f)(['one', 'two', 'three]); // one
```

### binary
Wraps a function of any arity (including nullary) in a function that accepts exactly 2 parameters.
Any extraneous parameters will not be passed to the supplied function.

```php
$f = static function ($a = '', $b = '', $c = '') {
     return $a . $b . $c;
};
binary($f)(['one', 'two', 'three]); // onetwo
```

### append
Returns a new list containing the contents of the given list, followed by the given element.

```php
append('three', ['one', 'two']); // ['one', 'two', 'three']
```

### prepend
Returns a new list with the given element at the front, followed by the contents of the list.

```php
prepend('three', ['one', 'two']); // ['three', 'one', 'two']
```

### pluck
Extract a property from a list of objects.

```php
pluck('qty', [['qty' => 2], ['qty' => 1]]); // [2, 1]
```

### head
Looks through each element in the list, returning the first one.

```php
head([
     ['name' => 'jack', 'score' => 1],
     ['name' => 'mark', 'score' => 9],
     ['name' => 'john', 'score' => 1],
]); // ['name' => 'jack', 'score' => 1]
```

### head_by
Looks through each element in the list, returning the first one that passes a truthy test (function). The
function returns as soon as it finds an acceptable element, and doesn't traverse the entire list. Function
arguments will be element, index, list

### tail
Returns all items from $list except first element (head). Preserves $list keys.

```php
tail([
     ['name' => 'jack', 'score' => 1],
     ['name' => 'mark', 'score' => 9],
     ['name' => 'john', 'score' => 1],
]); // [1 => ['name' => 'mark', 'score' => 9], 2 => ['name' => 'john', 'score' => 1]]
```

### tail_by


### select
Looks through each element in the list, returning an array of all the elements that pass a test (function).
Opposite is Functional\reject(). Function arguments will be element, index, list.

```php
$activeUsers = select(invoker('isActive'), [$user1, $user2, $user3]);
```

### reject
Returns the elements in list without the elements that the test (function) passes.
The opposite of Functional\select(). Function arguments will be element, index, list.

```php
$inactiveUsers = reject(invoker('isActive'), [$user1, $user2, $user3]);
```

### contains
Returns true if the list contains the given value. If the third parameter is
true values will be compared in strict mode.

```php
contains('foo', ['foo', 'bar']); // true
contains('foo', 'foo and bar'); // true
```

### take
Creates a slice of $list with $count elements taken from the beginning. If the list has less than $count,
the whole list will be returned as an array.
For strings its works like `substr`.

```php
take(2, [1, 2, 3]); // [0 => 1, 1 => 2]
take(4, 'Slava'); // 'Slav'
```

### take_r
Creates a slice of $list with $count elements taken from the end. If the list has less than $count,
the whole list will be returned as an array.
For strings its works like `substr`.

```php
take_r(2, [1, 2, 3]); // [1 => 2, 2 => 3]
take_r(4, 'Slava'); // 'lava'
```

### nth
Return N-th element of an array or string.
First element is first, but not zero. So you need to write `nth(1, ['one', 'two']); // one` if you want first item.

```php
nth(1, ['foo', 'bar', 'baz', 'qwe']); // 'foo'
nth(-1, ['foo', 'bar', 'baz', 'qwe']); // 'qwe'
nth(1, 'Slava'); // 'S'
nth(-2, 'Slava'); // 'v'
```

### group
Groups a list by index returned by function.

```php
group(prop('type'), [
     [
         'name' => 'john',
         'type' => 'admin'
     ],
     [
         'name' => 'mark',
         'type' => 'user'
     ],
     [
         'name' => 'bill',
         'type' => 'user'
     ],
     [
         'name' => 'jack',
         'type' => 'anonymous'
     ],
]); // ['admin' => [...], 'user' => [...], 'anonymous' => [...]]
```

### partition
Partitions a list by function predicate results. Returns an
array of partition arrays, one for each predicate, and one for
elements which don't pass any predicate. Elements are placed in the
partition for the first predicate they pass.

Elements are not re-ordered and have the same index they had in the
original array.

```php
list($best, $good_students, $others) = partition(
     [
         compose(gte(9), prop('score')),
         compose(both(gt(6), lt(9)), prop('score'))
     ],
     $students
);
```

### flatten
Takes a nested combination of list and returns their contents as a single, flat list.

```php
flatten([1, 2, [3, 4], 5, [6, [7, 8, [9, [10, 11], 12]]]]); // [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
flatten([1 => 1, 'foo' => '2', 3 => '3', ['foo' => 5]]); // [1, "2", "3", 5]
```

### intersperse
Insert a given value between each element of a collection.
Indexes are not preserved.

```php
intersperse('a', ['b', 'n', 'n', 's']); // ['b', 'a', 'n', 'a', 'n', 'a', 's']
```

### sort
Sorts a list with a user-defined function.

```php
sort(binary('strcmp'), ['cat', 'bear', 'aardvark'])); // [2 => 'aardvark', 1 => 'bear', 0 => 'cat']
```

### comparator
Makes a comparator function out of a function that reports whether the first
element is less than the second.

```php
$ar = [1, 1, 2, 3, 5, 8];
usort($ar, comparator(function ($a, $b) {
     return $a < $b;
})); // $ar = [1, 1, 2, 3, 5, 8]

sort(
     comparator(function ($a, $b) {
         return prop('age', $a) < prop('age', $b);
     }),
     [
         ['name' => 'Emma', 'age' => 70],
         ['name' => 'Peter', 'age' => 78],
         ['name' => 'Mikhail', 'age' => 62],
     ]
); // [['name' => 'Mikhail', 'age' => 62], ['name' => 'Emma', 'age' => 70], ['name' => 'Peter', 'age' => 78]]
```

### ascend
Makes an ascending comparator function out of a function that returns a value that can be compared with `<` and `>`.

```php
sort(ascend(prop('age')), [
     ['name' => 'Emma', 'age' => 70],
     ['name' => 'Peter', 'age' => 78],
     ['name' => 'Mikhail', 'age' => 62],
]); // [['name' => 'Mikhail', 'age' => 62], ['name' => 'Emma', 'age' => 70], ['name' => 'Peter', 'age' => 78]]
```

### descend
Makes a descending comparator function out of a function that returns a value that can be compared with `<` and `>`.

```php
sort(descend(prop('age')), [
     ['name' => 'Emma', 'age' => 70],
     ['name' => 'Peter', 'age' => 78],
     ['name' => 'Mikhail', 'age' => 62],
]); // [['name' => 'Peter', 'age' => 78], ['name' => 'Emma', 'age' => 70], ['name' => 'Mikhail', 'age' => 62]]
```

### uniq_by
Returns a new list containing only one copy of each element in the original list,
based upon the value returned by applying the supplied function to each list element.
Prefers the first item if the supplied function produces the same value on two items.

```php
uniq_by('abs', [-1, -5, 2, 10, 1, 2]); // [-1, -5, 2, 10]
```

### uniq
Returns a new list containing only one copy of each element in the original list.

```php
uniq([1, 1, 2, 1]); // [1, 2]
uniq([1, '1']); // [1, '1']
```

### is_even
Check if number is even.

```php
is_even(4); // true
is_even(3); // false
```

### is_odd
Check if number is odd.

```php
is_odd(5); // true
is_odd(2); // false
```

### plus
Perform $a + $b.

```php
plus(4, 2); // 6
```

### minus
Perform $a - $b.

```php
minus(4, 2); // 2
```

### div
Perform $a / $b.

```php
div(4, 2); // 2
```

### multiply
Perform $a$b.

```php
multiply(4, 2); // 8
```

### sum
Fold list with `plus`.

```php
sum([3, 2, 1]); // 6
```

### diff
Fold list with `minus`.

```php
diff([10, 2, 1]); // 7
```

### divide
Fold list with `div`.

```php
divide([20, 2, 2]); // 5
```

### product
Fold list with `multiply`.

```php
product([4, 2, 2]); // 16
```

### average
Calculate average value.

```php
average([1, 2, 3, 4, 5, 6, 7]); // 4
```

### inc
Increments its argument.

```php
inc(41); // 42
```

### dec
Decrements its argument.

```php
dec(43); // 42
```

### power
Power its argument.

```php
power(4); // 16
```

### median
Calculate median.

```php
median([2, 9, 7]); // 7
median([7, 2, 10, 9]); // 8
```

### partial
Returns new function which will behave like $f with
predefined left arguments passed to partial.

```php
$implode_coma = partial('implode', ',');
$implode_coma([1, 2]); // 1,2
```

### partial_r
Returns new partial function which will behave like $f with
predefined right arguments passed to rpartial.

```php
$implode12 = partial_r('implode', [1, 2]);
$implode12(','); // 1,2
```

### partial_p
Returns new partial function which will behave like $f with
predefined positional arguments passed to ppartial.

```php
$sub_abcdef_from = partial_p('substr', [
     1 => 'abcdef',
     3 => 2
]);
$sub_abcdef_from(0); // 'ab'
```

### lens
Returns a lens for the given getter and setter functions.
The getter "gets" the value of the focus; the setter "sets" the value of the focus.

```php
$xLens = lens(prop('x'), assoc('x'));
view($xLens, ['x' => 1, 'y' => 2]); // 1
set($xLens, 4, ['x' => 1, 'y' => 2]); // ['x' => 4, 'y' => 2]
over($xLens, dec, ['x' => 1, 'y' => 2]); // ['x' => 0, 'y' => 2]
```

### view
Returns a "view" of the given data structure, determined by the given lens.

```php
$xLens = lens_prop('x');
view($xLens, ['x' => 1, 'y' => 2]); // 1
view($xLens, ['x' => 4, 'y' => 2]); // 4
```

### over
Returns the result of "setting" the portion of the given data structure
focused by the given lens to the result of applying the given function to the focused value.

```php
$xLens = lens_prop('x');
over($xLens, plus(100), ['x' => 1, 'y' => 2]); // ['x' => 101, 'y' => 2]
```

### set
Returns the result of "setting" the portion of the given data structure focused by the given lens to the given value.

```php
$xLens = lens_prop('x');
set($xLens, 4, ['x' => 1, 'y' => 2]); // ['x' => 4, 'y' => 2]
set($xLens, 8, ['x' => 1, 'y' => 2]); // ['x' => 8, 'y' => 2]
```

### lens_prop
Returns a lens whose focus is the specified property.

```php
$xLens = lens_prop('x');
view($xLens, ['x' => 1, 'y' => 2]); // 1
set($xLens, 4, ['x' => 1, 'y' => 2]); // ['x' => 4, 'y' => 2]
over($xLens, dec, ['x' => 1, 'y' => 2]); // ['x' => 0, 'y' => 2]
```

### lens_path
Returns a lens whose focus is the specified path.

```php
$data = [
     'a' => 1,
     'b' => [
         'c' => 2
     ],
];
$lens = lens_path(['b', 'c']);
view($lens, $data); // 2
view($lens, set($lens, 4, $data)); // ['a' => 1, 'b' => ['c' => 4]]
view($lens, over($lens, multiply(2), $data)); // ['a' => 1, 'b' => ['c' => 4]]
```

### value_to_key
Internal function.

### memoized
Create memoized versions of $f function.

Note that memoization is safe for pure functions only. For a function to be
pure it should:
  1. Have no side effects
  2. Given the same arguments it should always return the same result

Memoizing an impure function will lead to all kinds of hard to debug issues.

In particular, the function to be memoized should never rely on a state of a
mutable object. Only immutable objects are safe.

```php
$randAndSalt = function ($salt) {
     return rand(1, 100) . $salt;
};
$memoizedRandAndSalt = f\memoized($randAndSalt);
$memoizedRandAndSalt('x'); // 42x
$memoizedRandAndSalt('x'); // 42x
```

### to_list
Returns arguments as a list.

```php
to_list(1, 2, 3); // [1, 2, 3]
to_list('1, 2, 3'); // [1, 2, 3]
```

### concat
Concatenates `$a` with `$b`.

```php
concat('foo', 'bar'); // 'foobar'
```

### concat_all
Concatenates all given arguments.

```php
concat('foo', 'bar', 'baz'); // 'foobarbaz'
```

### join
Returns a string made by inserting the separator between each element and concatenating all the elements
into a single string.

```php
join('|', [1, 2, 3]); // '1|2|3'
```

### if_else
Performs an if/else condition over a value using functions as statements.

```php
$ifFoo = if_else(eq('foo'), always('bar'), always('baz'));
$ifFoo('foo'); // 'bar'
$ifFoo('qux'); // 'baz'
```

### repeat
Creates a function that can be used to repeat the execution of $f.

```php
repeat(thunkify('print_r')('Hello'))(3); // Print 'Hello' 3 times
```

### try_catch
Takes two functions, a tryer and a catcher. The returned function evaluates the tryer. If it does not throw,
it simply returns the result. If the tryer does throw, the returned function evaluates the catcher function
and returns its result. For effective composition with this function, both the tryer and catcher functions
must return the same type of results.

```php
try_catch(function () {
     throw new \Exception();
}, always('val'))(); // 'val'
```

### invoker
Returns a function that invokes method `$method` with arguments `$methodArguments` on the object.

```php
array_filter([$user1, $user2], invoker('isActive')); // only active users
```

### len
Count length of string or number of elements in the array.

```php
len('foo'); // 3
len(['a', 'b']); // 2
```

### prop
Returns a function that when supplied an object returns the indicated property of that object, if it exists.

```php
prop(0, [99]); // 99
prop('x', ['x' => 100]); // 100
$object = new \stdClass();
$object->x = 101;
prop('x', $object); // 101
```

### prop_thunk
Thunkified version of `prop` function, for more easily composition with `either` for example.

```php
prop_thunk(0, [99])(); // 99
```

### prop_path
Nested version of `prop` function.

```php
prop_path(['b', 'c'], [
     'a' => 1,
     'b' => [
         'c' => 2
     ],
]); // 2
```

### props
Acts as multiple prop: array of keys in, array of values out. Preserves order.

```php
props(['c', 'a', 'b'], ['b' => 2, 'a' => 1]); // [null, 1, 2]
```

### assoc
Creates a shallow clone of a list with an overwritten value at a specified index.

```php
assoc('bar', 42, ['foo' => 'foo', 'bar' => 'bar']); // ['foo' => 'foo', 'bar' => 42]

assoc(
     'full_name',
     compose(join(' '), props(['first_name', 'last_name'])),
     [
         'first_name' => 'Slava',
         'last_name' => 'Basko'
     ]
); // ['first_name' => 'Slava', 'last_name' => 'Basko', 'full_name' => 'Slava Basko']
```

### assoc_path
Nested version of `assoc` function.

```php
assoc_path(['bar', 'baz'], 42, ['foo' => 'foo', 'bar' => ['baz' => 41]]); // ['foo' => 'foo', 'bar' => ['baz' => 42]]
```

### to_fn
Returns a function that invokes `$method` with arguments `$arguments` on the $object.

```php
to_fn($obj, 'someMethod', ['arg'])(); // Equal to $obj->someMethod('arg');
```

### pair
Takes two arguments, $fst and $snd, and returns [$fst, $snd].

```php
pair('foo', 'bar'); // ['foo', 'bar']
```

### either
A function wrapping calls to the functions in an `||` operation, returning the result of the first function
if it is truth-y and the result of the next function otherwise.

```php
either(gt(10), is_even, 101); // true
$value = either(prop('prop1'), prop('prop2'), prop('prop3'));
$value([
     'prop2' => 'some value'
]); // 'some value'
```

### either_strict
The same as `either`, but returning the result of the first function
if it is not NULL and the result of the next function otherwise.

### quote
Quote given string.

```php
quote('foo'); // "foo"
map(quote, ['foo', 'bar']); // ['"foo"', '"bar"']
```

### safe_quote
Same as `quote`, but with `addslashes` before.

### select_keys
Select the specified keys from the array.

```php
select_keys(['bar', 'baz'], ['foo' => 1, 'bar' => 2, 'baz' => 3]); // ['bar' => 2, 'baz' => 3]
```

### omit_keys
Returns an array with the specified keys omitted from the array.

```php
omit_keys(['baz'], ['foo' => 1, 'bar' => 2, 'baz' => 3]); // ['foo' => 1, 'bar' => 2]
```

### map_keys
Applies provided function to specified keys.

```php
map_keys('strtoupper', ['foo'], ['foo' => 'val1', 'bar' => 'val2']); // ['foo' => 'VAL1', 'bar' => 'val2']
```

### map_elements
Applies provided function to N-th elements of an array.
First element is first, but not zero (similar to `nth` function).

### find_missing_keys
Finds if a given array has all of the required keys set.

```php
find_missing_keys(
     ['login', 'email'],
     ['login' => 'admin']
); // ['email']
```

### copy
Creates copy of provided value. `clone` will be called for objects.

### pick_random_value
Return random value from list.

```php
pick_random_value(['sword', 'gold', 'ring', 'jewel']); // 'gold'
```

### combine
Creates an associative array using a `$keyProp` as the path to build its keys,
and optionally `$valueProp` as path to get the values.

### sequence_constant
Returns an infinite, traversable sequence of constant values.

### sequence_linear
Returns an infinite, traversable sequence that linearly grows by given amount.

### sequence_exponential
Returns an infinite, traversable sequence that exponentially grows by given percentage.

### no_delay
Returns an infinite, traversable sequence of 0.
This helper mostly to use with `retry`.

### retry
Retry a function until the number of retries are reached or the function does no longer throw an exception.

```php
retry(3, no_delay, [$db, 'connect']); // Runs `$db->connect()` 3 times without delay (if method throw exception)
retry(3, sequence_linear(1, 5), [$ftp, 'upload']); // Runs `$ftp->upload()` 3 times with a linear back-off
```

### construct
Creates instance of given class.

```php
construct('stdClass'); // object(stdClass)
```

### construct_with_args
Creates instance of given class with arguments passed to __construct method.

```php
$user = construct_with_args(User::class, ['first_name' => 'Slava', 'last_name' => 'Basko']);
echo $user->first_name; // Slava
```

### str_split
Alias of `explode`.

```php
str_split(' ', 'Hello World'); // ['Hello', 'World']
```

### str_split_on
Splits string on 2 parts by X position.

```php
str_split_on(2, 'UA1234567890'); // ['UA', '1234567890']
```

### str_replace
Alias of native `str_replace`.

```php
str_replace(' ', '', 'a b c d e f'); // abcdef
```

Use `partial_p` if you need $count argument:
```php
$f = partial_p('str_replace', [
     1 => $search,
     2 => $replace,
     4 => &$count
]);
```

### str_starts_with
Checks if `$string` starts with `$token`.

```php
str_starts_with('http://', 'http://gitbub.com'); // true
```

### str_ends_with
Checks if `$string` ends with `$token`.

```php
str_ends_with('.com', 'http://gitbub.com'); // true
```

### str_test
Checks if a string matches a regular expression.

```php
$is_numeric = str_test('/^[0-9.]+$/');
$is_numeric('123.43'); // true
$is_numeric('12a3.43'); // false
```

### str_pad_left
Alias of `str_pad`.

```php
str_pad_left('6', '0', '481'); // 000481
```

### str_pad_right
Alias of `str_pad`.

```php
str_pad_right('6', '0', '481'); // 481000
```

### instance_of
This function can't be automatically partialed because `$object` can be NULL and thant's OK.

```php
instance_of(stdClass::class, new stdClass()); // true
instance_of(User::class, new stdClass()); // false
```

### is_instance_of
Curryied version of `instance_of`.

```php
is_instance_of(stdClass::class)(new stdClass()); // true
is_instance_of(User::class)(new stdClass()); // false
```

### type_of
Return a function that checks `$value instanceof SomeClass.

```php
type_of(\User::class)(new User()); // User
type_of(\User::class)(new SomeClass()); // TypeException: Could not convert "SomeClass" to type "User"
```

### type_bool
Checks and coerces value to `bool`.

```php
type_bool(true); // true
type_bool(1); // true
type_bool('1'); // true
type_bool(false); // false
type_bool(0); // false
type_bool('0'); // false
type_bool('some-string'); // TypeException: Could not convert "string" to type "bool"
```

### type_string
Checks and coerces value to `string`.
Object: method __toString will be called
Array: all values will be concatenated with comma.

```php
type_string('hello'); // 'hello'
type_string(123); // '123'
```

### type_int
Checks and coerces value to `int`.

```php
type_int('123'); // 123
type_int('007'); // 7
type_int('1.0'); // 1
```

### type_float
Checks and coerces value to `float`.

```php
type_float(123); // 123.0
type_float('123'); // 123.0
```

### type_union
Union type.

```php
$t = type_union(type_int, type_float);
$t(1); // 1;
$t(1.00); // 1
$t('1'); // 1
```

### type_positive_int
Checks and coerces value to positive `int`.

