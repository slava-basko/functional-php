<?php

namespace Basko\Functional\Functor;

use Basko\Functional\Functor\Traits\OfTrait;

class Identity extends Monad
{
    use OfTrait;

    const of = "Basko\Functional\Functor\Identity::of";

    public function map(callable $f)
    {
        return static::of($f($this->value));
    }
}
