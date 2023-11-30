<?php

namespace Basko\Functional\Functor;

use Basko\Functional\Exception\TypeException;
use Exception;

/**
 * @template-extends \Basko\Functional\Functor\Monad<mixed>
 */
final class Either extends Monad
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
     * @return \Basko\Functional\Functor\Either
     */
    public static function of($validValue, $value)
    {
        if ($value instanceof Either) {
            return $value;
        }

        $m = new Either($value);
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
        return Either::of(true, $value);
    }

    /**
     * Aka "failure"
     *
     * @param mixed $value
     * @return \Basko\Functional\Functor\Either
     */
    public static function left($value)
    {
        return Either::of(false, $value);
    }

    /**
     * @param callable $f
     * @return \Basko\Functional\Functor\Either
     */
    public function map(callable $f)
    {
        if (!$this->validValue) {
            return $this;
        }

        try {
            return Either::right(\call_user_func($f, $this->value));
        } catch (Exception $exception) {
            return Either::left($exception->getMessage());
        }
    }

    /**
     * @param callable(mixed):static $f
     * @return static
     * @throws \Basko\Functional\Exception\TypeException
     */
    public function flatMap(callable $f)
    {
        if (!$this->validValue) {
            return $this;
        }

        try {
            $result = \call_user_func($f, $this->value);
        } catch (Exception $exception) {
            $result = Either::left($exception->getMessage());
        }

        TypeException::assertReturnType($result, Either::class, __METHOD__);

        return $result;
    }

    /**
     * @param callable $right
     * @param callable $left
     * @return void
     */
    public function match(callable $right, callable $left)
    {
        if ($this->validValue) {
            \call_user_func_array($right, [$this->extract()]);
        } else {
            \call_user_func_array($left, [$this->extract()]);
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
