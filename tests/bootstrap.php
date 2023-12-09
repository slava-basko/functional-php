<?php

## How to run tests
// PHP 5
// docker run -v `pwd`:/var/www --rm feitosa/php55-with-composer composer install
// docker run -v `pwd`:/var/www --rm feitosa/php55-with-composer vendor/bin/phpunit
// PHP 8
// docker run -v `pwd`:/var/www --rm composer:2.5.8 composer install -d /var/www/
// docker run -v `pwd`:/var/www --rm php:8.2-cli var/www/vendor/bin/phpunit /var/www/ -c /var/www/phpunit.xml.dist

require_once __DIR__ . '/../vendor/autoload.php'; // composer autoload

$custom_clone_flag = false;
function custom_clone($object)
{
    global $custom_clone_flag;

    $custom_clone_flag = true;

    return clone $object;
}

function float($str)
{
    return (bool)preg_match('/^\d+([\.,]\d+)?$/D', $str);
}
