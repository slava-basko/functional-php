<?php

require_once __DIR__ . '/../vendor/autoload.php'; // composer autoload

function float($str) {
    return (bool)preg_match('/^\d+([\.,]\d+)?$/D', $str);
}

class User {

    /**
     * @var array
     */
    private $data;

    public $first_name;

    public function __construct(array $data)
    {
        $this->data = $data;
        if (isset($this->data['first_name'])) {
            $this->first_name = $this->data['first_name'];
        }
    }

    public function __invoke($nameSeparator)
    {
        return $this->data['first_name'] . $nameSeparator . $this->data['last_name'];
    }

    public function getFullName($nameSeparator)
    {
        return $this($nameSeparator);
    }

    public static function getAddress($separator)
    {
        return null;
    }

    public function isActive()
    {
        return \Functional\prop('active', $this->data) ?: false;
    }
}

class Value
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function concatWith($str)
    {
        return $this->value.$str;
    }

    public function concatWith2($str, $str2)
    {
        return $this->value.$str.$str2;
    }
}

class Repeated
{
    public function someMethod()
    {
    }
}

class ClassWithMonadValue
{
    /**
     * @var \Functional\Monad
     */
    private $name;

    public function __construct(\Functional\Monad $name)
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