<?php

namespace Basko\Functional\Functor;

abstract class Monad
{
    protected $value;

    protected function __construct($value)
    {
        $this->value = $value;
    }

    public static function of($value)
    {
        if ($value instanceof static) {
            return $value;
        }

        return new static($value);
    }

    public function map(callable $f)
    {
        return $this::of($f($this->value));
    }

    public function extract()
    {
        if ($this->value instanceof self) {
            return $this->value->extract();
        }

        return $this->value;
    }
}
