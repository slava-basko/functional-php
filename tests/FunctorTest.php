<?php

namespace Tests\Functional;

use Basko\Functional as f;

class FunctorTest extends BaseTest
{
    public function test_identity()
    {
        $this->assertEquals(f\Functor\Identity::of('FOO'), f\Functor\Identity::of('foo')->map('strtoupper'));
        $this->assertEquals(f\Functor\Identity::of(6), f\Functor\Identity::of(3)->map(f\multiply(2)));
    }

    public function test_constant()
    {
        $this->assertEquals(f\Functor\Constant::of(3), f\Functor\Constant::of(3)->map(f\multiply(2)));
    }

    public function test_maybe()
    {
        $this->assertEquals(f\Functor\Maybe::of('1'), f\Functor\Maybe::of(1)->map('strval'));

        $called = false;
        $func = function($a) use (&$called) {
            $called = true;
        };
        $this->assertEquals(f\Functor\Maybe::of(null), f\Functor\Maybe::of(null)->map($func));
        $this->assertFalse($called);
    }

    public function test_maybe_chain()
    {
        $getParent = f\invoker('getParent');
        $getName = f\invoker('getName');
        $this->assertNull(
            f\Functor\Maybe::of(null)->map($getParent)->map($getParent)->map($getName)->extract()
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
        f\Functor\Maybe::of(10)->match($justHandler, $nothingHandler);
        $this->assertTrue($justHandlerCallFlag);
        $this->assertFalse($nothingHandlerCallFlag);

        // Reset flags
        $justHandlerCallFlag = false;
        $nothingHandlerCallFlag = false;

        // Test without value
        f\Functor\Maybe::of(null)->match($justHandler, $nothingHandler);
        $this->assertFalse($justHandlerCallFlag);
        $this->assertTrue($nothingHandlerCallFlag);
    }
}
