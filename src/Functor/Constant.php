<?php

namespace Basko\Functional\Functor;

use Basko\Functional as f;

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

    public function transform($m)
    {
        $this->assertTransform($m);

        if ($m == Maybe::class) {
            return Maybe::just($this->extract());
        } elseif ($m == Either::class) {
            return Either::right($this->extract());
        } elseif ($m == Optional::class) {
            return Optional::just($this->extract());
        } elseif ($m == Identity::class) {
            return Identity::of($this->extract());
        } elseif ($m == IO::class) {
            return IO::of(f\always($this->extract()));
        }

        $this->cantTransformException($m);
    }
}
