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
}