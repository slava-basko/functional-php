<?php

namespace Tests\Functional;

use Basko\Functional as f;

class PolyfillTest extends BaseTest
{
    public function test_get_debug_type()
    {
        $this->assertSame(__CLASS__, get_debug_type($this));
        $this->assertSame('stdClass', get_debug_type(new \stdClass()));

        if (PHP_VERSION_ID >= 70000) {
            $this->assertSame('class@anonymous', get_debug_type(eval('return new class() {};')));
            $this->assertSame('stdClass@anonymous', get_debug_type(eval('return new class() extends stdClass {};')));
            $this->assertSame('Reflector@anonymous', get_debug_type(eval('return new class() implements Reflector { function __toString() {} public static function export() {} };')));
        }

        $this->assertSame('string', get_debug_type('foo'));
        $this->assertSame('bool', get_debug_type(false));
        $this->assertSame('bool', get_debug_type(true));
        $this->assertSame('null', get_debug_type(null));
        $this->assertSame('array', get_debug_type([]));
        $this->assertSame('int', get_debug_type(1));
        $this->assertSame('float', get_debug_type(1.2));
        $this->assertSame('resource (stream)', get_debug_type($h = fopen(__FILE__, 'r')));
        $this->assertSame('resource (closed)', get_debug_type(fclose($h) ? $h : $h));

        $unserializeCallbackHandler = ini_set('unserialize_callback_func', null);
        ini_set('unserialize_callback_func', $unserializeCallbackHandler);
        $var = unserialize('O:8:"Foo\Buzz":0:{}');

        $this->assertSame('__PHP_Incomplete_Class', get_debug_type($var));
    }

    /**
     * @dataProvider provideValidDigits
     */
    public function test_valid_ctype_digit($text)
    {
        $this->assertTrue(ctype_digit($text));
    }

    public static function provideValidDigits()
    {
        return [
            ['0'],
            ['123'],
            ['01234'],
            ['934'],
        ];
    }
}