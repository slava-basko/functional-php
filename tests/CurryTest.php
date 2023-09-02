<?php

namespace Tests\Functional;

use Basko\Functional\Exception\InvalidArgumentException;
use Basko\Functional as f;

class CurryTest extends BaseTest
{
    public function test_count_args()
    {
        $f1 = function ($a) {};
        $f2 = function ($a, $b) {};
        $f3 = function ($a, $b, $c) {};
        $f4 = function ($a, $b, $c, $d) {};

        $this->assertEquals(1, f\count_args($f1));
        $this->assertEquals(2, f\count_args($f2));
        $this->assertEquals(3, f\count_args($f3));
        $this->assertEquals(4, f\count_args($f4));

        $f5 = function ($a, $b, $c = 1) {};
        $this->assertEquals(3, f\count_args($f5));
        $this->assertEquals(2, f\count_args($f5, true));

        $this->assertEquals(2, f\count_args('array_map', true));

        $user = new \User([
            'first_name' => 'Slava',
            'last_name' => 'Basko'
        ]);
        $this->assertEquals(1, f\count_args($user));
        $this->assertEquals(1, f\count_args([$user, 'getFullName']));
        $this->assertEquals(1, f\count_args('\User::getAddress'));
    }

    public function test_curry_n()
    {
        $curriedAdd = f\curry_n(4, function($a, $b, $c, $d) {
            return $a + $b + $c + $d;
        });

        $add10 = $curriedAdd(10);
        $add15 = $add10(5);

        $this->assertEquals(52, $add15(27, 10));
    }

    public function test_curry()
    {
        $add = function($a, $b, $c) {
            return $a + $b + $c;
        };

        $curryiedAdd = f\curry($add);
        $addTen = $curryiedAdd(10);
        $addEleven = $addTen(1);

        $this->assertEquals(15, $addEleven(4));
    }

    public function test_thunkify()
    {
        $add = function($a, $b) {
            return $a + $b;
        };

        $curryiedAdd = f\thunkify($add);
        $addTen = $curryiedAdd(10);
        $eleven = $addTen(1);

        $this->assertEquals(11, $eleven());

        $t_add = f\thunkify(f\plus);
        $eleven = $t_add(10, 1);
        $this->assertEquals(11, $eleven());
    }

    public function test_ary()
    {
        $f = static function ($a = 0, $b = 0, $c = 0) {
            return $a + $b + $c;
        };

        $this->assertSame(5, $f(5));
        $this->assertSame(5, call_user_func_array(f\ary($f, 1), [5, 5]));
        $this->assertSame(6, call_user_func_array(f\ary($f, -1), [5, 6]));
        $this->assertSame(7, call_user_func_array(f\ary($f, 2), [5, 2]));

        $this->setExpectedException(
            InvalidArgumentException::class,
            'Basko\Functional\ary expected parameter 2 to be non-zero'
        );
        $f = function ($a = 0, $b = 0, $c = 0) {
            return $a + $b + $c;
        };
        call_user_func_array(f\ary($f, 0), [5]);
    }

    public function test_unary_binary()
    {
        $f = static function ($a = '', $b = '', $c = '') {
            return $a . $b . $c;
        };
        $this->assertSame('one', call_user_func_array(f\unary($f), ['one', 'two', 'three']));
        $this->assertSame('onetwo', call_user_func_array(f\binary($f), ['one', 'two', 'three']));
    }
}
