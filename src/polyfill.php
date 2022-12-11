<?php

if (!function_exists('get_debug_type')) {
    /**
     * @param $value
     * @return string
     */
    function get_debug_type($value)
    {
        $gettype = \Basko\Functional\cond([
            [\Basko\Functional\identical(null), \Basko\Functional\always('null')],
            ['is_bool', \Basko\Functional\always('bool')],
            ['is_string', \Basko\Functional\always('string')],
            ['is_array', \Basko\Functional\always('array')],
            ['is_int', \Basko\Functional\always('int')],
            ['is_float', \Basko\Functional\always('float')],
            [\Basko\Functional\partial(\Basko\Functional\instance_of, \__PHP_Incomplete_Class::class), \Basko\Functional\always('__PHP_Incomplete_Class')],
            ['is_object', function($value) {
                $class = \get_class($value);

                if (false === strpos($class, '@')) {
                    return $class;
                }

                return (get_parent_class($class) ?: key(class_implements($class)) ?: 'class') . '@anonymous';
            }],
            [\Basko\Functional\T, function ($value) {
                if (null === $type = @get_resource_type($value)) {
                    return 'unknown';
                }

                if ('Unknown' === $type) {
                    $type = 'closed';
                }

                return "resource ($type)";
            }],
        ]);

        return $gettype($value);
    }
}