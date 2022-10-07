# Functional PHP
Collection of php functions that allows you to write code in a declarative way.

The purpose of this library is not to replace imperative and OOP. They can be combined and I believe 
they should be combined.

## Examples
I will omit theory because you can find yourself a lot of information about functional programming.
But I want to show you an examples.

Let's imagine that you are using collection lib, and you want to upper all elements.
You need to write thing like this:
```php
$collection = collect(['one']);
$collection->map(function ($value) {
    return strtoupper($value);
});
```

Because you can get an error like ```ArgumentCountError : strtoupper() expects exactly 1 argument, X given```.
Only user defined functions does not throw an exception when called with more arguments. But you can do this:
```php
$collection = collect(['one']);
$collection->map(unary('strtoupper'));
```
Bam! You get less bloated code, without `function`, `{`, `return`, `}`, `;`.

That's what I mean when I talk about combining imperative/OOP and functional code.

Now let's consider second example when we need to calculate qty of items in order. 
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
Ok, I understand that this could be a bit odd for you. You get used to write code differently.

What we also did, is that we created "point free" function (tacit programming).
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
and `$totalQty` variables. We tell the machine "how to" calculate qty.
> Hey, computer. Create $totalQty with the initial 0 value. 
> Now iterate through $products.
> Add $product qty property to the $totalQty variable.
> Return the value of $totalQty variable.

But the second version is point free. We tell machine "what we want", without details.
> Hey, computer. Give me a sum of 'quantity' properties.


## Influenced by
https://ramdajs.com
https://github.com/lstrojny/functional-php
https://github.com/ace411/bingo-functional
https://https@github.com/ircmaxell/monad-php
https://github.com/yuhanz/ramda-php












docker run --rm -v $(pwd):/var/www/html -it xmlshopslav/php-5538-apache-custom:1.1 php bin/index.php
docker run --rm -v $(pwd):/var/www/html -it xmlshopslav/php-5538-apache-custom:1.1 composer install
docker run --rm -v $(pwd):/var/www/html -it xmlshopslav/php-5538-apache-custom:1.1 php -dxdebug.remote_host=docker.for.mac.localhost vendor/bin/phpunit -c /var/www/html/phpunit.xml.dist
docker run --rm -v $(pwd):/var/www/html -it xmlshopslav/php-5538-apache-custom:1.1 composer install

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