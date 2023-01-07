<?php

namespace Tests\Functional;

use Basko\Functional as f;
use Basko\Functional\Functor\Either;
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

    public function test_maybe_is_methods()
    {
        $m = f\Functor\Maybe::just(1);
        $this->assertTrue($m->isJust());
        $this->assertFalse($m->isNothing());

        $m2 = f\Functor\Maybe::nothing();
        $this->assertTrue($m2->isNothing());
        $this->assertFalse($m2->isJust());
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
            $f = f\if_else(f\is_even, f\pipe(f\div(2), f\Functor\Maybe::just), f\Functor\Maybe::nothing);

            return $f($x);
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

        $optionalNoKey->map(f\tap);
        $optionalNoKey->match($func, f\N);

        $this->assertFalse($called);
    }

    public function test_optional_type_no()
    {
        $this->assertEquals(
            Optional::nothing(),
            Optional::fromArrayKey('no_key', [], f\type_string)
        );
    }

    public function test_optional_type()
    {
        $this->assertEquals(
            Optional::just('value'),
            Optional::fromArrayKey('key', ['key' => 'value'], f\type_string)
        );
    }

    public function test_optional_type_convert()
    {
        $this->assertType(
            'int',
            Optional::fromArrayKey('key', ['key' => '1'], f\type_int)->extract()
        );
    }

    public function test_optional_type_non_convertable()
    {
        $this->setExpectedException(
            f\Exception\TypeException::class,
            'Could not convert "string" to type "int"'
        );
        Optional::fromArrayKey('key', ['key' => 'non-convertable-string'], f\type_int);
    }

    public function test_either()
    {
        $shouldContainAtSign = function($string) {
            if (!stristr($string, '@')) {
                throw new \InvalidArgumentException('The string should contain an @ sign');
            }

            return $string;
        };

        $shouldContainDot = function($string) {
            if (!stristr($string, '.')) {
                throw new \InvalidArgumentException('The string should contain a . sign');
            }

            return $string;
        };

        $this->assertEquals(
            Either::right('name@example.com'),
            Either::right('name@example.com')
                ->map($shouldContainAtSign)
                ->map($shouldContainDot)
        );

        $this->assertEquals(
            Either::left('The string should contain an @ sign'),
            Either::right('nameexample.com')
                ->map($shouldContainAtSign)
                ->map($shouldContainDot)
        );

        $this->assertEquals(
            Either::left('The string should contain a . sign'),
            Either::right('name@examplecom')
                ->map($shouldContainAtSign)
                ->map($shouldContainDot)
        );
    }

    public function test_either_is_methods()
    {
        $e = Either::right(1);
        $this->assertTrue($e->isRight());
        $this->assertFalse($e->isLeft());

        $e2 = Either::left('error');
        $this->assertTrue($e2->isLeft());
        $this->assertFalse($e2->isRight());
    }

    public function test_either_with_functions_that_returns_either()
    {
        $shouldContainAtSign = function($string) {
            if (!stristr($string, '@')) {
                return Either::left('The string should contain an @ sign');
            }

            return Either::right($string);
        };

        $shouldContainDot = function($string) {
            if (!stristr($string, '.')) {
                return Either::left('The string should contain a . sign');
            }

            return Either::right($string);
        };

        $pipe = f\pipe(
            $shouldContainAtSign,
            f\lift_e($shouldContainDot)
        );
        $this->assertEquals(
            Either::right('name@example.com'),
            $pipe('name@example.com')
        );

        $this->assertEquals(
            Either::right('name@example.com'),
            Either::right('name@example.com')
                ->map($shouldContainAtSign)
                ->map($shouldContainDot)
        );

        $this->assertEquals(
            Either::left('The string should contain an @ sign'),
            Either::right('nameexample.com')
                ->map($shouldContainAtSign)
                ->map($shouldContainDot)
        );

        $m = Either::right('name@examplecom')
            ->map($shouldContainAtSign)
            ->map($shouldContainDot);
        $this->assertEquals(
            Either::left('The string should contain a . sign'),
            $m
        );

        $failureCalled = false;
        $failureF = function ($value) use (&$failureCalled) {
            $failureCalled = true;
            return $value;
        };
        $m->match(f\N, $failureF);
        $this->assertTrue($failureCalled);
    }

}
