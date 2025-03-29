<?php

// php -S localhost:8000 examples/http.php
// All `f\functionName()` should be replaced by `use function functionName` in moder PHP versions
// curl "http://localhost:8000?id=1"
// curl "http://localhost:8000?id=str"

require_once __DIR__ . '/../vendor/autoload.php';

use Basko\Functional as f;

/**
 * @return \Basko\Functional\Functor\IO
 */
function getParams()
{
    return f\Functor\IO::of(function () {
        return array_merge($_GET, $_POST);
    });
}

/**
 * @param $param
 * @return \Basko\Functional\Functor\Maybe
 */
function getParam($param)
{
    return f\Functor\Maybe::of(getParams()->map(f\prop($param))());
}

/**
 * @param $value
 * @return \Basko\Functional\Functor\Either
 */
function toUserId($value)
{
    try {
        return f\Functor\Either::right(f\type_positive_int($value));
    } catch (\Exception $e) {
        return f\Functor\Either::left($e->getMessage());
    }
}

/**
 * @param $id
 * @return \Basko\Functional\Functor\Maybe
 */
function getUserById($id)
{
    $users = [
        1 => ['name' => 'John', 'age' => 25],
        2 => ['name' => 'Slav', 'age' => 30],
        3 => ['age' => 99],
    ];

    return array_key_exists($id, $users) ? f\Functor\Maybe::just($users[$id]) : f\Functor\Maybe::nothing();
}

/**
 * @param string $name
 * @return string
 * @throws \Basko\Functional\Exception\TypeException
 */
function formatName($name)
{
    return 'Hello ' . f\type_string($name) . '!';
}

/**
 * @return \Basko\Functional\Functor\IO
 */
function outputValue()
{
    return f\Functor\IO::of(function ($value) {
        http_response_code(200);
        header('Content-Type: text/plain');
        echo (string)$value;
    });
}

/**
 * @param $generalMsg
 * @return \Basko\Functional\Functor\IO
 */
function outputError($generalMsg = null)
{
    return f\Functor\IO::of(function ($specificMsg = null) use ($generalMsg) {
        http_response_code(400);
        header('Content-Type: text/plain');
        echo $specificMsg ?: ($generalMsg ?: 'Unknown error');
    });
}

getParam('id')
    ->map('trim')
    ->flatMap('toUserId')
    ->flatMap('getUserById')
    ->map(f\prop('name'))
    ->map('formatName')
    ->match(outputValue(), outputError('Oops =('));
