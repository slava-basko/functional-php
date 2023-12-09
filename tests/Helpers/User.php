<?php

namespace Basko\FunctionalTest\Helpers;

class User
{

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
        return \Basko\Functional\prop('active', $this->data) ?: false;
    }
}
