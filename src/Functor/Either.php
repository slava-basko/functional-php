<?php

namespace Basko\Functional\Functor;

use Exception;

class Either extends Monad
{
    const of = "Basko\Functional\Functor\Either::of";

    const right = "Basko\Functional\Functor\Either::right";

    const left = "Basko\Functional\Functor\Either::left";

    /**
     * @var bool
     */
    protected $validValue = false;

    /**
     * @param mixed $value
     */
    final protected function __construct($value)
    {
        parent::__construct($value);
    }

    /**
     * @param bool $validValue
     * @param mixed $value
     * @return \Basko\Functional\Functor\Either|static
     */
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
     * @param mixed $value
     * @return \Basko\Functional\Functor\Either
     */
    public static function right($value)
    {
        return static::of(true, $value);
    }

    /**
     * Aka "failure"
     *
     * @param mixed $error
     * @return \Basko\Functional\Functor\Either
     */
    public static function left($error)
    {
        return static::of(false, $error);
    }

    /**
     * @param callable $f
     * @return $this
     */
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

    /**
     * @param callable $right
     * @param callable $left
     * @return void
     */
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
