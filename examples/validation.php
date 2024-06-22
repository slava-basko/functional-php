<?php

// php examples/validation.php

require_once __DIR__ . '/../vendor/autoload.php';

use Basko\Functional as f;

/**
 * @param $name
 * @param $str
 * @return \Basko\Functional\Functor\EitherWriter
 */
function isLongEnough($name, $str)
{
    return strlen($str) >= 8
        ? f\Functor\EitherWriter::right($str)
        : f\Functor\EitherWriter::left("Value of $name too short");
}

/**
 * @param $password
 * @return \Basko\Functional\Functor\EitherWriter
 */
function isPasswordStrongEnough($password)
{
    return preg_match('/[A-Z]/', $password)
        && preg_match('/[a-z]/', $password)
        && preg_match('/[0-9]/', $password)
        ? f\Functor\EitherWriter::right($password)
        : f\Functor\EitherWriter::left('Password is not strong enough');
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
 * @param array $data
 * @return \Basko\Functional\Functor\EitherWriter
 * @throws \Basko\Functional\Exception\TypeException
 */
function validateForm(array $data)
{
    return isLongEnough('password', $data['password'])
        ->flatMap(f\compose('isPasswordStrongEnough', f\prop_thunk('password', $data)))
        ->flatMap(f\compose(f\partial('isLongEnough', 'username'), f\prop_thunk('username', $data)))
        ->map(f\always($data));
}

// Validate data
$data1 = ['username' => 'Slava Basko', 'password' => 'Password1'];
$data1Str = var_export($data1, true);
echo "Case1: $data1Str\n";
validateForm($data1)->match('echoData', 'echoError');

echo "---\n";

$data2 = ['username' => 'Slav', 'password' => 'Password'];
$data2Str = var_export($data2, true);
echo "Case2: $data2Str\n";
validateForm($data2)->match('echoData', 'echoError');
