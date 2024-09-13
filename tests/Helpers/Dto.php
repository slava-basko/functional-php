<?php

namespace Basko\FunctionalTest\Helpers;

class Dto {
    public $value1;
    public $value2;

    public function __construct($value1 = null, $value2 = null)
    {

        $this->value1 = $value1;
        $this->value2 = $value2;
    }
}