<?php

namespace Basko\Functional\Functor;

abstract class Monad
{
    protected $value;

    protected function __construct($value)
    {
        $this->value = $value;
    }

    abstract public function map(callable $f);

    public function extract()
    {
        if ($this->value instanceof self) {
            return $this->value->extract();
        }

        return $this->value;
    }
}
