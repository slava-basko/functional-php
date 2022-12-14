<?php

namespace Basko\Functional\Functor;

use Basko\Functional\Functor\Traits\OfTrait;

class Maybe extends Monad
{
    use OfTrait;

    const of = "Basko\Functional\Functor\Maybe::of";

    const just = "Basko\Functional\Functor\Maybe::just";

    const nothing = "Basko\Functional\Functor\Maybe::nothing";

    public static function just($value)
    {
        return static::of($value);
    }

    public static function nothing()
    {
        return static::of(null);
    }

    public function map(callable $f)
    {
        if (!is_null($this->value)) {
            return static::just(call_user_func_array($f, [$this->value]));
        }

        return $this::nothing();
    }

    public function match(callable $just, callable $nothing)
    {
        if (!is_null($this->value)) {
            call_user_func_array($just, [$this->value]);
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
