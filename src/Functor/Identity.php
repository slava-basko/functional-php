<?php

namespace Basko\Functional\Functor;

use Basko\Functional\Exception\TypeException;

class Identity extends Monad
{
    const of = "Basko\Functional\Functor\Identity::of";

    /**
     * @param mixed $value
     * @return static
     */
    public static function of($value)
    {
        return new static($value);
    }

    /**
     * @inheritdoc
     */
    public function map(callable $f)
    {
        return static::of(\call_user_func($f, $this->value));
    }

    /**
     * @inheritdoc
     */
    public function flatMap(callable $f)
    {
        $result = \call_user_func($f, $this->value);

        TypeException::assertReturnType($result, Monad::class, __METHOD__);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function ap(Monad $m)
    {
        TypeException::assertType($m, static::class, __METHOD__);

        return $this->map($m->extract());
    }

    /**
     * @inheritdoc
     */
    public function flatAp(Monad $m)
    {
        TypeException::assertType($m, static::class, __METHOD__);

        return $this->flatMap($m->extract());
    }
}
