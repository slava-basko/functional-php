<?php

namespace Basko\Functional\Functor;

abstract class Monad
{
    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param mixed $value
     */
    protected function __construct($value)
    {
        $this->value = $value;
    }

    abstract public function map(callable $f);

    abstract public function flatMap(callable $f);

    /**
     * @return mixed
     */
    public function extract()
    {
        if ($this->value instanceof self) {
            return $this->value->extract();
        }

        return $this->value;
    }
}
