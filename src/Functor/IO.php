<?php

namespace Basko\Functional\Functor;

use Basko\Functional as f;
use Basko\Functional\Exception\TypeException;

class IO extends Monad
{
    const of = "Basko\Functional\Functor\IO::of";

    /**
     * Wraps unsafe IO function `$f` like: read file, DB fetch, HTTP requests, etc.
     * IMPORTANT: throw Exception in `$f` to clearly show error path.
     *
     * @param callable $f
     * @return \Basko\Functional\Functor\IO
     */
    public static function of(callable $f)
    {
        if ($f instanceof static) {
            return $f;
        }

        return new static($f);
    }

    /**
     * @param callable $f
     * @return \Basko\Functional\Functor\IO
     */
    public function map(callable $f)
    {
        return static::of(f\compose($f, $this->value));
    }

    /**
     * @param callable(mixed):\Basko\Functional\Functor\IO $f
     * @return \Basko\Functional\Functor\IO
     * @throws \Basko\Functional\Exception\TypeException
     */
    public function flatMap(callable $f)
    {
        $result = \call_user_func($f, $this->__invoke());

        TypeException::assertReturnType($result, static::class, __METHOD__);

        return $result;
    }

    /**
     * Runs IO
     *
     * @return mixed
     */
    public function __invoke()
    {
        return \call_user_func_array($this->value, \func_get_args());
    }
}
