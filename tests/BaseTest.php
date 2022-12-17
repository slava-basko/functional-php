<?php

namespace Tests\Functional;

use PHPUnit\Framework\TestCase;
use RuntimeException;

abstract class BaseTest extends TestCase
{
    protected function mock($class)
    {
        if (PHP_VERSION_ID < 80000 && !\function_exists('Functional\match')) {
            $mockMethod = 'getMock';
        } else {
            $mockMethod = 'createMock';
        }

        return $this->{$mockMethod}($class);
    }

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

    protected function assertType($type, $value)
    {
        if (method_exists($this, 'assertInternalType')) {
            $this->assertInternalType($type, $value);
        } else {
            $this->{'assertIs' . ucfirst($type)}($value);
        }
    }
}
