<?php

namespace Basko\Functional\Functor\Traits;

trait OfTrait
{
    /**
     * @param mixed $value
     * @return static
     */
    public static function of($value)
    {
        if ($value instanceof static) {
            return $value;
        }

        return new static($value);
    }
}
