<?php

namespace Tests\Functional;

use PHPUnit\Framework\TestCase;
use RuntimeException;

abstract class BaseTest extends TestCase
{
    /**
     * @param string $exceptionClass
     * @return void
     */
    public function setExpectedException($exceptionClass)
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException($exceptionClass);
        } else if (method_exists(parent::class, 'setExpectedException')) {
            parent::setExpectedException($exceptionClass);
        } else {
            throw new RuntimeException("Don't know how to expect exceptions");
        }
    }
}
