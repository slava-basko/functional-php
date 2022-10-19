<?php

namespace Basko\Functional\Functor;

class Constant extends Monad
{
    const of = "Basko\Functional\Functor\Constant::of";

    public function map(callable $f)
    {
        return $this;
    }
}
