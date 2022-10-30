<?php

namespace Tests\Functional;

use Basko\Functional as f;
use Basko\Functional\Functor\Optional;

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
        $this->assertEquals(f\Functor\Maybe::just('1'), f\Functor\Maybe::just(1)->map('strval'));

        $called = false;
        $func = function($a) use (&$called) {
            $called = true;
        };
        $this->assertEquals(f\Functor\Maybe::nothing(), f\Functor\Maybe::nothing()->map($func));
        $this->assertFalse($called);
    }

    public function test_maybe_chain()
    {
        $getParent = f\invoker('getParent');
        $getName = f\invoker('getName');
        $this->assertEquals(
            f\Functor\Maybe::nothing(),
            f\Functor\Maybe::nothing()->map($getParent)->map($getParent)->map($getName)
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
        f\Functor\Maybe::just(10)->match($justHandler, $nothingHandler);
        $this->assertTrue($justHandlerCallFlag);
        $this->assertFalse($nothingHandlerCallFlag);

        // Reset flags
        $justHandlerCallFlag = false;
        $nothingHandlerCallFlag = false;

        // Test without value
        f\Functor\Maybe::nothing()->match($justHandler, $nothingHandler);
        $this->assertFalse($justHandlerCallFlag);
        $this->assertTrue($nothingHandlerCallFlag);
    }

    public function test_half()
    {
        $half = function ($x) {
            f\Exception\InvalidArgumentException::assertInteger($x, __FUNCTION__, 1);
            if (f\is_even($x)) {
                return f\Functor\Maybe::just($x)->map(function ($n) {
                    return $n / 2;
                });
            } else {
                return f\Functor\Maybe::nothing();
            }
        };

        $this->assertEquals(
            f\Functor\Maybe::just(2),
            f\Functor\Maybe::just(8)->map($half)->map($half)
        );

        $this->assertEquals(
            f\Functor\Maybe::nothing(),
            f\Functor\Maybe::just(3)->map($half)
        );
    }

    public function test_div_zero()
    {
        $safe_div = function ($a, $b) {
            f\Exception\InvalidArgumentException::assertInteger($a, __FUNCTION__, 1);
            f\Exception\InvalidArgumentException::assertInteger($b, __FUNCTION__, 2);

            if ($b == 0) {
                return f\Functor\Maybe::nothing();
            }

            return f\Functor\Maybe::just($a / $b);
        };

        $this->assertEquals(
            f\Functor\Maybe::just(4),
            f\Functor\Maybe::just(8)->map(f\partial_r($safe_div, 2))
        );

        $this->assertEquals(
            f\Functor\Maybe::nothing(),
            f\Functor\Maybe::just(8)->map(f\partial_r($safe_div, 0))
        );
    }

    public function test_optional()
    {
        $_POST = [
            'title' => 'Some title',
            'description' => null
        ];

        $optionalDescription = Optional::fromArrayKey('description', $_POST);

        $called = false;
        $func = function($a) use (&$called) {
            $called = true;
        };

        $optionalDescription->map($func);

        $this->assertTrue($called);
    }

    public function test_optional2()
    {
        $_POST = [
            'title' => 'Some title',
            'description' => null
        ];

        $optionalNoKey = Optional::fromArrayKey('no_key', $_POST);

        $called = false;
        $func = function($a) use (&$called) {
            $called = true;
        };

        $optionalNoKey->map($func);

        $this->assertFalse($called);
    }
}
