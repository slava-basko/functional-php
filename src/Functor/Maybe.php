<?php

namespace Basko\Functional\Functor;

use Basko\Functional\Exception\TypeException;

/**
 * @template-extends \Basko\Functional\Functor\Monad<mixed>
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
        if ($value instanceof static) {
            return $value;
        }

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
     * @param callable $f
     * @return static
     */
    public function map(callable $f)
    {
        if (\is_null($this->value)) {
            return $this::nothing();
        }

        return static::just(\call_user_func($f, $this->value));
    }

    /**
     * @param callable(mixed):static $f
     * @return static
     * @throws \Basko\Functional\Exception\TypeException
     */
    public function flatMap(callable $f)
    {
        if (\is_null($this->value)) {
            return $this::nothing();
        }

        $result = \call_user_func($f, $this->value);

        TypeException::assertReturnType($result, static::class, __METHOD__);

        return $result;
    }

    public function transform($m)
    {
        $this->assertTransform($m);

        $value = $this->extract();

        if ($m == Either::class) {
            return $this->isJust()
                ? Either::right($value)
                : Either::left('Nothing');
        } elseif ($m == Optional::class) {
            return $this->isJust()
                ? Optional::just($value)
                : Optional::nothing();
        } elseif ($m == Constant::class) {
            return Constant::of($value);
        } elseif ($m == Identity::class) {
            return Identity::of($value);
        } elseif ($m == IO::class) {
            return IO::of(function () use ($value) {
                return $value;
            });
        } elseif ($m == Writer::class) {
            return Writer::of([], $value);
        }

        $this->cantTransformException($m);
    }

    /**
     * @param callable $just
     * @param callable $nothing
     * @return \Basko\Functional\Functor\Maybe
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

    public function __toString()
    {
        return $this->isJust() ? 'Just(' . \var_export($this->value, true) . ')' : 'Nothing';
    }
}
