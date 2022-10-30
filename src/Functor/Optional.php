<?php

namespace Basko\Functional\Functor;

class Optional extends Monad
{
    const of = "Basko\Functional\Functor\Optional::of";

    protected $hasValue = false;

    public static function of($hasValue, $value)
    {
        if ($value instanceof static) {
            return $value;
        }

        $m = new static($value);
        $m->hasValue = $hasValue;
        return $m;
    }

    public static function just($value)
    {
        return static::of(true, $value);
    }

    public static function nothing()
    {
        return static::of(false, null);
    }

    public function map(callable $f)
    {
        if ($this->hasValue) {
            return static::just($f($this->value));
        }

        return $this::nothing();
    }

    public function match(callable $just, callable $nothing)
    {
        return $this->hasValue ? $just($this->value) : $nothing();
    }

    public static function fromArrayKey($key, array $data)
    {
        if (!array_key_exists($key, $data)) {
            return static::nothing();
        }

        return static::just($data[$key]);
    }
}
