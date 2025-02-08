<?php

namespace Basko\Functional\Functor;

use Basko\Functional\Exception\TypeException;

/**
 * @template T
 * @extends \Basko\Functional\Functor\Monad<T>
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
        return new static($value);
    }

    /**
     * @inheritdoc
     */
    public function map(callable $f)
    {
        $this->value = \call_user_func($f, $this->value);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function flatMap(callable $f)
    {
        $result = \call_user_func($f, $this->value);

        TypeException::assertReturnType($result, static::class, __METHOD__);

        return $result;
    }

    /**
     * @inheritdoc
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
        } elseif ($m == Constant::class) {
            return Constant::of($value);
        } elseif ($m == IO::class) {
            return IO::of(function () use ($value) {
                return $value;
            });
        } elseif ($m == Writer::class) {
            return Writer::of([], $value);
        } elseif ($m == EitherWriter::class) {
            return EitherWriter::right($value);
        }

        throw $this->cantTransformException($m);
    }
}
