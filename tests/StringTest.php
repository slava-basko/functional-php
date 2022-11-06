<?php

namespace Tests\Functional;

use Basko\Functional as f;

class StringTest extends BaseTest
{
    public function test_split()
    {
        $words = f\str_split(' ');
        $this->assertEquals(['Hello', 'World'], $words('Hello World'));
    }

    public function test_str_replace()
    {
        $string = 'a b c d e f';
        $noSpace = f\str_replace(' ', '');
        $this->assertEquals('abcdef', $noSpace($string));
        $this->assertEquals('cdef', f\str_replace(['a', 'b', ' '], '', $string));
        $this->assertEquals('xbcdyf', f\str_replace(['a', 'e', ' '], ['x', 'y', ''], $string));

        $f = f\str_replace(' ');
        $f2 = $f('');
        $this->assertEquals('abcdef', $f2($string));
    }

    public function test_str_starts_with() {
        $http = f\str_starts_with('http://');
        $this->assertTrue($http('http://gitbub.com'));
        $this->assertFalse($http('gitbub.com'));
    }

    public function test_str_ends_with() {
        $dotCom = f\str_ends_with('.com');
        $this->assertTrue($dotCom('http://gitbub.com'));
        $this->assertFalse($dotCom('php.net'));
    }

    public function test_str_test() {
        $numeric = f\str_test('/^[0-9.]+$/');
        $this->assertTrue($numeric('123.43'));
        $this->assertFalse($numeric('12a3.43'));
    }

    public function test_str_pad_left()
    {
        $pad6 = f\str_pad_left('6');
        $padZero = $pad6('0');
        $this->assertEquals('000481', $padZero('481'));
    }

    public function test_str_pad_right()
    {
        $pad6 = f\str_pad_right('6');
        $padZero = $pad6('0');
        $this->assertEquals('481000', $padZero('481'));
    }
}
