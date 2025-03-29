<?php

namespace Basko\Functional\Functor;

class Constant extends Monad
{
    const of = "Basko\Functional\Functor\Constant::of";

    /**
     * @param mixed $value
     * @return static
     */
    public static function of($value)
    {
        return new static($value);
    }

    /**
     * @inheritdoc
     */
    public function map(callable $f)
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function flatMap(callable $f)
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function ap(Monad $m)
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function flatAp(Monad $m)
    {
        return $this;
    }
}
