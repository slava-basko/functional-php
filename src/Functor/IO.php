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
     * @return static
     */
    public static function of(callable $f)
    {
        return new static($f);
    }

    /**
     * @param callable $f
     * @return static
     * @throws \Basko\Functional\Exception\TypeException
     */
    public function map(callable $f)
    {
        TypeException::assertNotSelfType($f, static::class, __METHOD__);

        return static::of(f\compose($f, $this->value));
    }

    /**
     * @inheritdoc
     */
    public function flatMap(callable $f)
    {
        $result = \call_user_func($f, $this->__invoke());

        TypeException::assertReturnType($result, Monad::class, __METHOD__);

        return $result;
    }

    /**
     * @param static $m
     * @return \Basko\Functional\Functor\Monad
     */
    public function ap(Monad $m)
    {
        return static::of(f\compose($m, $this->value));
    }

    /**
     * @param static $m
     * @return \Basko\Functional\Functor\Monad
     */
    public function flatAp(Monad $m)
    {
        return static::of(f\compose($m, $this->value));
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
