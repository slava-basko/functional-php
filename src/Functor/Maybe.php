<?php

namespace Basko\Functional\Functor;

use Basko\Functional\Exception\TypeException;

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

        TypeException::assertReturnType($result, Monad::class, __METHOD__);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function ap(Monad $m)
    {
        TypeException::assertType($m, static::class, __METHOD__);

        if (\is_null($this->value)) {
            return static::nothing();
        }

        return $this->map($m->extract());
    }

    /**
     * @inheritdoc
     */
    public function flatAp(Monad $m)
    {
        TypeException::assertType($m, static::class, __METHOD__);

        if (\is_null($this->value)) {
            return static::nothing();
        }

        return $this->flatMap($m->extract());
    }

    /**
     * @param callable $just
     * @param callable $nothing
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
