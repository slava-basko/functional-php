<?php

namespace Functional;

abstract class Monad
{
    protected $value;

    protected function __construct($value)
    {
        $this->value = $value;
    }

    public static function of($value)
    {
        if ($value instanceof static) {
            return $value;
        }

        return new static($value);
    }

    public function map(callable $f)
    {
        return $this::of($f($this->value));
    }

    public function extract()
    {
        if ($this->value instanceof self) {
            return $this->value->extract();
        }

        return $this->value;
    }
}

class Constant extends Monad
{
    const of = "Functional\Constant::of";

    public function map(callable $f)
    {
        return $this;
    }
}

class Identity extends Monad
{
    const of = "Functional\Identity::of";
}

class Maybe extends Monad
{
    const of = "Functional\Maybe::of";

    public function map(callable $f)
    {
        if (!is_null($this->value)) {
            return parent::map($f);
        }

        return $this::of(null);
    }

    public function match(callable $just, callable $nothing)
    {
        return !is_null($this->value) ? $just($this->value) : $nothing();
    }
}