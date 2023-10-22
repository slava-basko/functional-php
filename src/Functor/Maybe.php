<?php

namespace Basko\Functional\Functor;

use Basko\Functional\Exception\TypeException;
use Basko\Functional\Functor\Traits\OfTrait;

class Maybe extends Monad
{
    use OfTrait;

    const of = "Basko\Functional\Functor\Maybe::of";

    const just = "Basko\Functional\Functor\Maybe::just";

    const nothing = "Basko\Functional\Functor\Maybe::nothing";

    /**
     * @param mixed $value
     * @return \Basko\Functional\Functor\Maybe
     */
    public static function just($value)
    {
        return static::of($value);
    }

    /**
     * @return \Basko\Functional\Functor\Maybe
     */
    public static function nothing()
    {
        return static::of(null);
    }

    /**
     * @param callable $f
     * @return \Basko\Functional\Functor\Maybe
     */
    public function map(callable $f)
    {
        if (is_null($this->value)) {
            return $this::nothing();
        }

        return static::just(call_user_func($f, $this->value));
    }

    /**
     * @param callable(mixed):\Basko\Functional\Functor\Maybe $f
     * @return \Basko\Functional\Functor\Maybe
     * @throws \Basko\Functional\Exception\TypeException
     */
    public function flatMap(callable $f)
    {
        if (is_null($this->value)) {
            return $this::nothing();
        }

        $result = call_user_func($f, $this->value);

        TypeException::assertReturnType($result, static::class, __METHOD__);

        return $result;
    }

    /**
     * @param callable $just
     * @param callable $nothing
     * @return void
     */
    public function match(callable $just, callable $nothing)
    {
        if (!is_null($this->value)) {
            call_user_func_array($just, [$this->extract()]);
        } else {
            call_user_func($nothing);
        }
    }

    /**
     * Syntax sugar for more convenience when using in procedural style.
     *
     * @return bool
     */
    public function isJust()
    {
        return is_null($this->value) === false;
    }

    /**
     * Syntax sugar for more convenience when using in procedural style.
     *
     * @return bool
     */
    public function isNothing()
    {
        return is_null($this->value) === true;
    }
}
