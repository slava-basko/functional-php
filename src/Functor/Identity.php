<?php

namespace Basko\Functional\Functor;

use Basko\Functional\Exception\TypeException;
use Basko\Functional\Functor\Traits\OfTrait;

class Identity extends Monad
{
    use OfTrait;

    const of = "Basko\Functional\Functor\Identity::of";

    /**
     * @param callable $f
     * @return \Basko\Functional\Functor\Identity
     */
    public function map(callable $f)
    {
        return static::of(call_user_func($f, $this->value));
    }

    /**
     * @param callable(mixed):\Basko\Functional\Functor\Identity $f
     * @return \Basko\Functional\Functor\Identity
     * @throws \Basko\Functional\Exception\TypeException
     */
    public function flatMap(callable $f)
    {
        $result = call_user_func($f, $this->value);

        TypeException::assertReturnType($result, static::class, __METHOD__);

        return $result;
    }
}
