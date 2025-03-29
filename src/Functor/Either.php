<?php

namespace Basko\Functional\Functor;

use Basko\Functional\Exception\TypeException;
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
     * @param bool $validValue
     * @param mixed $value
     * @return static
     */
    public static function of($validValue, $value)
    {
        $m = new static($value);
        $m->validValue = $validValue;

        return $m;
    }

    /**
     * Aka "success"
     *
     * @param mixed $value
     * @return static
     */
    public static function right($value)
    {
        return static::of(true, $value);
    }

    /**
     * Aka "failure"
     *
     * @param mixed $value
     * @return static
     */
    public static function left($value)
    {
        return static::of(false, $value);
    }

    /**
     * @inheritdoc
     */
    public function map(callable $f)
    {
        if (!$this->validValue) {
            return static::left($this->value);
        }

        try {
            return static::right(\call_user_func($f, $this->value));
        } catch (Exception $exception) {
            return static::left($exception->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function flatMap(callable $f)
    {
        if (!$this->validValue) {
            return static::left($this->value);
        }

        try {
            $result = \call_user_func($f, $this->value);
        } catch (Exception $exception) {
            $result = static::left($exception->getMessage());
        }

        TypeException::assertReturnType($result, Monad::class, __METHOD__);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function ap(Monad $m)
    {
        TypeException::assertType($m, static::class, __METHOD__);

        if (!$this->validValue) {
            return static::left($this->value);
        }

        try {
            return $this->map($m->extract());
        } catch (Exception $exception) {
            return static::left($exception->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function flatAp(Monad $m)
    {
        TypeException::assertType($m, static::class, __METHOD__);

        if (!$this->validValue) {
            return static::left($this->value);
        }

        try {
            return $this->flatMap($m->extract());
        } catch (Exception $exception) {
            return static::left($exception->getMessage());
        }
    }

    /**
     * @param callable $right
     * @param callable $left
     * @return static
     */
    public function match(callable $right, callable $left)
    {
        if ($this->validValue) {
            \call_user_func($right, $this->extract());
        } else {
            \call_user_func($left, $this->extract());
        }

        return $this;
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

    public function toString()
    {
        if ($this->isRight()) {
            return 'Right(' . \var_export($this->value, true) . ')';
        }

        return 'Left(' . \var_export($this->value, true) . ')';
    }
}
