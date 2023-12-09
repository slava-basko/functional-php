<?php

namespace Basko\Functional\Functor;

/**
 * @template-extends \Basko\Functional\Functor\Monad<mixed>
 */
class Constant extends Monad
{
    const of = "Basko\Functional\Functor\Constant::of";

    /**
     * @param mixed $value
     * @return static
     */
    public static function of($value)
    {
        if ($value instanceof static) {
            return $value;
        }

        return new static($value);
    }

    /**
     * @param callable $f
     * @return static
     */
    public function map(callable $f)
    {
        return $this;
    }

    /**
     * @param callable $f
     * @return static
     */
    public function flatMap(callable $f)
    {
        return $this;
    }
}
