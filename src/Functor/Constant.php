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

    /**
     * @template M as object
     * @param class-string<M> $m
     * @return M
     */
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
        } elseif ($m == Identity::class) {
            return Identity::of($value);
        } elseif ($m == IO::class) {
            return IO::of(function () use ($value) {
                return $value;
            });
        } elseif ($m == Writer::class) {
            return Writer::of([], $value);
        } elseif ($m == EitherWriter::class) {
            return EitherWriter::right($value);
        }

        $this->cantTransformException($m);
    }
}
