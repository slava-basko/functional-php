<?php

namespace Functional\Functor;

class Maybe extends Monad
{
    const of = "Functional\Functor\Maybe::of";

    public function map(callable $f)
    {
        if (!is_null($this->value)) {
            return parent::map($f);
        }

        return $this::of(null);
    }

    public function match(callable $just, callable $nothing)
    {
        return !is_null($this->value) ? $just($this->value) : $nothing();
    }
}
