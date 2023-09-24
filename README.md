# Functional PHP
Collection of PHP functions that allows you to write code in a declarative way.

## General

#### Name convention
The `snake_case` is used to be closer to a PHP native functions.

#### "Data last" principe
The data to be operated on is generally supplied last (last functions argument).
Functions is more convenient for currying in this way.

#### Functions are curried by default
This allows us to be more efficient in building new functions from old ones simply by not supplying the final parameter.

---
The last two points together make it easy to build functions as sequences of simpler functions,
each of which transforms the data and passes it along to the next.

## Docs
Here you can find available function.

[Functions list](docs/functions.md)

Other useful things.
* [Maybe](docs/maybe.md)
* [Either](docs/either.md)
* [Optional](docs/optional.md)

## OOP 🤝 FP
The purpose of this library is not to replace imperative and OOP. They can be combined, and I believe
they should be combined because any of these approaches is not a silver bullet.

I will omit the theory about functional programming because you can find a lot of information about it yourself.
But I want to show you examples.

#### Collection example
Let's imagine that you are using collection lib, and you want to upper all elements.
You need to write things like this:
```php
$collection = new Collection(['one']);
$collection->map(function ($value) {
    return strtoupper($value);
});
```
You can get an error like `ArgumentCountError : strtoupper() expects exactly 1 argument, X given` 
when you will write `$collection->map('strtoupper');`.
Only user defined functions does not throw an exception when called with more arguments. But you can do this:
```php
$collection = new Collection(['one']);
$collection->map(unary('strtoupper'));
```
Bam! You get less bloated code without `function`, `{`, `return`, `}`, `;`. Function `unary` is a higher-order function, 
it takes function with any arity and return new function that accept only one argument.

That's what I mean when I talk about combining imperative/OOP and functional code.

One more example with the collection. We need to filter users by `isActive` method for example.
```php
$collection = new Collection([$user1, $user2, $user3]);

$collection->filter(function ($user) {
    return $user->isActive();
});

// VS

$collection->filter(invoker('isActive'));
```

#### Point-free example
Now let's consider the second example when we need to calculate qty of items in order.
```php
$products = [
    [
        'description' => 't-shirt',
        'qty' => 2,
        'value' => 20
    ],
    [
        'description' => 'jeans ',
        'qty' => 1,
        'value' => 30
    ],
    [
        'description' => ' boots',
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

#### Pipe and partial application
We have a `$products[]` and we need to create a common description from the `description` property of each one.
So, here are the basic steps:
1. Fetch property 'description' from products.
2. Strip whitespace from the beginning and end of each value.
3. Remove empty elements.
4. Join elements with commas.
5. Cut generated descriptions up to 34 characters.
6. Trim the comma at the end if present.

The imperative way could be:
```php
$commonDescription = trim(substr(implode(', ', array_filter(array_map('trim', array_column($products, 'description')), 'strlen')), 0, 34), ', ');
// OR
$commonDescription = trim(
    substr(
        implode(
            ', ', 
            array_filter(
                array_map(
                    'trim', 
                    array_column($products, 'description')
                ), 
                'strlen'
            )
        )
        , 0, 34
    ),
    ', '
);
```
Quite big cognitive load 🤯. Let's try to reorder it and make it more readable.
```php
$descriptions = array_column($products, 'description');
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
    map(unary('trim')),
    select(unary('strlen')),
    join(', '),
    take(34),
    partial_r('trim', ', ')
)($products);
```
This is precisely what we need. It's in a natural order. No intermediate states.

#### What about some real-life example?
No problem, this project has a doc auto-generation script.
Written in an entirely point-free manner.

[Show me your doc_generator.php](internal/doc_generator.php)

No variable were harmed during script development (c).

## License
Use as you want. No liability or warranty from me. Can be considered as MIT.