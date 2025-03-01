<?php

namespace Basko\FunctionalTest\TestCase;

use PHPUnit\Framework\TestCase;
use RuntimeException;

abstract class BaseTest extends TestCase
{
    protected function mock($class)
    {
//        if (PHP_VERSION_ID < 80000) {
//            $mockMethod = 'getMock';
//        } else {
//            $mockMethod = 'createMock';
//        }
//
//        return $this->{$mockMethod}($class);

        if (method_exists($this, 'getMock')) {
            return $this->getMock($class);
        }

        return $this->createMock($class);
    }

    /**
     * @param $exceptionClass
     * @param $exceptionMessage
     * @param $exceptionCode
     * @return void
     */
    public function setExpectedException($exceptionClass, $exceptionMessage = '', $exceptionCode = 0)
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException($exceptionClass);
            $this->expectExceptionMessage($exceptionMessage);
            $this->expectExceptionCode($exceptionCode);
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
