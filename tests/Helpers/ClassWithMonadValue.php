<?php

namespace Basko\FunctionalTest\Helpers;

class ClassWithMonadValue
{
    /**
     * @var \Basko\Functional\Functor\Monad
     */
    private $name;

    public function __construct(\Basko\Functional\Functor\Monad $name)
    {
        $this->name = $name;
    }

    public function getData()
    {
        return $this->name;
    }

    public function doSomethingWithValue()
    {
        $this->name = $this->name->map('strtoupper');
        $this->name = $this->name->map([$this, 'rev']);
    }

    public function rev($value)
    {
        return strrev($value);
    }
}
