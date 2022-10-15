<?php

namespace Tests\Functional;

use Functional\Maybe;
use PHPUnit\Framework\TestCase;
use Functional as f;

class FunctorTest extends TestCase
{
    public function test_identity()
    {
        $this->assertEquals(f\Identity::of('FOO'), f\Identity::of('foo')->map('strtoupper'));
        $this->assertEquals(f\Identity::of(6), f\Identity::of(3)->map(f\multiply(2)));
    }

    public function test_constant()
    {
        $this->assertEquals(f\Constant::of(3), f\Constant::of(3)->map(f\multiply(2)));
    }

    public function test_maybe()
    {
        $this->assertEquals(Maybe::of('1'), Maybe::of(1)->map('strval'));

        $called = false;
        $func = function($a) use (&$called) {
            $called = true;
        };
        $this->assertEquals(Maybe::of(null), Maybe::of(null)->map($func));
        $this->assertFalse($called);
    }

    public function test_maybe_chain()
    {
        $getParent = f\invoker('getParent');
        $getName = f\invoker('getName');
        $this->assertNull(
            Maybe::of(null)->map($getParent)->map($getParent)->map($getName)->extract()
        );
    }

    public function test_maybe_match()
    {
        $justHandlerCallFlag = false;
        $nothingHandlerCallFlag = false;

        $justHandler = function($a) use (&$justHandlerCallFlag) {
            $justHandlerCallFlag = true;
        };
        $nothingHandler = function() use (&$nothingHandlerCallFlag) {
            $nothingHandlerCallFlag = true;
        };

        // Test with value
        Maybe::of(10)->match($justHandler, $nothingHandler);
        $this->assertTrue($justHandlerCallFlag);
        $this->assertFalse($nothingHandlerCallFlag);

        // Reset flags
        $justHandlerCallFlag = false;
        $nothingHandlerCallFlag = false;

        // Test without value
        Maybe::of(null)->match($justHandler, $nothingHandler);
        $this->assertFalse($justHandlerCallFlag);
        $this->assertTrue($nothingHandlerCallFlag);
    }
}