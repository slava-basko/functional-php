<?php

namespace Basko\Functional\Functor;

class Maybe extends Monad
{
    const of = "Basko\Functional\Functor\Maybe::of";

    public static function just($value)
    {
        return parent::of($value);
    }

    public static function nothing()
    {
        return parent::of(null);
    }

    public function map(callable $f)
    {
        if (!is_null($this->value)) {
            return parent::map($f);
        }

        return $this::nothing();
    }

    public function match(callable $just, callable $nothing)
    {
        return !is_null($this->value) ? $just($this->value) : $nothing();
    }
}
