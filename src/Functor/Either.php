<?php

namespace Basko\Functional\Functor;

class Either extends Monad
{
    const of = "Basko\Functional\Functor\Either::of";

    const right = "Basko\Functional\Functor\Either::right";

    const left = "Basko\Functional\Functor\Either::left";

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

    /**
     * Alias, syntax sugar, for self::right()
     *
     * @param $value
     * @return \Basko\Functional\Functor\Either
     */
    public static function success($value)
    {
        return static::right($value);
    }

    /**
     * Alias, syntax sugar, for self::left()
     *
     * @param $error
     * @return \Basko\Functional\Functor\Either
     */
    public static function failure($error)
    {
        return static::left($error);
    }

    public function map(callable $f)
    {
        if (!$this->validValue) {
            return $this;
        }

        try {
            return static::right(call_user_func_array($f, [$this->value]));
        } catch (\Exception $exception) {
            return static::left($exception->getMessage());
        }
    }

    public function match(callable $success, callable $failure)
    {
        if ($this->validValue) {
            return static::right(call_user_func_array($success, [$this->value]));
        } else {
            return static::left(call_user_func_array($failure, [$this->value]));
        }
    }
}
