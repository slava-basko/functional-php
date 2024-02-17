<?php

namespace Basko\Functional\Functor;

use Basko\Functional\Exception\TypeException;

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

        $value = $this->extract();

        if ($m == Maybe::class) {
            return $value === null ? Maybe::nothing() : Maybe::just($value);
        } elseif ($m == Either::class) {
            return Either::right($value);
        } elseif ($m == Optional::class) {
            return Optional::just($value);
        } elseif ($m == Constant::class) {
            return Constant::of($value);
        } elseif ($m == IO::class) {
            return IO::of(function () use ($value) {
                return $value;
            });
        } elseif ($m == Writer::class) {
            return Writer::of([], $value);
        }

        $this->cantTransformException($m);
    }
}
