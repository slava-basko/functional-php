<?php

// php examples/validation.php
// All `f\functionName()` should be replaced by `use function functionName` in moder PHP versions

require_once __DIR__ . '/../vendor/autoload.php';

use Basko\Functional as f;
use Basko\Functional\Functor\EitherWriter;

/**
 * Validates if a string is long enough
 *
 * @param string $name
 * @param string $str
 * @return \Basko\Functional\Functor\EitherWriter
 */
function isLongEnough($name, $str)
{
    return strlen($str) >= 8
        ? EitherWriter::right("Field '$name' has valid length. ", $str)
        : EitherWriter::left("Field '$name' validation failed. ", "Value of $name too short");
}

/**
 * Validates if a password is strong enough
 *
 * @param string $password
 * @return \Basko\Functional\Functor\EitherWriter
 */
function isPasswordStrongEnough($password)
{
    $hasUppercase = preg_match('/[A-Z]/', $password);
    $hasLowercase = preg_match('/[a-z]/', $password);
    $hasNumber = preg_match('/[0-9]/', $password);

    if ($hasUppercase && $hasLowercase && $hasNumber) {
        return EitherWriter::right("Password strength check passed. ", $password);
    }

    $errors = [];
    if (!$hasUppercase) $errors[] = "missing uppercase letter";
    if (!$hasLowercase) $errors[] = "missing lowercase letter";
    if (!$hasNumber) $errors[] = "missing number";

    $errorMsg = "Password strength check failed: " . implode(", ", $errors);
    return EitherWriter::left("Password validation failed. ", $errorMsg);
}

/**
 * Outputs successful data
 *
 * @param mixed $data
 * @param string $log
 */
function echoData($data, $log)
{
    $data = var_export($data, true);
    echo "Data: $data\n";
    echo "Validation Log: $log\n";
}

/**
 * Outputs error messages
 *
 * @param mixed $error
 * @param string $log
 */
function echoError($error, $log)
{
    $error = var_export($error, true);
    echo "Error: $error\n";
    echo "Validation Log: $log\n";
}

/**
 * Validates a specific field using a validator function
 *
 * @param string $field
 * @param callable $validator
 * @param array $object
 * @return \Basko\Functional\Functor\EitherWriter
 */
function validateField($field, callable $validator, $object)
{
    $log = "Validating field '$field'. ";
    return EitherWriter::right($log, $object[$field])
        ->flatMap(function ($value) use ($validator) {
            return $validator($value);
        })
        ->map(function () use ($object) {
            return $object;
        });
}

/**
 * Validates the entire form
 *
 * @param array $data
 * @return \Basko\Functional\Functor\EitherWriter
 */
function validateForm(array $data)
{
    // Start with a right EitherWriter containing our data and an initial log message
    return EitherWriter::right("Starting form validation. ", $data)
        ->flatMap(function ($data) {
            // Validate password length
            return validateField('password', function ($password) {
                return isLongEnough('password', $password);
            }, $data);
        })
        ->flatMap(function ($data) {
            // Validate password strength
            return validateField('password', 'isPasswordStrongEnough', $data);
        })
        ->flatMap(function ($data) {
            // Validate username length
            return validateField('username', function ($username) {
                return isLongEnough('username', $username);
            }, $data);
        })
        // Final success message if everything passed
        ->map(function ($data) {
            return $data;
        });
}

// Validate data - Case 1 (should pass)
$data1 = ['username' => 'Slava Basko', 'password' => 'Password1'];
$data1Str = var_export($data1, true);
echo "Case 1: $data1Str\n";
validateForm($data1)->match('echoData', 'echoError');

echo "\n---\n\n";

// Validate data - Case 2 (should fail)
$data2 = ['username' => 'Slava', 'password' => 'Password'];
$data2Str = var_export($data2, true);
echo "Case 2: $data2Str\n";
validateForm($data2)->match('echoData', 'echoError');

echo "\n---\n\n";

// Validate data - Case 3 (multiple failures)
$data3 = ['username' => 'Sl', 'password' => 'pass'];
$data3Str = var_export($data3, true);
echo "Case 3: $data3Str\n";
validateForm($data3)->match('echoData', 'echoError');
