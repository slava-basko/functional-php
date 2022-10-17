<?php

namespace Tests\Functional;

use PHPUnit\Framework\TestCase;
use RuntimeException;

abstract class BaseTest extends TestCase
{
    public function setExpectedException(string $exceptionClass)
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
