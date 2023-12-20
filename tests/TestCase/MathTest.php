<?php

namespace Basko\FunctionalTest\TestCase;

use Basko\Functional as f;

class MathTest extends BaseTest
{
    public function test_is_even()
    {
        $this->assertTrue(f\is_even(2));
        $this->assertTrue(f\is_even(4));
        $this->assertFalse(f\is_even(3));
    }

    public function test_is_odd()
    {
        $this->assertTrue(f\is_odd(3));
        $this->assertTrue(f\is_odd(5));
        $this->assertFalse(f\is_odd(2));
    }

    public function test_plus()
    {
        $this->assertSame(5, f\plus(3, 2));
        $plus10 = f\plus(10);
        $this->assertSame(12, $plus10(2));
    }

    public function test_plus_fail()
    {
        $this->setExpectedException(
            'Basko\Functional\Exception\InvalidArgumentException',
            'plus() expects parameter 2 to be numeric, NULL given'
        );
        f\plus(2, null);
    }

    public function test_minus()
    {
        $this->assertSame(1, f\minus(3, 2));
        $minus10 = f\partial_r(f\minus, 10);
        $this->assertSame(1, $minus10(11));
    }

    public function test_minus_fail()
    {
        $this->setExpectedException(
            'Basko\Functional\Exception\InvalidArgumentException',
            'minus() expects parameter 2 to be numeric, NULL given'
        );
        f\minus(2, null);
    }

    public function test_div()
    {
        $this->assertSame(2, f\div(4, 2));
        $div2 = f\partial_r(f\div, 2);
        $this->assertSame(2, $div2(4));
    }

    public function test_div_fail()
    {
        $this->setExpectedException(
            'Basko\Functional\Exception\InvalidArgumentException',
            'div() expects parameter 2 to be numeric, NULL given'
        );
        f\div(2, null);
    }

    public function test_modulo()
    {
        $this->assertEquals(20 % 10, f\modulo(20, 10)); // 0
        $this->assertEquals(1089 % 37, f\modulo(1089, 37)); // 16
        $this->assertEquals(1089 % -37, f\modulo(1089, -37)); // 16
        $this->assertEquals(-1089 % 37, f\modulo(-1089, 37)); // -16
        $this->assertEquals(-1089 % -37, f\modulo(-1089, -37)); // -16
        $this->assertEquals(-55 % -4, f\modulo(-55, -4)); // -3
    }

    public function test_modulo_fail()
    {
        $this->setExpectedException(
            'Basko\Functional\Exception\InvalidArgumentException',
            'modulo() expects parameter 2 to be numeric, NULL given'
        );
        f\modulo(2, null);
    }

    public function test_multiply()
    {
        $this->assertSame(8, f\multiply(4, 2));
        $multiply2 = f\multiply(2);
        $this->assertSame(8, $multiply2(4));
    }

    public function test_multiply_fail()
    {
        $this->setExpectedException(
            'Basko\Functional\Exception\InvalidArgumentException',
            'multiply() expects parameter 2 to be numeric, NULL given'
        );
        f\multiply(2, null);
    }

    public function test_sum()
    {
        $this->assertEquals(6, f\sum(f\to_list(3, 2, 1)));
        $this->assertEquals(6, f\sum([3, 2, 1]));
    }

    public function test_diff()
    {
        $this->assertEquals(7, f\diff(f\to_list(10, 2, 1)));
        $this->assertEquals(7, f\diff([10, 2, 1]));
    }

    public function test_product()
    {
        $this->assertEquals(16, f\product([4, 2, 2]));
    }

    public function test_inc_dec()
    {
        $this->assertEquals(42, f\inc(41));
        $this->assertEquals(42, f\dec(43));
    }

    public function test_divide()
    {
        $this->assertEquals(5, f\divide([15, 3]));
        $this->assertEquals(5, f\divide([20, 2, 2]));
    }

    public function test_average()
    {
        $this->assertEquals(4, f\average([1, 2, 3, 4, 5, 6, 7]));
    }

    public function test_power()
    {
        $this->assertEquals(4, f\power(2));
        $this->assertEquals(16, f\power(4));
    }

    public function test_median()
    {
        $this->assertEquals(7, f\median([2, 9, 7]));
        $this->assertEquals(8, f\median([7, 2, 10, 9]));
    }

    public function test_clamp()
    {
        $min1 = f\clamp(1);
        $between1and10 = $min1(10);

        $this->assertEquals(1, $between1and10(-5));
        $this->assertEquals(10, f\clamp(1, 10, 15));
        $this->assertEquals(4, f\clamp(1, 10, 4));
        $this->assertEquals('2023-01-01', f\clamp('2023-01-01', '2023-11-22', '2012-11-22'));
    }

    public function testCartesianProduct()
    {
        $this->assertEquals(
            [
                [2, 'Hearts'],
                [2, 'Diamonds'],
                [3, 'Hearts'],
                [3, 'Diamonds'],
            ],
            f\cartesian_product([2, 3], new \ArrayIterator(['Hearts', 'Diamonds']))
        );
    }

    public function testCartesianProductFail()
    {
        $this->setExpectedException(
            f\Exception\InvalidArgumentException::class,
            'cartesian_product() expects parameter 1 to be array or instance of Traversable, NULL given'
        );
        f\cartesian_product(null, ['Hearts', 'Diamonds']);
    }
}
