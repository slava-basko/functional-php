<?php

namespace Basko\Functional\Functor;

interface Type
{
    /**
     * Returns the type of the value.
     * Expected values: 'boolean', 'integer', 'double', 'string', 'array' or class name (e.g. Some::class).
     *
     * @return string|class-string
     */
    public static function type();
}
