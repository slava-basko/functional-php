<?php

// php examples/validation.php
// All `f\functionName()` should be replaced by `use function functionName` in moder PHP versions

require_once __DIR__ . '/../vendor/autoload.php';

use Basko\Functional as f;
use Basko\Functional\Functor\Either;

/**
 * @param $name
 * @param $str
 * @return \Basko\Functional\Functor\Either
 */
function isLongEnough($name, $str)
{
    return strlen($str) >= 8
        ? f\Functor\Either::right($str)
        : f\Functor\Either::left("Value of $name too short");
}

/**
 * @param $password
 * @return \Basko\Functional\Functor\Either
 */
function isPasswordStrongEnough($password)
{
    return preg_match('/[A-Z]/', $password)
        && preg_match('/[a-z]/', $password)
        && preg_match('/[0-9]/', $password)
        ? f\Functor\Either::right($password)
        : f\Functor\Either::left('Password is not strong enough');
}

function echoData($data)
{
    $data = var_export($data, true);
    echo "Data: $data\n";
}

function echoError($error)
{
    $error = var_export($error, true);
    echo "Error: $error\n";
}

/**
 * @param string $field
 * @param callable(mixed):\Basko\Functional\Functor\Monad $validator
 * @param array $object
 * @return mixed
 */
function validateField($field, callable $validator, $object)
{
    return $validator($object[$field])->flatMap(function () use ($object) {
        return f\Functor\Either::right($object);
    });
}

function validateForm(array $data)
{
    return f\Functor\Either::right($data)
        ->flatMap(function ($data) {
            return isLongEnough('password', $data['password'])->flatMap(function () use ($data) {
                return Either::right($data);
            });
        })
        ->flatMap(function ($data) {
            return isPasswordStrongEnough($data['password'])->flatMap(function () use ($data) {
                return Either::right($data);
            });
        })
        ->flatMap(function ($data) {
            return isLongEnough('username', $data['username'])->flatMap(function () use ($data) {
                return Either::right($data);
            });
        });

//    return f\Functor\Either::right($data)
//        ->flatMap(f\partial('validateField', 'password', f\partial('isLongEnough', 'password')))
//        ->flatMap(f\partial('validateField', 'password', 'isPasswordStrongEnough'))
//        ->flatMap(f\partial('validateField', 'username', f\partial('isLongEnough', 'username')));
}

// Validate data
$data1 = ['username' => 'Slava Basko', 'password' => 'Password1'];
$data1Str = var_export($data1, true);
echo "Case1: $data1Str\n";
validateForm($data1)->match('echoData', 'echoError');

echo "---\n";

$data2 = ['username' => 'Slava', 'password' => 'Password'];
$data2Str = var_export($data2, true);
echo "Case2: $data2Str\n";
validateForm($data2)->match('echoData', 'echoError');
