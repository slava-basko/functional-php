# Functional PHP
Collection of PHP functions that allows you to write code in a declarative way.

The purpose of this library is not to replace imperative and OOP. They can be combined, and I believe
they should be combined because any of these approaches is not a silver bullet.

## Examples

I will omit the theory about functional programming because you can find a lot of information about it yourself.
But I want to show you examples.

### Collection example
Let's imagine that you are using collection lib, and you want to upper all elements.
You need to write things like this:
```php
$collection = collect(['one']);
$collection->map(function ($value) {
    return strtoupper($value);
});
```
Because you can get an error like `ArgumentCountError : strtoupper() expects exactly 1 argument, X given` 
when you will write `$collection->map('strtoupper');`.
Only user defined functions does not throw an exception when called with more arguments. But you can do this:
```php
$collection = collect(['one']);
$collection->map(unary('strtoupper'));
```
Bam! You get less bloated code without `function`, `{`, `return`, `}`, `;`. `unary` is a higher-order function, it takes
function with any arity and return new function that accept only one argument.

That's what I mean when I talk about combining imperative/OOP and functional code.

One more example with the collection. We need to filter users by isActive method for example.
```php
$collection = collect([$user1, $user2, $user3]);
$collection->filter(function ($user) {
    return $user->isActive();
});

// VS

$collection = collect([$user1, $user2, $user3]);
$collection->filter(invoker('isActive'));
```

### Point-free example
Now let's consider the second example when we need to calculate qty of items in order.
```php
$products = [
    [
        'description' => 't-shirt',
        'qty' => 2,
        'value' => 20
    ],
    [
        'description' => 'jeans',
        'qty' => 1,
        'value' => 30
    ],
    [
        'description' => 'boots',
        'qty' => 1,
        'value' => 40
    ],
];

$imperativeTotalQty = 0;
foreach ($products as $product) {
    $imperativeTotalQty += $product['qty'];
}

// OR
$totalQty = compose(sum, pluck('qty'))($products);
```

You can read code `compose(sum, pluck('qty'))` like `sum of 'quantity' properties`.
Ok, I understand that this could be a bit odd for you. You get used to writing code differently.

What we also did, is that we created a "point free" function (tacit programming).
```php
function getTotalQty($products) {
    $totalQty = 0;
    foreach ($products as $product) {
        $totalQty += $product['qty'];
    }
    return $totalQty;
}

// VS

$getTotalQty = compose(sum, pluck('qty'));
```

In the first version we created regular function. We need to operate with the `$products`, `$product` 
and `$totalQty`. We tell the machine "how to" calculate qty.
> Hey, computer. Create $totalQty with the initial 0 value. 
> Now iterate through $products.
> Add the value of $product 'qty' property to the value of $totalQty variable.
> Return the value of $totalQty variable.

But the second version is point free. We tell "what we want", without details.
> Hey, computer. Give me a sum of 'qty' properties.

### Pipe and partial application
We have a $product[] and we need to create a common description from the 'description' property of each one.
So, here are the basic steps:
1. Fetch property 'description' from products.
2. Strip whitespace from the beginning and end of each value.
3. Remove empty elements.
4. Join elements with commas.
5. Cut generated descriptions up to 34 characters.
6. Trim the comma at the end if present.

The imperative way could be:
```php
$commonDescription = trim(substr(implode(', ', array_filter(array_map('trim', $descriptions), 'strlen')), 0, 34), ', ');
// OR
$commonDescription = trim(
    substr(
        implode(
            ', ', 
            array_filter(
                array_map('trim', $descriptions), 
                'strlen'
            )
        )
        , 0, 34
    ),
    ', '
);
```
Quite a big cognitive load. Let's try to reorder it and make it more readable.
```php
$descriptions = array_map('trim', $descriptions);
$descriptions = array_filter($descriptions, 'strlen');
$description = implode(', ', $descriptions);
$description = substr($description, 0, 34);
$commonDescription = trim($description, ', ');
```
Now it's more readable, but we need to mess with states.

The functional code could be like this:
```php
$commonDescription = pipe(
    pluck('description'),
    partial('array_map', 'trim'),
    partial_r('array_filter', 'strlen'),
    partial('implode', ', '),
    partial_r('substr', 0, 34),
    partial_r('trim', ', ')
)($products);
```
This is precisely what we need. It's in a natural order. No intermediate states.

## General

### Import functions
Add `use Functional as f;` on top of your PHP file or use `use function Functional\function_name`.
The last option is used in the docs, and it's the preferred way starting with PHP 5.6.

### Name convention
The `snake_case` is used to be closer to a PHP native functions.

### "Data last" principe
The data to be operated on is generally supplied last (last functions argument).
Functions is more convenient for currying in this way.

### Functions are curried by default
This allows us to be more efficient in building new functions from old ones simply by not supplying the final parameters.

---
The last two points together make it easy to build functions as sequences of simpler functions, 
each of which transforms the data and passes it along to the next.

## Documentation

Examples is cool, but now it's time for "boring" part.

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

### NULL
Always return `null`.
```php
NULL(); // null
```

### eq
Run PHP comparison operator `==`
```php
eq(1, 1); // true
eq(1, '1'); // true
eq(1, 2); // false
```

### identical
Run PHP comparison operator `===`
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

    return $fact($n - 1, $acc * $n);
});
$fact(10); // 3628800
```

### map
Produces a new list of elements by mapping each element in list through a transformation function.
```php
map(plus(1), [1, 2, 3]); // [2, 3, 4]
```

### ap
Applies a list of functions to a list of values.
```php
ap([multiply(2), plus(3)], [1,2,3]); // [2, 4, 6, 4, 5, 6]
```

### not
Logical negation of the given function.
```php
$notString = not('is_string');
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
Applies a function to each element in the list and reduces it to a single value. Accumulator on the left.
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
`compose(f, g, h)` is the same as `f(g(h(x)))`
```php
$powerPlus1 = compose(plus(1), power);
$powerPlus1(3); // 10
```

### pipe
Performs left to right function composition. `pipe(f, g, h)` is the same as `h(g(f(x)))`.
```php
$plus1AndPower = pipe(plus(1), power);
$plus1AndPower(3); // 16
```

### converge
Accepts a converging function and a list of branching functions and returns a new function.
The results of each branching function are passed as arguments to the converging function to produce the return value.
```php
function div($dividend, $divisor) {
    return $dividend / $divisor;
}

$average = converge('div', ['array_sum', 'count']);
$average([1, 2, 3, 4]); // 2.5
```

### apply_to
Create a function that will pass arguments to a given function.
```php
$fiveAndThree = apply_to([5, 3]);
$fiveAndThree(sum); // 8
```

### cond
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
```php
$mergeStrings = function ($head, $tail) {
    return $head . $tail;
};
$flippedMergeStrings = flipped($mergeStrings);
$flippedMergeStrings('two', 'one'); // 'onetwo'
```

### on
Takes a binary function f, and unary function g, and two values. 
Applies g to each value, then applies the result of each to f.
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

### partial
Returns new function which will behave like given but with predefined left arguments.
```php
$implode_coma = partial('implode', ',');
$implode_coma([1, 2]); // 1,2
```

### partial_r
The same as `partial` but with predefined right arguments.
```php
$implode12 = partial_r('implode', [1, 2]);
$implode12(','); // 1,2
```

### partial_p
The same as `partial` but with predefined positional arguments.
```php
$sub_abcdef_from = partial_p('substr', [
    1 => 'abcdef',
    3 => 2
]);
$sub_abcdef_from(0); // 'ab'
```

### curry
Return a curried version of the given function.
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
Return function that will be called only with `abs($count)` arguments, 
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
```php
$f = static function ($a = '', $b = '', $c = '') {
    return $a . $b . $c;
};
unary($f)(['one', 'two', 'three]); // one
```

### binary
Same as `unary` but function will accept exactly 2 parameter.
```php
$f = static function ($a = '', $b = '', $c = '') {
    return $a . $b . $c;
};
binary($f)(['one', 'two', 'three]); // onetwo
```

### memoized
Create memoized versions of  function.
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
```

### concat
Concatenates given arguments.
```php
concat('foo', 'bar'); // 'foobar'
```

### join
The same as native `implode` function.
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
Creates a function that can be used to repeat the execution of function.
```php
repeat(thunkify('print_r')('Hello'))(3); // Print 'Hello' 3 times
```

### try_catch
Takes two functions, a tryer and a catcher. The returned function evaluates the tryer. If it does not throw, 
it simply returns the result. If the tryer does throw, the returned function evaluates the catcher function 
and returns its result.
```php
try_catch(function () {
    throw new \Exception();
}, always('val'))(); // 'val'
```

### invoker
Returns a function that invokes method `$method` with arguments `$methodArguments` on the object.
```php
array_filter([$user1, $user2], invoker('isActive'));
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
prop(0, [99])(); // 99
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
A function wrapping calls to the functions in an || operation, returning the result of the first 
function if it is truth-y and the result of the next function otherwise.
```php
either(gt(10), is_even, 101); // true
$value = either(prop('prop1'), prop('prop2'), prop('prop3'));
$value([
    'prop2' => 'some value'
]); // 'some value'
```

### quote
Quote given string.
```php
quote('foo'); // "foo"
map(quote, ['foo', 'bar']); // ['"foo"', '"bar"']
```

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

### set
Returns the result of "setting" the portion of the given data structure focused by the given lens to the given value.
```php
$xLens = lens_prop('x');
set($xLens, 4, ['x' => 1, 'y' => 2]); // ['x' => 4, 'y' => 2]
set($xLens, 8, ['x' => 1, 'y' => 2]); // ['x' => 8, 'y' => 2]
```

### over
Returns the result of "setting" the portion of the given data structure 
focused by the given lens to the result of applying the given function to the focused value.
```php
$xLens = lens_prop('x');
over($xLens, plus(100), ['x' => 1, 'y' => 2]); // ['x' => 101, 'y' => 2]
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

### pluck
Extract a property from a list of objects.
```php
pluck('qty', [['qty' => 2], ['qty' => 1]]); // [2, 1]
```

### head
Returning the first element in the list.
```php
head([
    ['name' => 'jack', 'score' => 1],
    ['name' => 'mark', 'score' => 9],
    ['name' => 'john', 'score' => 1],
]); // ['name' => 'jack', 'score' => 1]
```

### tail
Returns all items from $list except first element (head). Preserves $list keys.
```php
tail([
    ['name' => 'jack', 'score' => 1],
    ['name' => 'mark', 'score' => 9],
    ['name' => 'john', 'score' => 1],
]); // [1 => ['name' => 'mark', 'score' => 9], 2 => ['name' => 'john', 'score' => 1]]
```

### select
Looks through each element in the list, returning an array of all the elements that pass a test (function).
```php
$activeUsers = select(invoker('isActive'), [$user1, $user2, $user3]);
```

### reject
Returns the elements in list without the elements that the test (function) passes.
```php
$inactiveUsers = reject(invoker('isActive'), [$user1, $user2, $user3]);
```

### contains
Returns true if the list contains the given value.
```php
contains('foo', ['foo', 'bar']); // true
contains('foo', 'foo and bar'); // true
```

### take
Creates a slice of list with N elements taken from the beginning.
```php
take(2, [1, 2, 3]); // [0 => 1, 1 => 2]
```

### take_r
Creates a slice of list with N elements taken from the end.
```php
take_r(2, [1, 2, 3]); // [1 => 2, 2 => 3]
```

### group
Groups a list by function.
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
Partitions a list by function predicate results.
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
```php
intersperse('a', ['b', 'n', 'n', 's']); // ['b', 'a', 'n', 'a', 'n', 'a', 's']
```

### sort
Sorts a list with a user-defined function.
```php
sort(binary('strcmp'), ['cat', 'bear', 'aardvark'])); // [2 => 'aardvark', 1 => 'bear', 0 => 'cat']
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
Perform $a * $b.
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
Increment value.
```php
inc(41); // 42
```

### dec
Decrement value.
```php
dec(43); // 42
```

### power
Power value.
```php
power(4); // 16
```

### median
Calculate median.
```php
median([2, 9, 7]); // 7
median([7, 2, 10, 9]); // 8
```

### liftm
Lift a function so that it accepts Monad as parameters. Lifted function returns Monad.

_Note, that you cannot use curry on a lifted function._
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

### pick_random_value
Return random value from list.
```php
pick_random_value(['sword', 'gold', 'ring', 'jewel']); // 'gold'
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
Makes an ascending comparator function out of a function that returns a value that can be compared with `<` and `>`.
```php
sort(descend(prop('age')), [
    ['name' => 'Emma', 'age' => 70],
    ['name' => 'Peter', 'age' => 78],
    ['name' => 'Mikhail', 'age' => 62],
]); // [['name' => 'Peter', 'age' => 78], ['name' => 'Emma', 'age' => 70], ['name' => 'Mikhail', 'age' => 62]]
```

### comparator
Makes a comparator function out of a function that reports whether the first element is less than the second.
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

### uniq_by
Returns a new list containing only one copy of each element in the original list, based upon the value 
returned by applying the supplied function to each list element. Prefers the first item if the supplied function 
produces the same value on two items.
```php
uniq_by('abs', [-1, -5, 2, 10, 1, 2]); // [-1, -5, 2, 10]
```

### uniq
Returns a new list containing only one copy of each element in the original list.
```php
uniq([1, 1, 2, 1]); // [1, 2]
uniq([1, '1']); // [1, '1']
```

## Optional
Almost the same as `Maybe`. But `Maybe` is more about technical layer, and `Optional` is about business cases.
Let's take CRUD operation as an example. Does a `null` `$description` mean "remove the description", 
or "skip setting the description"?
```php
class EditArticle {
    private function __construct(
        public readonly int $id,
        public readonly string|null $title,
        public readonly string|null $description,
    ) {}

    public static function fromPost(array $post): self
    {
        Assert::keyExists($post, 'id');
        Assert::positiveInt(prop('id', $post));
                
        return new self(
            prop('id', $post), 
            prop('title', $post), 
            prop('description', $post)
        );
    }
}
```
The usage side.
```php
class HandleEditArticle
{
    public function __construct(private readonly Articles $articles) {}
    
    public function __invoke(EditArticle $command): void {
        $article = $this->articles->get($command->id);
        
        if ($command->title !== null) {
            // update title only when provided
            $article->setTitle($command->title);
        }
        
        // Description always updating. Maybe we just forgot extract it from the payload?
        // Who is responsible for deciding "optional field" vs "remove description when not provided": the command,
        // or the command handler?
        // Is this a bug, or correct behavior?
        $article->setDescription($command->description);
        
        $this->articles->save($article);
    }
}
```

Now let's use `Optional`.
```php
class EditArticle {
    private function __construct(
        public readonly int $id,
        public readonly Optional $title,
        public readonly Optional $description,
    ) {}

    public static function fromPost(array $post): self
    {
        Assert::keyExists($post, 'id');
        Assert::positiveInt(prop('id', $post));
                
        return new self(
            prop('id', $post), 
            Optional::fromArrayKey('title', $post), 
            Optional::fromArrayKey('description', $post)
        );
    }
}
```

The handler.
```php
class HandleEditArticle
{
    public function __construct(private readonly Articles $articles) {}
    
    public function __invoke(EditArticle $command): void {
        $article = $this->articles->get($command->id);
        
        // Only called if a fields has a provided value.
        $command->title->match([$article, 'setTitle'], N);
        $command->description->match([$article, 'setDescription'], N);
        
        $this->articles->save($article);
    }
}
```
What we have here:
* Better clarity about the optional nature of specific fields
* No conditional logic (less static analysis and testing efforts)
* Strict behavior: field provided — will be updated, not provided — nothing happen

## Influenced by

https://ramdajs.com \
https://github.com/lstrojny/functional-php \
https://github.com/ace411/bingo-functional \
https://github.com/ircmaxell/monad-php \
https://github.com/yuhanz/ramda-php \
And many other libs/articles/etc.

Why just not use one from the list? 


Well, RamdaJS obviously not our choice because it is great JS lib but we are using PHP. 
lstrojny/functional-php good choice but functions are not curried by default and there is no "data last principle". 
ircmaxell/monad-php wonderful monad implementation in my opinion, but it's only monad with no additional functions. 
yuhanz/ramda-php abandoned RamdaJS port plus I don't like all these public static properties.



## How to run tests

PHP 5
```shell
docker run -v `pwd`:/var/www --rm feitosa/php55-with-composer composer install
docker run -v `pwd`:/var/www --rm feitosa/php55-with-composer vendor/bin/phpunit
```

PHP 8
```shell
docker run -v `pwd`:/var/www --rm composer:2.4.2 composer install -d /var/www/
docker run -v `pwd`:/var/www --rm php:8.1.11-cli var/www/vendor/bin/phpunit /var/www/ -c /var/www/phpunit.xml.dist
```