<?php

namespace Tests\Functional;

use PHPUnit\Framework\TestCase;
use RuntimeException;

abstract class BaseTest extends TestCase
{
    /**
     * @param $exceptionClass
     * @param $exceptionMessage
     * @param $exceptionCode
     * @return void
     */
    public function setExpectedException($exceptionClass, $exceptionMessage = '', $exceptionCode = null)
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException($exceptionClass, $exceptionMessage, $exceptionCode);
        } else if (method_exists(parent::class, 'setExpectedException')) {
            parent::setExpectedException($exceptionClass, $exceptionMessage, $exceptionCode);
        } else {
            throw new RuntimeException("Don't know how to expect exceptions");
        }
    }
}
