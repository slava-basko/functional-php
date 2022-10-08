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
You can get an error like ```ArgumentCountError : strtoupper() expects exactly 1 argument, X given```.
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
1. Get fetch property 'description' from products.
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

## Influenced by
https://ramdajs.com \
https://github.com/lstrojny/functional-php \
https://github.com/ace411/bingo-functional \
https://github.com/ircmaxell/monad-php \
https://github.com/yuhanz/ramda-php

And many other libs/articles/etc.

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