<?php

namespace Functional\Functor;

class Constant extends Monad
{
    const of = "Functional\Functor\Constant::of";

    public function map(callable $f)
    {
        return $this;
    }
}
