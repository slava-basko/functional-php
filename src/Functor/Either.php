<?php

namespace Basko\Functional\Functor;

use Exception;

class Either extends Monad
{
    const of = "Basko\Functional\Functor\Either::of";

    const right = "Basko\Functional\Functor\Either::right";

    const left = "Basko\Functional\Functor\Either::left";

    protected $validValue = false;

    public static function of($validValue, $value)
    {
        if ($value instanceof static) {
            return $value;
        }

        $m = new static($value);
        $m->validValue = $validValue;

        return $m;
    }

    /**
     * Aka "success"
     *
     * @param $value
     * @return \Basko\Functional\Functor\Either
     */
    public static function right($value)
    {
        return static::of(true, $value);
    }

    /**
     * Aka "failure"
     *
     * @param $error
     * @return \Basko\Functional\Functor\Either
     */
    public static function left($error)
    {
        return static::of(false, $error);
    }

    public function map(callable $f)
    {
        if (!$this->validValue) {
            return $this;
        }

        try {
            return static::right(call_user_func_array($f, [$this->value]));
        } catch (Exception $exception) {
            return static::left($exception->getMessage());
        }
    }

    public function match(callable $right, callable $left)
    {
        if ($this->validValue) {
            call_user_func_array($right, [$this->value]);
        } else {
            call_user_func_array($left, [$this->value]);
        }
    }

    /**
     * Syntax sugar for more convenience when using in procedural style.
     *
     * @return bool
     */
    public function isRight()
    {
        return $this->validValue === true;
    }

    /**
     * Syntax sugar for more convenience when using in procedural style.
     *
     * @return bool
     */
    public function isLeft()
    {
        return $this->validValue === false;
    }
}
