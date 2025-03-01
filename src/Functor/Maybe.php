<?php

namespace Basko\Functional\Functor;

use Basko\Functional\Exception\TypeException;

/**
 * @template T
 * @extends \Basko\Functional\Functor\Monad<T>
 */
class Maybe extends Monad
{
    const of = "Basko\Functional\Functor\Maybe::of";

    const just = "Basko\Functional\Functor\Maybe::just";

    const nothing = "Basko\Functional\Functor\Maybe::nothing";

    /**
     * @param mixed $value
     * @return static
     */
    public static function of($value)
    {
        return new static($value);
    }

    /**
     * @param mixed $value
     * @return static
     */
    public static function just($value)
    {
        return static::of($value);
    }

    /**
     * @return static
     */
    public static function nothing()
    {
        return static::of(null);
    }

    /**
     * @inheritdoc
     */
    public function map(callable $f)
    {
        if (\is_null($this->value)) {
            return static::nothing();
        }

        return static::of(\call_user_func($f, $this->value));
    }

    /**
     * @inheritdoc
     */
    public function flatMap(callable $f)
    {
        if (\is_null($this->value)) {
            return static::nothing();
        }

        $result = \call_user_func($f, $this->value);

        TypeException::assertReturnType($result, static::class, __METHOD__);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function ap(Monad $m)
    {
        TypeException::assertReturnType($m, static::class, __METHOD__);

        if (\is_null($this->value)) {
            return static::nothing();
        }

        return $this->map($m->extract());
    }

    /**
     * @inheritdoc
     */
    public function transform($m)
    {
        $this->assertTransform($m);

        $value = $this->extract();

        if ($m === Either::class) {
            return $this->isJust()
                ? Either::right($value)
                : Either::left('Nothing');
        } elseif ($m === Optional::class) {
            return $this->isJust()
                ? Optional::just($value)
                : Optional::nothing();
        } elseif ($m === Constant::class) {
            return Constant::of($value);
        } elseif ($m === Identity::class) {
            return Identity::of($value);
        } elseif ($m === IO::class) {
            return IO::of(function () use ($value) {
                return $value;
            });
        } elseif ($m === Writer::class) {
            return Writer::of([], $value);
        } elseif ($m === EitherWriter::class) {
            return $this->isJust()
                ? EitherWriter::right($value)
                : EitherWriter::left('Nothing');
        }

        throw $this->cantTransformException($m);
    }

    /**
     * @param callable(T):void $just
     * @param callable():void $nothing
     * @return static
     */
    public function match(callable $just, callable $nothing)
    {
        if (!\is_null($this->value)) {
            \call_user_func($just, $this->extract());
        } else {
            \call_user_func($nothing);
        }

        return $this;
    }

    /**
     * Syntax sugar for more convenience when using in procedural style.
     *
     * @return bool
     */
    public function isJust()
    {
        return \is_null($this->value) === false;
    }

    /**
     * Syntax sugar for more convenience when using in procedural style.
     *
     * @return bool
     */
    public function isNothing()
    {
        return \is_null($this->value) === true;
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return $this->isJust() ? 'Just(' . \var_export($this->value, true) . ')' : 'Nothing';
    }
}
