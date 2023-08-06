<?php

namespace Basko\Functional\Functor;

use Basko\Functional\Functor\Traits\OfTrait;

class Identity extends Monad
{
    use OfTrait;

    const of = "Basko\Functional\Functor\Identity::of";

    /**
     * @param mixed $value
     */
    final protected function __construct($value)
    {
        parent::__construct($value);
    }

    /**
     * @param callable $f
     * @return \Basko\Functional\Functor\Identity
     */
    public function map(callable $f)
    {
        return static::of(call_user_func_array($f, [$this->value]));
    }
}
