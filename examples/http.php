<?php

// php -S localhost:8000 examples/http.php
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
    return getParams()->map(f\prop($param))->transform(f\Functor\Maybe::class);
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
 * @return \Basko\Functional\Functor\IO
 */
function outputValue()
{
    return f\Functor\IO::of(function ($value) {
        http_response_code(200);
        header('Content-Type: text/plain');
        echo (string)$value;
        exit;
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
        exit;
    });
}

getParam('id')
    ->map('trim')
    ->transform(f\Functor\Either::class)
    ->flatMap('toUserId')
    ->match(f\identity, outputError())
    ->transform(f\Functor\Maybe::class)
    ->flatMap('getUserById')
    ->map(f\prop('name'))
    ->match(outputValue(), outputError('Oops =('));
