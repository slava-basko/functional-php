<?php

namespace Functional;

class Constant extends Monad
{
    const of = "Functional\Constant::of";

    public function map(callable $f)
    {
        return $this;
    }
}
