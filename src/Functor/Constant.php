<?php

namespace Basko\Functional\Functor;

use Basko\Functional\Functor\Traits\OfTrait;

class Constant extends Monad
{
    use OfTrait;

    const of = "Basko\Functional\Functor\Constant::of";

    /**
     * @param callable $f
     * @return \Basko\Functional\Functor\Constant
     */
    public function map(callable $f)
    {
        return $this;
    }

    /**
     * @param callable $f
     * @return \Basko\Functional\Functor\Constant
     */
    public function flatMap(callable $f)
    {
        return $this;
    }
}
