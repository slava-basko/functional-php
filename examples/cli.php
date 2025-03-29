<?php

// php examples/cli.php
// All `f\functionName()` should be replaced by `use function functionName` in moder PHP versions

require_once __DIR__ . '/../vendor/autoload.php';

use Basko\Functional as f;

/**
 * @return \Basko\Functional\Functor\IO
 */
function askForName()
{
    return f\Functor\IO::of(function () {
        echo "Enter your name: ";
        return trim(fgets(STDIN));
    });
}

/**
 * @param string|null $name
 * @return \Basko\Functional\Functor\Either
 */
function validateName($name)
{
    return !empty($name)
        ? f\Functor\Either::right($name)
        : f\Functor\Either::left("Name cannot be empty.");
}

/**
 * @param string $name
 * @return string
 * @throws \Basko\Functional\Exception\TypeException
 */
function formatGreeting($name)
{
    return 'Hello, ' . f\type_string($name) . '!';
}

/**
 * @return \Basko\Functional\Functor\IO
 */
function outputValue()
{
    return f\Functor\IO::of(function ($value) {
        print($value . PHP_EOL);
    });
}

/**
 * @param string|null $message
 * @return \Basko\Functional\Functor\IO
 */
function outputError($message = 'Unknown error')
{
    return f\Functor\IO::of(function () use ($message) {
        fwrite(STDERR, "\e[1;37;41mError: $message\e[0m\n" . PHP_EOL);
    });
}

askForName()
    ->flatMap('validateName')
    ->map('formatGreeting')
    ->match(outputValue(), outputError());
