<?php

// php examples/common_description.php
// All `f\functionName()` should be replaced by `use function functionName` in moder PHP versions

require_once __DIR__ . '/../vendor/autoload.php';

use Basko\Functional as f;

$products = [
    [
        'description' => 't-shirt red',
        'qty' => 1,
        'price' => 20,
    ],
    [
        'description' => '',
        'qty' => 1,
        'price' => 1,
    ],
    [
        'description' => 'jeans ',
        'qty' => 1,
        'price' => 50,
    ],
    [
        'description' => '  ',
        'qty' => 1,
        'price' => 1,
    ],
    [
        'description' => 'shoes size 46 black leather',
        'qty' => 1,
        'price' => 230,
    ],
];

$commonDescription = f\pipe(
    f\pluck('description'),
    f\map(f\unary('trim')),
    f\select(f\unary('strlen')),
    f\join(', '),
    f\take(40),
    f\partial_r('trim', ', ')
)($products);

var_dump($commonDescription); // t-shirt red, jeans, shoes size 46 black
