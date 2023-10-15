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
