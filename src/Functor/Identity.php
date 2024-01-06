<?php

namespace Basko\Functional\Functor;

use Basko\Functional\Exception\TypeException;
use Basko\Functional as f;

/**
 * @template-extends \Basko\Functional\Functor\Monad<mixed>
 */
class Identity extends Monad
{
    const of = "Basko\Functional\Functor\Identity::of";

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
        return static::of(\call_user_func($f, $this->value));
    }

    /**
     * @param callable(mixed):static $f
     * @return static
     * @throws \Basko\Functional\Exception\TypeException
     */
    public function flatMap(callable $f)
    {
        $result = \call_user_func($f, $this->value);

        TypeException::assertReturnType($result, static::class, __METHOD__);

        return $result;
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
        } elseif ($m == Constant::class) {
            return Constant::of($this->extract());
        } elseif ($m == IO::class) {
            return IO::of(f\always($this->extract()));
        }

        $this->cantTransformException($m);
    }
}
