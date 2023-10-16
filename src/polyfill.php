<?php

use Basko\Functional as f;

if (!function_exists('get_debug_type')) {
    /**
     * @param $value
     * @return string
     */
    function get_debug_type($value)
    {
        $gettype = f\cond([
            [f\identical(null), f\always('null')],
            ['is_bool', f\always('bool')],
            ['is_string', f\always('string')],
            ['is_array', f\always('array')],
            ['is_int', f\always('int')],
            ['is_float', f\always('float')],
            [f\is_type_of(__PHP_Incomplete_Class::class),
                f\always('__PHP_Incomplete_Class')],
            ['is_object', function ($value) {
                $class = get_class($value);

                if (false === strpos($class, '@')) {
                    return $class;
                }

                return (get_parent_class($class) ?: key(class_implements($class)) ?: 'class') . '@anonymous';
            }],
            [f\T, function ($value) {
                if (null === $type = @get_resource_type($value)) {
                    return 'unknown';
                }

                if ($type === 'Unknown') {
                    $type = 'closed';
                }

                return "resource ($type)";
            }],
        ]);

        return $gettype($value);
    }
}

if (!function_exists('ctype_digit')) {
    function convert_int_to_char_before_ctype($int, $function)
    {
        if (!is_int($int)) {
            return $int;
        }

        if ($int < -128 || $int > 255) {
            return (string)$int;
        }

        if (PHP_VERSION_ID >= 80100) {
            @trigger_error(
                $function . '(): Argument of type int will be interpreted as string in the future',
                E_USER_DEPRECATED
            );
        }

        if ($int < 0) {
            $int += 256;
        }

        return chr($int);
    }

    /**
     * @param $text
     * @return bool
     */
    function ctype_digit($text)
    {
        $text = convert_int_to_char_before_ctype($text, __FUNCTION__);

        return is_string($text) && $text !== '' && !preg_match('/[^0-9]/', $text);
    }
}
