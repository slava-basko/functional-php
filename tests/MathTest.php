<?php

namespace Tests\Functional;

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

    public function test_minus()
    {
        $this->assertSame(1, f\minus(3, 2));
        $minus10 = f\minus(10);
        $this->assertSame(1, $minus10(11));
    }

    public function test_div()
    {
        $this->assertSame(2, f\div(4, 2));
        $div2 = f\div(2);
        $this->assertSame(2, $div2(4));
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

    public function test_multiply()
    {
        $this->assertSame(8, f\multiply(4, 2));
        $multiply2 = f\multiply(2);
        $this->assertSame(8, $multiply2(4));
    }

    public function test_sun()
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
}
