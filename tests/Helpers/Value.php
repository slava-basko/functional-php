<?php

namespace Basko\FunctionalTest\Helpers;

class Value
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function concatWith($str)
    {
        return $this->value . $str;
    }

    public function concatWith2($str, $str2)
    {
        return $this->value . $str . $str2;
    }

    public function __toString()
    {
        return (string)$this->value;
    }
}
