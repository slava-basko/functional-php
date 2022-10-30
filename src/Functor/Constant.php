<?php

namespace Basko\Functional\Functor;

use Basko\Functional\Functor\Traits\OfTrait;

class Constant extends Monad
{
    use OfTrait;

    const of = "Basko\Functional\Functor\Constant::of";

    public function map(callable $f)
    {
        return $this;
    }
}
