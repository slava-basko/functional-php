### noop
Function that do nothing.

```php
noop(); // nothing happen
noop('some string'); // nothing happen
```

### identity
Return the parameter supplied to it.

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
N(); // null
```

### eq
Runs PHP comparison operator `==`.

```php
eq(1, 1); // true
eq(1, '1'); // true
eq(1, 2); // false
```

### identical
Runs PHP comparison operator `===`.

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

Also, this function useful as a debug in the `pipe`.

```
pipe(
     'strrev',
     tap('var_dump'),
     concat('Basko ')
)('avalS'); //string(5) "Slava"
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
`compose(f, g, h)` is the same as `f(g(h(x)))`.
Note: Lenses don't compose backwards https://www.reddit.com/r/haskell/comments/23x3f3/lenses_dont_compose_backwards/

```php
$powerPlus1 = compose(plus(1), power);
$powerPlus1(3); // 10
```

### pipe
Performs left to right function composition.
`pipe(f, g, h)` is the same as `h(g(f(x)))`.

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

$average = converge(div, ['array_sum', 'count']);
$average([1, 2, 3, 4]); // 2.5
```

### call
Alias for `call_user_func`.

### call_array
Alias for `call_user_func_array`.

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
     [partial_r(gte, 100), always('water boils')],
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

Note, that you cannot use curry on a flipped function.
`curry` uses reflection to get the number of function arguments,
but this is not possible on the function returned from flip. Instead, use `curry_n` on flipped functions.

```php
$mergeStrings = function ($head, $tail) {
     return $head . $tail;
};
$flippedMergeStrings = flipped($mergeStrings);
$flippedMergeStrings('two', 'one'); // 'onetwo'
```

### flip
Returns function which accepts two arguments in the reversed order.

```php
$gt9 = flip(gt)(9);
$gt9(10); // true
$gt9(7); // false
```

### on
Takes a binary function `$f`, and unary function `$g`, and two values. Applies `$g` to each value,
then applies the result of each to `$f`.
Also known as the P combinator.

```php
$containsInsensitive = on(contains, 'strtolower');
$containsInsensitive('o', 'FOO'); // true
```

### y
Accepts function `$f` that isn't recursive and returns function `$g` which is recursive.
Also known as the Y combinator.

```php
function factorial($n) {
     return ($n <= 1) ? 1 : $nfactorial($n - 1);
}

echo factorial(5); // 120, no problem here

$factorial = function ($n) {
     return ($n <= 1) ? 1 : $ncall_user_func(__FUNCTION__, $n - 1);
};

echo $factorial(5); // Exception will be thrown
```

You can't call anonymous function recursively. But you can use `y` to make it possible.
```
$factorial = y(function ($fact) {
     return function ($n) use ($fact) {
         return ($n <= 1) ? 1 : $n$fact($n - 1);
     };
});

echo $factorial(5); // 120
```

### both
Acts as the boolean `and` statement.

```php
both(T(), T()); // true
both(F(), T()); // false
$between6And9 = both(flip(gt)(6), flip(lt)(9));
$between6And9(7); // true
$between6And9(10); // false
```

### all_pass
Takes a list of predicates and returns a predicate that returns true for a given list of arguments
if every one of the provided predicates is satisfied by those arguments.

```php
$isQueen = pipe(prop('rank'), eq('Q'));
$isSpade = pipe(prop('suit'), eq('♠︎'));
$isQueenOfSpades = all_pass([$isQueen, $isSpade]);

$isQueenOfSpades(['rank' => 'Q', 'suit' => '♣︎']); // false
$isQueenOfSpades(['rank' => 'Q', 'suit' => '♠︎']); // true
```

### any_pass
Takes a list of predicates and returns a predicate that returns true for a given list of arguments
if at least one of the provided predicates is satisfied by those arguments.

```php
$isClub = pipe(prop('suit'), eq('♣'));
$isSpade = pipe(prop('suit'), eq('♠'));
$isBlackCard = any_pass([$isClub, $isSpade]);

$isBlackCard(['rank' => '10', 'suit' => '♣']); // true
$isBlackCard(['rank' => 'Q', 'suit' => '♠']); // true
$isBlackCard(['rank' => 'Q', 'suit' => '♦']); // false
```

### ap
Applies a list of functions to a list of values.

```php
ap([multiply(2), plus(3)], [1,2,3]); // [2, 4, 6, 4, 5, 6]
```

### lift_m
Lift a function so that it accepts `Monad` as parameters. Lifted function returns `Monad`.

### memoized
Create memoized versions of `$f` function.

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
$memoizedRandAndSalt = memoized($randAndSalt);
$memoizedRandAndSalt('x'); // 42x
$memoizedRandAndSalt('x'); // 42x
```

### count_args
Return number of function arguments.

```php
count_args('explode'); // 3
```

### curry_n
Return a version of the given function where the $count first arguments are curryied.

No check is made to verify that the given argument count is either too low or too high.
If you give a smaller number you will have an error when calling the given function. If
you give a higher number, arguments will simply be ignored.

### curry
Return a curried version of the given function. You can decide if you also
want to curry optional parameters or not.

```php
function add($a, $b, $c) {
     return $a + $b + $c;
};

$curryiedAdd = curry('add');
$curryiedAdd(1, 2, 3); // 6
$curryiedAdd(1)(2)(3); // 6
$curryiedAdd(1)(2, 3); // 6
$curryiedAdd(1, 2)(3); // 6
```

### thunkify
Creates a thunk out of a function. A thunk delays calculation until its result is needed,
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
Return function `$f` that will be called only with `abs($count)` arguments,
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

### map
Produces a new list of elements by mapping each element in list through a transformation function.
Function arguments will be `element`, `index`, `list`.

```php
map(plus(1), [1, 2, 3]); // [2, 3, 4]
```

### flat_map
`flat_map` works applying `$f` that returns a sequence for each element in a list,
and flattening the results into the resulting array.

`flat_map($data)` differs from `flatten(map($data))` because it only flattens one level of nesting,
whereas flatten will recursively flatten nested collections. Indexes will not preserve.

```php
$items = [
     [
         'id' => 1,
         'type' => 'train',
         'users' => [
             ['id' => 1, 'name' => 'Jimmy Page'],
             ['id' => 5, 'name' => 'Roy Harper'],
         ],
     ],
     [
         'id' => 421,
         'type' => 'hotel',
         'users' => [
             ['id' => 1, 'name' => 'Jimmy Page'],
             ['id' => 2, 'name' => 'Robert Plant'],
         ],
     ],
];

$result = flat_map(prop('users'), $items);

//$result is [
//    ['id' => 1, 'name' => 'Jimmy Page'],
//    ['id' => 5, 'name' => 'Roy Harper'],
//    ['id' => 1, 'name' => 'Jimmy Page'],
//    ['id' => 2, 'name' => 'Robert Plant'],
//];
```

### each
Calls `$f` on each element in list. Returns origin `$list`.
Function arguments will be `element`, `index`, `list`.

```php
each(unary('print_r'), [1, 2, 3]); // Print: 123
```

### fold
Applies a function to each element in the list and reduces it to a single value.

```php
fold(concat, '4', [5, 1]); // 451

function sc($a, $b)
{
     return "($a+$b)";
}

fold('sc', '0', range(1, 13)); // (((((((((((((0+1)+2)+3)+4)+5)+6)+7)+8)+9)+10)+11)+12)+13)
```

### fold_r
The same as `fold` but accumulator on the right.

```php
fold_r(concat, '4', [5, 1]); // 514

function sc($a, $b)
{
     return "($a+$b)";
}

fold_r('sc', '0', range(1, 13)); // (1+(2+(3+(4+(5+(6+(7+(8+(9+(10+(11+(12+(13+0)))))))))))))
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
arguments will be `element`, `index`, `list`

### tail
Returns all items from `$list` except first element (head). Preserves `$list` keys.

```php
tail([
     ['name' => 'jack', 'score' => 1],
     ['name' => 'mark', 'score' => 9],
     ['name' => 'john', 'score' => 1],
]); // [1 => ['name' => 'mark', 'score' => 9], 2 => ['name' => 'john', 'score' => 1]]
```

### tail_by
Returns all items from `$list` except first element (head) if `$f` returns true. Preserves `$list` keys.
Can be considered as `tail` + `select`.

```php
tail_by(f\compose(gt(8), prop('score')), [
     ['name' => 'jack', 'score' => 1],
     ['name' => 'mark', 'score' => 9],
     ['name' => 'john', 'score' => 1],
]); // [1 => ['name' => 'mark', 'score' => 9]]
```

### select
Looks through each element in the list, returning an array of all the elements that pass a test (function).
Opposite is `reject()`. Function arguments will be `element`, `index`, `list`.

```php
$activeUsers = select(invoker('isActive'), [$user1, $user2, $user3]);
```

### reject
Returns the elements in list without the elements that the test (function) passes.
The opposite of `select()`. Function arguments will be `element`, `index`, `list`.

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
Creates a slice of `$list` with `$count` elements taken from the beginning. If the list has less than `$count`,
the whole list will be returned as an array.
For strings its works like `substr`.

```php
take(2, [1, 2, 3]); // [0 => 1, 1 => 2]
take(4, 'Slava'); // 'Slav'
```

### take_r
Creates a slice of `$list` with `$count` elements taken from the end. If the list has less than `$count`,
the whole list will be returned as an array.
For strings its works like `substr`.

```php
take_r(2, [1, 2, 3]); // [1 => 2, 2 => 3]
take_r(4, 'Slava'); // 'lava'
```

### nth
Return N-th element of an array or string.
First element is first, but not zero. So you need to write `nth(1, ['one', 'two']); // one` if you want first item.
Consider `$elementNumber` as a position but not index.

```php
nth(1, ['foo', 'bar', 'baz', 'qwe']); // 'foo'
nth(-1, ['foo', 'bar', 'baz', 'qwe']); // 'qwe'
nth(1, 'Slava'); // 'S'
nth(-2, 'Slava'); // 'v'
```

### group
Groups a list by index returned by `$f` function.

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
         compose(partial_r(gte, 9), prop('score')),
         compose(both(partial_r(gte, 6), partial_r(lt, 9)), prop('score'))
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

### flatten_with_keys
Takes a nested combination of list and returns their contents as a single, flat list.
Keys concatenated by `.` and element index.

```php
flatten_with_keys([
 'title' => 'Some title',
 'body' => 'content',
 'comments' => [
     [
         'author' => 'user1',
         'body' => 'comment body 1'
     ],
     [
         'author' => 'user2',
         'body' => 'comment body 2'
     ]
 ]
]);

//  [
//      'title' => 'Some title',
//      'body' => 'content',
//      'comments.0.author' => 'user1',
//      'comments.0.body' => 'comment body 1',
//      'comments.1.author' => 'user2',
//      'comments.1.body' => 'comment body 2',
//  ]
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

### zip
Zips two or more sequences.

Note: This function is not curried because of no fixed arity.

```php
zip([1, 2], ['a', 'b']); // [[1, 'a'], [2, 'b']]
```

### zip_with
Zips two or more sequences with given function `$f`.

Note: `$f` signature is `callable(array $arg):mixed`.
As a result: `zip_with(plus, [1, 2], [3, 4])` equals to `plus([$arg1, $arg2])`.
But `zip_with(call(plus), [1, 2], [3, 4])` equals to `plus($arg1, $arg2)`.

```php
zip_with(call(plus), [1, 2], [3, 4]); // [4, 6]
```

### permute
Returns all possible permutations.

```php
permute(['a', 'b']); // [['a', 'b'], ['b', 'a']]
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

### plus
Perform `$a + $b`.

```php
plus(4, 2); // 6
```

### minus
Perform `$a - $b`.

```php
minus(4, 2); // 2
```

### div
Perform `$a / $b`.

```php
div(4, 2); // 2
```

### modulo
Modulo of two numbers.

```php
modulo(1089, 37)); // 16
```

### multiply
Perform `$a$b`.

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

### clamp
Restricts a value to be within a range.

```php
clamp(1, 10, -5); // 1
clamp(1, 10, 15); // 10
clamp(1, 10, 4); // 4
clamp('2023-01-01', '2023-11-22', '2012-11-22'); // 2023-01-01
clamp('2023-01-01', '2023-11-22', '2023-04-22'); // 2023-04-22

// Example:
$pagePortion = clamp(MIN_PORTION, MAX_PORTION, $_REQUEST['perPage']); // Safely use $pagePortion in your SQL query.
```

### cartesian_product
Cartesian product of sets.
X = {1, 2}
Y = {a, b}
Z = {A, B, C}
X × Y × Z = { (1, a, A), (2, a, A), (1, b, A), (2, b, A)
              (1, a, B), (2, a, B), (1, b, B), (2, b, B)
              (1, a, C), (2, a, C), (1, b, C), (2, b, C) }

Note: This function is not curried because of no fixed arity.

```php
$ranks = [2, 3, 4, 5, 6, 7, 8, 9, 10, 'Jack', 'Queen', 'King', 'Ace'];
$suits = ["Hearts", "Diamonds", "Clubs", "Spades"];

$cards = pipe(cartesian_product, map(join('')))($ranks, [' of '], $suits);
// [
//    '2 of Hearts',
//    '2 of Diamonds',
//    ...
//    'Ace of Clubs',
//    'Ace of Spades',
// ];
```

### partial
Returns new function which will behave like `$f` with
predefined left arguments passed to partial.

```php
$implode_coma = partial('implode', ',');
$implode_coma([1, 2]); // 1,2
```

### partial_r
Returns new partial function which will behave like `$f` with
predefined right arguments passed to rpartial.

```php
$implode12 = partial_r('implode', [1, 2]);
$implode12(','); // 1,2
```

### partial_p
Returns new partial function which will behave like `$f` with
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

### lens_prop_path
Returns a lens whose focus is the specified path.

```php
$data = [
     'a' => 1,
     'b' => [
         'c' => 2
     ],
];
$lens = lens_prop_path(['b', 'c']);
view($lens, $data); // 2
view($lens, set($lens, 4, $data)); // ['a' => 1, 'b' => ['c' => 4]]
view($lens, over($lens, multiply(2), $data)); // ['a' => 1, 'b' => ['c' => 4]]
```

### lens_element
Returns a lens whose focus is the specified `nth` element.

```php
view(lens_element(1), [10, 20, 30]); // 10
view(lens_element(-1), [10, 20, 30]); // 30
set(lens_element(1), 99, [10, 20, 30]); // [99, 20, 30]
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
Performs an `if/else` condition over a value using functions as statements.

```php
$ifFoo = if_else(eq('foo'), always('bar'), always('baz'));
$ifFoo('foo'); // 'bar'
$ifFoo('qux'); // 'baz'
```

### repeat
Creates a function that can be used to repeat the execution of `$f`.

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
Acts as multiple `prop`: array of keys in, array of values out. Preserves order.

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

### assoc_element
Same as `assoc`, but it allows to specify element by its number rather than named key.

```php
assoc_element(1, 999, [10, 20, 30]); // [999, 20, 30]
assoc_element(-1, 999, [10, 20, 30]); // [10, 20, 999]
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
Takes two arguments, `$fst` and `$snd`, and returns `[$fst, $snd]`.

```php
pair('foo', 'bar'); // ['foo', 'bar']
```

### either
A function wrapping calls to the functions in an `||` operation, returning the result of the first function
if it is truth-y and the result of the next function otherwise.
Note: Will return result of the last function if all fail.

```php
$value = either(prop('prop1'), prop('prop2'), prop('prop3'));
$value([
     'prop2' => 'some value'
]); // 'some value'
```

### either_strict
The same as `either`, but returning the result of the first function
if it is not NULL and the result of the next function otherwise.
Note: Will return NULL if all fail.

### quote
Quote given string.

```php
quote('foo'); // "foo"
map(quote, ['foo', 'bar']); // ['"foo"', '"bar"']
```

### safe_quote
Same as `quote`, but with `addslashes` before.

### only_keys
Returns an array only with the specified keys.

```php
only_keys(['bar', 'baz'], ['foo' => 1, 'bar' => 2, 'baz' => 3]); // ['bar' => 2, 'baz' => 3]
```

### omit_keys
Drops specified keys.

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

```php
map_elements('strtoupper', [1], ['foo' => 'val1', 'bar' => 'val2']); // ['foo' => 'VAL1', 'bar' => 'val2']
```

### find_missing_keys
Finds if a given array has all of the required keys set.

```php
find_missing_keys(
     ['login', 'email'],
     ['login' => 'admin']
); // ['email']
```

### cp
Creates copy of provided value. `clone` will be called for objects.
You can overwrite `clone` and provide your specific function, just define `CLONE_FUNCTION` constant.

```php
$obj = new \stdClass();  // object hash: 00000000000000030000000000000000
cp($obj);                // object hash: 00000000000000070000000000000000
```

### pick_random_value
Return random value from list.

```php
pick_random_value(['sword', 'gold', 'ring', 'jewel']); // 'gold'
```

### combine
Creates an associative array using a `$keyProp` as the path to build its keys,
and `$valueProp` as path to get the values.

```php
combine('alpha2', 'name', [
     [
         'name' => 'Netherlands',
         'alpha2' => 'NL',
         'alpha3' => 'NLD',
         'numeric' => '528',
     ],
     [
         'name' => 'Ukraine',
         'alpha2' => 'UA',
         'alpha3' => 'UKR',
         'numeric' => '804',
     ],
]); // ['NL' => 'Netherlands', 'UA' => 'Ukraine']
```

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
Creates instance of given class with arguments passed to `__construct` method.

```php
$user = construct_with_args(User::class, ['first_name' => 'Slava', 'last_name' => 'Basko']);
echo $user->first_name; // Slava
```

### flip_values
Swaps the values of keys `a` and `b`.

```php
flip_values('key1', 'key2', ['key1' => 'val1', 'key2' => 'val2']); // ['key1' => 'val2', 'key2' => 'val1']
```

### is_nth
Function that helps you determine every Nth iteration of a loop.

```php
$is10thIteration = is_nth(10);

for ($i = 1; $i <= 20; $i++) {
     if ($is10thIteration($i)) {
         // do something on each 10th iteration (when $i is 10 and 20 in this case)
     }
}
```

### publish
Publishes any private method.

```php
class Collection
{
     public function filterNumbers(array $collection) {
         return select([$this, 'isInt'], $collection); // This will throw an exception
     }

     private function isInt($n) {
         return is_int($n);
     }
}
```
The above will generate an error because `isInt` is a private method.

This will work.
```
public function filterNumbers(array $collection)
{
     return select(publish('isInt', $this), $collection);
}
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
```
$f = partial_p('str_replace', [
     1 => $search,
     2 => $replace,
     4 => &$count
]);
```

### str_replace_first
The same as `str_replace` but replace only first occurrence.

```php
str_replace_first('abc', '123', 'abcdef abcdef abcdef'); // "23def abcdef abcdef
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

### str_contains_any
Checks if any of the strings in an array `$needles` present in `$haystack` string.

```php
str_contains_any(['a', 'b', 'c'], 'abc'); // true
str_contains_any(['a', 'b', 'c'], 'a'); // true
str_contains_any(['a', 'b', 'c'], ''); // false
str_contains_any(['a', 'b', 'c'], 'defg'); // false
```

### str_contains_all
Checks if all of the strings in an array `$needles` present in `$haystack` string.
Note: Will return true if `$needles` is an empty array.

```php
str_contains_all(['a', 'b', 'c'], 'abc'); // true
str_contains_all(['a', 'b', 'c'], 'cba'); // true
str_contains_all(['a', 'b', 'c'], 'a'); // false
str_contains_all(['a', 'b', 'c'], ''); // false
str_contains_all([], 'abc'); // true
```

### str_surround
Surrounds a string with a prefix and suffix.

```php
str_surround('(', ')', 'abc'); // (abc)
str_surround('<strong>', '</strong>', 'abc'); // <strong>abc</strong>
```

### is_type_of
Validates that the value is instance of specific class.

```php
is_type_of(\User::class, new User()); // true
is_type_of(\User::class, new SomeClass()); // false
```

### type_of
Checks that the value is instance of specific class.

```php
type_of(\User::class, new User()); // User
type_of(\User::class, new SomeClass()); // TypeException: Could not convert "SomeClass" to type "User"
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
Object: method `__toString` will be called
Array: all values will be concatenated with comma.

```php
type_string('hello'); // 'hello'
type_string(123); // '123'
```

### type_non_empty_string
Checks and coerces value to `non-empty-string`.
Object: method `__toString` will be called
Array: all values will be concatenated with comma.

```php
type_non_empty_string('abc'); // 'abc'
type_non_empty_string([]); // TypeException: Could not convert "array" to type "non-empty-string"
```

### type_int
Checks and coerces value to `int`.

```php
type_int('123'); // 123
type_int('007'); // 7
type_int('1.0'); // 1
```

### type_positive_int
Checks and coerces value to `positive_int`.

```php
type_positive_int(2); // 2
type_positive_int('2'); // 2
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

### type_array_key
Checks and coerces value to valid array key that can either be an `int` or a `string`.

```php
type_array_key(1); // 1
type_array_key('some_key'); // some_key
```

### type_list
Checks and coerces list values to `$type[]`.

```php
type_list(type_int, [1, '2']); // [1, 2]
type_list(type_int, [1, 2.0]); // [1, 2]
type_list(type_of(SomeEntity::class), [$entity1, $entity2]); // [$entity1, $entity2]
```

### type_array
Checks and coerces array keys to `$keyType` and values to `$valueType`.

```php
type_array(type_array_key, type_int, ['one' => '1', 'two' => 2]); // ['one' => 1, 'two' => 2]
```

### type_shape
Checks array keys presence and coerces values to according types.
All `key => value` pair that not described will be removed.

```php
$parcelShape = type_shape([
     'description' => type_string,
     'value' => type_union(type_int, type_float),
     'dimensions' => type_shape([
         'width' => type_union(type_int, type_float),
         'height' => type_union(type_int, type_float),
     ]),
     'products' => type_list(type_shape([
         'description' => type_string,
         'qty' => type_int,
         'price' => type_union(type_int, type_float),
     ]))
]);

$parcelShape([
     'description' => 'some goods',
     'value' => 200,
     'dimensions' => [
         'width' => 0.1,
         'height' => 2.4,
     ],
     'products' => [
         [
             'description' => 'product 1',
             'qty' => 2,
             'price' => 50,
         ],
         [
             'description' => 'product 2',
             'qty' => 2,
             'price' => 50,
         ],
     ],
     'additional' => 'some additional element value that should not present in result'
]); // checked and coerced array will be returned and `additional` will be removed
```

### type_optional
Makes sense to use in `type_shape`.
```php
$typeUser = type_shape([
     'name' => type_string,
     'lastName' => type_string,
     'location' => type_optional(type_string),
]);

$typeUser(['name' => 'Slava', 'lastName' => 'Basko']);
// ['name' => 'Slava', 'lastName' => 'Basko']

$typeUser(['name' => 'Slava', 'lastName' => 'Basko', 'location' => 'Vancouver']);
// ['name' => 'Slava', 'lastName' => 'Basko', 'location' => 'Vancouver']

$typeUser(['name' => 'Slava', 'lastName' => 'Basko', 'location' => function() {}]); // TypeException
```

### write_file
Race conditions safe file write.

```php
$io = write_file(0666, '/path/to/file.txt', 'content');
$io(); // Content write into file at this moment.
```

### read_file
Read file contents.

```php
$io = read_file('/path/to/file.txt');
$content = $io(); // Content read from file at this moment.
```

### read_url


