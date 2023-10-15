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

    public function test_split_fail()
    {
        $this->setExpectedException(
            'Basko\Functional\Exception\InvalidArgumentException',
            'str_split() expects parameter 2 to be string, NULL given'
        );
        f\str_split(' ', null);
    }

    public function test_str_split_on()
    {
        $this->assertEquals(['UA', '1234567890'], f\str_split_on(2, 'UA1234567890'));
    }

    public function test_str_split_on_fail()
    {
        $this->setExpectedException(
            'Basko\Functional\Exception\InvalidArgumentException',
            'str_split_on() expects parameter 2 to be string, NULL given'
        );
        f\str_split_on(2, null);
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

        $testStr = 'beginningMiddleEnd';
        $testStrF = f\partial_r(f\str_starts_with, 'beginningMiddleEnd');

        $this->assertTrue($testStrF('beginning'));
        $this->assertTrue($testStrF($testStr));
        $this->assertTrue($testStrF(''));
        $this->assertTrue(f\str_starts_with('', "\x00"));
        $this->assertTrue(f\str_starts_with("\x00", "\x00"));
        $this->assertTrue(f\str_starts_with("\x00", "\x00a"));
        $this->assertTrue(f\str_starts_with("a\x00b", "a\x00bc"));

        $this->assertFalse($testStrF('Beginning'));
        $this->assertFalse($testStrF('eginning'));
        $this->assertFalse($testStrF($testStr.$testStr));
        $this->assertFalse($testStrF("\x00"));
        $this->assertFalse(f\str_starts_with("a\x00d", "a\x00b"));
        $this->assertFalse(f\str_starts_with("z\x00b", "a\x00b"));
        $this->assertFalse(f\str_starts_with("a\x00", 'a'));
        $this->assertFalse(f\str_starts_with("\x00a", 'a'));

        // අයේෂ් = අ + ය + "ේ" + ෂ + ්
        // අයේෂ් = (0xe0 0xb6 0x85) + (0xe0 0xb6 0xba) + (0xe0 0xb7 0x9a) + (0xe0 0xb7 0x82) + (0xe0 0xb7 0x8a)
        $testMultiByteF = f\partial_r(f\str_starts_with, 'අයේෂ්');; // 0xe0 0xb6 0x85 0xe0 0xb6 0xba 0xe0 0xb7 0x9a 0xe0 0xb7 0x82 0xe0 0xb7 0x8a
        $this->assertTrue($testMultiByteF('අයේ')); // 0xe0 0xb6 0x85 0xe0 0xb6 0xba 0xe0 0xb7 0x9a
        $this->assertTrue($testMultiByteF('අය')); // 0xe0 0xb6 0x85 0xe0 0xb6 0xba
        $this->assertFalse($testMultiByteF('ය')); // 0xe0 0xb6 0xba
        $this->assertFalse($testMultiByteF('අේ')); // 0xe0 0xb6 0x85 0xe0 0xb7 0x9a

        $testEmoji = '🙌🎉✨🚀'; // 0xf0 0x9f 0x99 0x8c 0xf0 0x9f 0x8e 0x89 0xe2 0x9c 0xa8 0xf0 0x9f 0x9a 0x80
        $this->assertTrue(f\str_starts_with('🙌', $testEmoji)); // 0xf0 0x9f 0x99 0x8c
        $this->assertFalse(f\str_starts_with('✨', $testEmoji)); // 0xe2 0x9c 0xa8
    }

    public function test_str_starts_with_fail()
    {
        $this->setExpectedException(
            'Basko\Functional\Exception\InvalidArgumentException',
            'str_starts_with() expects parameter 2 to be string, NULL given'
        );
        f\str_starts_with('http://', null);
    }

    public function test_str_ends_with() {
        $dotCom = f\str_ends_with('.com');
        $this->assertTrue($dotCom('http://gitbub.com'));
        $this->assertFalse($dotCom('php.net'));

        $testMultiByte = 'අයේෂ්'; // 0xe0 0xb6 0x85 0xe0 0xb6 0xba 0xe0 0xb7 0x9a 0xe0 0xb7 0x82 0xe0 0xb7 0x8a
        $this->assertTrue(f\str_ends_with('ෂ්', $testMultiByte)); // 0xe0 0xb7 0x82 0xe0 0xb7 0x8a
        $this->assertTrue(f\str_ends_with('්', $testMultiByte)); // 0xe0 0xb7 0x8a
        $this->assertFalse(f\str_ends_with('ෂ', $testMultiByte)); // 0xe0 0xb7 0x82

        $testEmoji = '🙌🎉✨🚀'; // 0xf0 0x9f 0x99 0x8c 0xf0 0x9f 0x8e 0x89 0xe2 0x9c 0xa8 0xf0 0x9f 0x9a 0x80
        $this->assertTrue(f\str_ends_with('🚀', $testEmoji)); // 0xf0 0x9f 0x9a 0x80
        $this->assertFalse(f\str_ends_with('✨', $testEmoji)); // 0xe2 0x9c 0xa8
    }

    public function test_str_ends_with_fail()
    {
        $this->setExpectedException(
            'Basko\Functional\Exception\InvalidArgumentException',
            'str_ends_with() expects parameter 2 to be string, NULL given'
        );
        f\str_ends_with('.com', null);
    }

    public function test_str_test() {
        $numeric = f\str_test('/^[0-9.]+$/');
        $this->assertTrue($numeric('123.43'));
        $this->assertFalse($numeric('12a3.43'));
    }

    public function test_str_test_fail()
    {
        $this->setExpectedException(
            'Basko\Functional\Exception\InvalidArgumentException',
            'str_test() expects parameter 2 to be string, NULL given'
        );
        f\str_test('/[a-z]/', null);
    }

    public function test_str_pad_left()
    {
        $pad6 = f\str_pad_left(6);
        $padZero = $pad6('0');
        $this->assertEquals('000481', $padZero('481'));
    }

    public function test_str_pad_left_fail()
    {
        $this->setExpectedException(
            'Basko\Functional\Exception\InvalidArgumentException',
            'str_pad_left() expects parameter 3 to be string, NULL given'
        );
        f\str_pad_left(5, 'str', null);
    }

    public function test_str_pad_right()
    {
        $pad6 = f\str_pad_right('6');
        $padZero = $pad6('0');
        $this->assertEquals('481000', $padZero('481'));
    }

    public function test_str_pad_right_fail()
    {
        $this->setExpectedException(
            'Basko\Functional\Exception\InvalidArgumentException',
            'str_pad_right() expects parameter 3 to be string, NULL given'
        );
        f\str_pad_right(5, 'str', null);
    }
}
