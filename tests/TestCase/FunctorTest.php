<?php

namespace Basko\FunctionalTest\TestCase;

use Basko\Functional as f;
use Basko\Functional\Functor\Either;
use Basko\Functional\Functor\Optional;
use Basko\FunctionalTest\Functor\LogWriter;
use Basko\FunctionalTest\Functor\MaybeString;
use Basko\FunctionalTest\Functor\MaybeUser;
use Basko\FunctionalTest\Helpers\User;

class FunctorTest extends BaseTest
{
    public function test_string_representation()
    {
        $this->assertEquals('Identity(1)', (string)f\Functor\Identity::of(1));
        $this->assertEquals('Constant(1)', (string)f\Functor\Constant::of(1));

        $this->assertEquals('Just(1)', (string)f\Functor\Maybe::just(1));
        $this->assertEquals('Nothing', (string)f\Functor\Maybe::nothing());

        $this->assertEquals('Just(1)', (string)f\Functor\Optional::just(1));
        $this->assertEquals('Just(NULL)', (string)f\Functor\Optional::just(null));
        $this->assertEquals('Nothing', (string)f\Functor\Optional::nothing());

        $this->assertEquals("IO('file_get_contents')", (string)f\Functor\IO::of('file_get_contents'));

        $this->assertEquals('Right(1)', (string)f\Functor\Either::right(1));
        $this->assertEquals("Left('error')", (string)f\Functor\Either::left('error'));

        $this->assertEquals(
            'Writer(aggregation:array(0=>1,1=>2,2=>3,),value:1)',
            \str_replace([' ', "\n"], '', (string)f\Functor\Writer::of([1, 2, 3], 1))
        );
    }

    public function test_identity()
    {
        $this->assertEquals(f\Functor\Identity::of('FOO'), f\Functor\Identity::of('foo')->map('strtoupper'));
        $this->assertEquals(f\Functor\Identity::of(6), f\Functor\Identity::of(3)->map(f\multiply(2)));
    }

    public function test_identity_flat_map()
    {
        $this->assertEquals(
            f\Functor\Identity::of(6),
            f\Functor\Identity::of(3)->flatMap(function ($x) {
                return f\Functor\Identity::of($x * 2);
            })
        );
    }

    public function test_identity_flat_map_falsy()
    {
        $this->setExpectedException(
            f\Exception\TypeException::class,
            'Basko\Functional\Functor\Identity::flatMap(): Return value must be of type Basko\Functional\Functor\Identity, int returned'
        );
        f\Functor\Identity::of(3)->flatMap(f\multiply(2));
    }

    public function test_constant()
    {
        $this->assertEquals(f\Functor\Constant::of(3), f\Functor\Constant::of(3)->map(f\multiply(2)));
    }

    public function test_maybe()
    {
        $this->assertEquals(f\Functor\Maybe::just('1'), f\Functor\Maybe::just(1)->map('strval'));

        $called = false;
        $func = function ($a) use (&$called) {
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

    public function test_maybe_typed()
    {
        $this->assertInstanceOf(
            MaybeString::class,
            MaybeString::just('str')
        );

        $this->assertInstanceOf(
            MaybeUser::class,
            MaybeUser::just(new User([]))
        );
    }

    public function test_maybe_typed_scalar_falsy()
    {
        $this->setExpectedException(
            f\Exception\InvalidArgumentException::class,
            'MaybeString() expects parameter 1 to be string, integer (1) given'
        );
        MaybeString::just(1);
    }

    public function test_maybe_typed_falsy()
    {
        $this->setExpectedException(
            f\Exception\InvalidArgumentException::class,
            'MaybeUser() expects parameter 1 to be Basko\FunctionalTest\Helpers\User, integer (1) given'
        );
        MaybeUser::just(1);
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

        $justHandler = function ($a) use (&$justHandlerCallFlag) {
            $justHandlerCallFlag = true;
        };
        $nothingHandler = function () use (&$nothingHandlerCallFlag) {
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
            $f = f\if_else(f\is_even, f\pipe(f\partial_r(f\div, 2), f\Functor\Maybe::just), f\Functor\Maybe::nothing);

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

    public function test_maybe_flat_map()
    {
        $this->assertEquals(
            f\Functor\Maybe::just(6),
            f\Functor\Maybe::just(3)->flatMap(function ($x) {
                return f\Functor\Maybe::just($x * 2);
            })
        );
    }

    public function test_maybe_flat_map_falsy()
    {
        $this->setExpectedException(
            f\Exception\TypeException::class,
            'Basko\Functional\Functor\Maybe::flatMap(): Return value must be of type Basko\Functional\Functor\Maybe, int returned'
        );
        f\Functor\Maybe::just(3)->flatMap(f\multiply(2));
    }

    public function test_optional()
    {
        $_POST = [
            'title' => 'Some title',
            'description' => null,
        ];

        $optionalDescription = Optional::fromProp('description', $_POST);

        $called = false;
        $func = function ($a) use (&$called) {
            $called = true;
        };

        $optionalDescription->map($func);

        $this->assertTrue($called);
    }

    public function test_optional2()
    {
        $_POST = [
            'title' => 'Some title',
            'description' => null,
        ];

        $optionalNoKey = Optional::fromProp('no_key', $_POST);

        $called = false;
        $func = function ($a) use (&$called) {
            $called = true;
        };

        $optionalNoKey->map(f\tap);
        $optionalNoKey->match($func, f\N);

        $this->assertFalse($called);
    }

    public function test_optional_with_object()
    {
        $request = new \stdClass();
        $request->title = 'Some title';
        $request->description = null;

        $optionalTitle = Optional::fromProp('title', $request);
        $optionalTitle->match(
            function ($value) {
                $this->assertEquals('Some title', $value);
            },
            function () {
                $this->fail('test_optional_with_object() failed');
            }
        );

        $optionalNoKey = Optional::fromProp('no_key', $request);
        $optionalNoKey->match(
            function () {
                $this->fail('test_optional_with_object() with no_key failed');
            },
            f\noop
        );
    }

    public function test_optional_type_no()
    {
        $this->assertEquals(
            Optional::nothing(),
            Optional::fromProp('no_key', [], f\type_string)
        );
    }

    public function test_optional_type()
    {
        $this->assertEquals(
            Optional::just('value'),
            Optional::fromProp('key', ['key' => 'value'], f\type_string)
        );
    }

    public function test_optional_type_convert()
    {
        $this->assertType(
            'int',
            Optional::fromProp('key', ['key' => '1'], f\type_int)->extract()
        );
    }

    public function test_optional_type_non_convertable()
    {
        $this->setExpectedException(
            f\Exception\TypeException::class,
            'Could not convert "string" to type "int"'
        );
        Optional::fromProp('key', ['key' => 'non-convertable-string'], f\type_int);
    }

    public function test_optional_flat_map()
    {
        $this->assertEquals(
            f\Functor\Optional::just(6),
            f\Functor\Optional::just(3)->flatMap(function ($x) {
                return f\Functor\Optional::just($x * 2);
            })
        );
    }

    public function test_optional_flat_map_falsy()
    {
        $this->setExpectedException(
            f\Exception\TypeException::class,
            'Basko\Functional\Functor\Optional::flatMap(): Return value must be of type Basko\Functional\Functor\Optional, int returned'
        );
        f\Functor\Optional::just(3)->flatMap(f\multiply(2));
    }

    public function test_either()
    {
        $shouldContainAtSign = function ($string) {
            if (!stristr($string, '@')) {
                throw new \InvalidArgumentException('The string should contain an @ sign');
            }

            return $string;
        };

        $shouldContainDot = function ($string) {
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
        $shouldContainAtSign = function ($string) {
            if (!stristr($string, '@')) {
                return Either::left('The string should contain an @ sign');
            }

            return Either::right($string);
        };

        $shouldContainDot = function ($string) {
            if (!stristr($string, '.')) {
                return Either::left('The string should contain a . sign');
            }

            return Either::right($string);
        };

        $pipe = f\pipe(
            $shouldContainAtSign,
            f\lift_m($shouldContainDot)
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

    public function test_either_flat_map()
    {
        $this->assertEquals(
            f\Functor\Either::right(6),
            f\Functor\Either::right(3)->flatMap(function ($x) {
                return f\Functor\Either::right($x * 2);
            })
        );
    }

    public function test_either_flat_map_falsy()
    {
        $this->setExpectedException(
            f\Exception\TypeException::class,
            'Basko\Functional\Functor\Either::flatMap(): Return value must be of type Basko\Functional\Functor\Either, int returned'
        );
        f\Functor\Either::right(3)->flatMap(f\multiply(2));
    }

    public function test_io()
    {
        $m = f\Functor\IO::of('file_get_contents')
            ->map('ucfirst')
            ->map(f\take(4))
            ->map(f\partial_r(f\concat, 'ik'));

        $this->assertEquals('Slavik', $m(__DIR__ . '/../name.txt'));
    }

    public function test_io_falsy()
    {
        $m = f\try_catch(
            f\compose(f\Functor\Either::right, f\Functor\IO::of('file_get_contents')->map('ucfirst')),
            f\compose(f\Functor\Either::left, f\invoker('getMessage'), f\identity)
        );

        $result = $m(__DIR__ . '/non-existed-file.txt');

        $this->assertTrue(
            f\contains('No such file or directory', $result->extract())
        );
    }

    public function test_io_flat_map_falsy()
    {
        $this->setExpectedException(
            f\Exception\TypeException::class,
            'Basko\Functional\Functor\IO::flatMap(): Return value must be of type Basko\Functional\Functor\IO, int returned'
        );
        f\Functor\IO::of(f\always(3))->flatMap(f\multiply(2));
    }

    public function test_transform_identity()
    {
        $m = f\Functor\Identity::of(1);

        $m2 = $m->transform(f\Functor\Constant::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\Constant::class, $m2);
        $this->assertEquals(1, $m2->extract());

        $m2 = $m->transform(f\Functor\Either::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\Either::class, $m2);
        $this->assertEquals(2, $m2->extract());

        $m2 = $m->transform(f\Functor\Maybe::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\Maybe::class, $m2);
        $this->assertEquals(2, $m2->extract());

        $m2 = $m->transform(f\Functor\Optional::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\Optional::class, $m2);
        $this->assertEquals(2, $m2->extract());

        $m2 = $m->transform(f\Functor\IO::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\IO::class, $m2);
        $this->assertEquals(2, $m2());
    }

    public function test_transform_constant()
    {
        $m = f\Functor\Constant::of(1);

        $m2 = $m->transform(f\Functor\Identity::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\Identity::class, $m2);
        $this->assertEquals(2, $m2->extract());

        $m2 = $m->transform(f\Functor\Either::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\Either::class, $m2);
        $this->assertEquals(2, $m2->extract());

        $m2 = $m->transform(f\Functor\Maybe::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\Maybe::class, $m2);
        $this->assertEquals(2, $m2->extract());

        $m2 = $m->transform(f\Functor\Optional::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\Optional::class, $m2);
        $this->assertEquals(2, $m2->extract());

        $m2 = $m->transform(f\Functor\IO::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\IO::class, $m2);
        $this->assertEquals(2, $m2());
    }

    public function test_transform_either()
    {
        $m = f\Functor\Either::right(1);
        $m2 = f\Functor\Either::left('error');

        $m3 = $m->transform(f\Functor\Constant::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\Constant::class, $m3);
        $this->assertEquals(1, $m3->extract());

        $m3 = $m->transform(f\Functor\Identity::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\Identity::class, $m3);
        $this->assertEquals(2, $m3->extract());

        $m3 = $m->transform(f\Functor\Maybe::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\Maybe::class, $m3);
        $this->assertEquals(2, $m3->extract());
        $m4 = $m2->transform(f\Functor\Maybe::class)->map(function () {
            $this->fail('test_transform_either test failed');
        });
        $this->assertInstanceOf(f\Functor\Maybe::class, $m4);
        $this->assertEquals(null, $m4->extract());

        $m3 = $m->transform(f\Functor\Optional::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\Optional::class, $m3);
        $this->assertEquals(2, $m3->extract());
        $m4 = $m2->transform(f\Functor\Optional::class)->map(function () {
            $this->fail('test_transform_either test failed');
        });
        $this->assertInstanceOf(f\Functor\Optional::class, $m4);
        $this->assertEquals(null, $m4->extract());

        $m3 = $m->transform(f\Functor\IO::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\IO::class, $m3);
        $this->assertEquals(2, $m3());
    }

    public function test_transform_maybe()
    {
        $m = f\Functor\Maybe::just(1);
        $m2 = f\Functor\Maybe::nothing();

        $m3 = $m->transform(f\Functor\Constant::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\Constant::class, $m3);
        $this->assertEquals(1, $m3->extract());

        $m3 = $m->transform(f\Functor\Identity::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\Identity::class, $m3);
        $this->assertEquals(2, $m3->extract());

        $m3 = $m->transform(f\Functor\Either::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\Either::class, $m3);
        $this->assertEquals(2, $m3->extract());
        $m4 = $m2->transform(f\Functor\Either::class)->map(function () {
            $this->fail('test_transform_either test failed');
        });
        $this->assertInstanceOf(f\Functor\Either::class, $m4);
        $this->assertEquals('Nothing', $m4->extract());

        $m3 = $m->transform(f\Functor\Optional::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\Optional::class, $m3);
        $this->assertEquals(2, $m3->extract());
        $m4 = $m2->transform(f\Functor\Optional::class)->map(function () {
            $this->fail('test_transform_either test failed');
        });
        $this->assertInstanceOf(f\Functor\Optional::class, $m4);
        $this->assertEquals(null, $m4->extract());

        $m3 = $m->transform(f\Functor\IO::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\IO::class, $m3);
        $this->assertEquals(2, $m3());
    }

    public function test_transform_optional()
    {
        $m = f\Functor\Optional::just(1);
        $m2 = f\Functor\Optional::nothing();

        $m3 = $m->transform(f\Functor\Constant::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\Constant::class, $m3);
        $this->assertEquals(1, $m3->extract());

        $m3 = $m->transform(f\Functor\Identity::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\Identity::class, $m3);
        $this->assertEquals(2, $m3->extract());

        $m3 = $m->transform(f\Functor\Either::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\Either::class, $m3);
        $this->assertEquals(2, $m3->extract());
        $m4 = $m2->transform(f\Functor\Either::class)->map(function () {
            $this->fail('test_transform_either test failed');
        });
        $this->assertInstanceOf(f\Functor\Either::class, $m4);
        $this->assertEquals('Nothing', $m4->extract());

        $m3 = $m->transform(f\Functor\Maybe::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\Maybe::class, $m3);
        $this->assertEquals(2, $m3->extract());
        $m4 = $m2->transform(f\Functor\Maybe::class)->map(function () {
            $this->fail('test_transform_either test failed');
        });
        $this->assertInstanceOf(f\Functor\Maybe::class, $m4);
        $this->assertEquals(null, $m4->extract());

        $m3 = $m->transform(f\Functor\IO::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\IO::class, $m3);
        $this->assertEquals(2, $m3());

        $plus1 = function ($x) {
            return f\Functor\Writer::of([__FUNCTION__ . ' executed'], $x + 1);
        };
        $m3 = $m->transform(f\Functor\Writer::class)->map($plus1);
        $this->assertInstanceOf(f\Functor\Writer::class, $m3);
        $this->assertEquals(2, $m3->extract());
    }

    public function test_transform_fail()
    {
        $this->setExpectedException(
            f\Exception\InvalidArgumentException::class,
            'Monad::transform() expects parameter 1 to be class-string<Monad>'
        );
        f\Functor\Identity::of(1)->transform(User::class);
    }

    public function test_writer()
    {
        $plus1 = function ($a) {
            return f\Functor\Writer::of(['plus1 executed'], $a + 1);
        };

        $plus2 = function ($a) {
            return f\Functor\Writer::of(['plus2 executed'], $a + 2);
        };

        f\Functor\Writer::of([], 1)
            ->flatMap($plus1)
            ->flatMap($plus2)
            ->match(
                function ($value) {
                    $this->assertEquals(4, $value);
                },
                function ($aggregation) {
                    $this->assertEquals(['plus1 executed', 'plus2 executed'], $aggregation);
                }
            );
    }

    public function test_custom_writer()
    {
        $plus1 = function ($a) {
            return LogWriter::of('plus1 executed', $a + 1);
        };

        $plus2 = function ($a) {
            return LogWriter::of('plus2 executed', $a + 2);
        };

        LogWriter::of('', 1)
            ->flatMap($plus1)
            ->flatMap($plus2)
            ->match(
                function ($value) {
                    $this->assertEquals(4, $value);
                },
                function ($aggregation) {
                    $this->assertEquals(\sprintf(
                        "[%s]plus1 executed\n[%s]plus2 executed\n",
                        LogWriter::TIME,
                        LogWriter::TIME
                    ), $aggregation);
                }
            );
    }

    public function test_writer_fail()
    {
        $this->setExpectedException(
            f\Exception\InvalidArgumentException::class,
            'Argument 1 passed to Basko\Functional\Functor\Writer::of must be of the type string, array, int, float or bool, NULL given'
        );
        f\Functor\Writer::of(null, 1);
    }

    public function test_transform_writer()
    {
        $m = f\Functor\Writer::of([], 1);
        $mNull = f\Functor\Writer::of([], null);

        $m2 = $m->transform(f\Functor\Constant::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\Constant::class, $m2);
        $this->assertEquals(1, $m2->extract());

        $m2 = $m->transform(f\Functor\Either::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\Either::class, $m2);
        $this->assertEquals(2, $m2->extract());

        $m2 = $m->transform(f\Functor\Maybe::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\Maybe::class, $m2);
        $this->assertEquals(2, $m2->extract());
        $m2 = $mNull->transform(f\Functor\Maybe::class)->map(f\plus(1));
        $this->assertTrue($m2->isNothing());

        $m2 = $m->transform(f\Functor\Optional::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\Optional::class, $m2);
        $this->assertEquals(2, $m2->extract());

        $m2 = $m->transform(f\Functor\IO::class)->map(f\plus(1));
        $this->assertInstanceOf(f\Functor\IO::class, $m2);
        $this->assertEquals(2, $m2());
    }

}
