<?php

namespace Basko\Functional\Functor;

class Either extends Monad
{
    const of = "Basko\Functional\Functor\Either::of";

    const success = "Basko\Functional\Functor\Either::success";

    const failure = "Basko\Functional\Functor\Either::failure";

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

    public static function success($value)
    {
        return static::of(true, $value);
    }

    public static function failure($error)
    {
        return static::of(false, $error);
    }

    public function map(callable $f)
    {
        if (!$this->validValue) {
            return $this;
        }

        try {
            return static::success(call_user_func_array($f, [$this->value]));
        } catch (\Exception $exception) {
            return static::failure($exception->getMessage());
        }
    }

    public function match(callable $success, callable $failure)
    {
        return $this->validValue ? $success($this->value) : $failure($this->value);
    }
}