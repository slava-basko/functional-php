<?php

namespace Basko\Functional\Functor;

/**
 * @template T
 */
abstract class Monad
{
    /**
     * @var T
     */
    protected $value;

    /**
     * @param T $value
     */
    protected function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @param callable(T):mixed $f
     * @return static
     */
    abstract public function map(callable $f);

    /**
     * @param callable(T):static $f
     * @return static
     */
    abstract public function flatMap(callable $f);

    /**
     * @return T
     */
    public function extract()
    {
        if ($this->value instanceof self) {
            return $this->value->extract();
        }

        return $this->value;
    }
}
