<?php

namespace Basko\Functional\Functor\Traits;

trait OfTrait
{
    public static function of($value)
    {
        if ($value instanceof static) {
            return $value;
        }

        return new static($value);
    }
}