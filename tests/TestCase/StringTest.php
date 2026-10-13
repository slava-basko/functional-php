<?php

namespace Basko\FunctionalTest\TestCase;

use Basko\Functional as f;
use Basko\Functional\Exception\InvalidArgumentException;

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

    public function test_str_replace_first()
    {
        $this->assertEquals(
            '123def abcdef abcdef',
            f\str_replace_first('abc', '123', 'abcdef abcdef abcdef')
        );
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
        $testMultiByteF = f\partial_r(f\str_starts_with, 'අයේෂ්'); // 0xe0 0xb6 0x85 0xe0 0xb6 0xba 0xe0 0xb7 0x9a 0xe0 0xb7 0x82 0xe0 0xb7 0x8a
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
        $pad6 = f\str_pad_right(6);
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

    public function test_str_contains_any()
    {
        $containsAny = f\str_contains_any(['a', 'b', 'c']);
        $this->assertTrue($containsAny('abc'));
        $this->assertTrue($containsAny('a'));
        $this->assertTrue($containsAny('b'));
        $this->assertTrue($containsAny('c'));
        $this->assertFalse($containsAny(''));
        $this->assertFalse($containsAny('defg'));
    }

    public function test_str_contains_any_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'str_contains_any() expects parameter 2 to be string, NULL given'
        );
        f\str_contains_any(['a', 'b', 'c'], null);
    }

    public function test_str_contains_all()
    {
        $containsAll = f\str_contains_all(['a', 'b', 'c']);
        $this->assertTrue($containsAll('abc'));
        $this->assertTrue($containsAll('cba'));
        $this->assertFalse($containsAll('a'));
        $this->assertFalse($containsAll('b'));
        $this->assertFalse($containsAll('c'));
        $this->assertFalse($containsAll(''));
        $this->assertFalse($containsAll('defg'));
    }

    public function test_str_contains_all_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'str_contains_all() expects parameter 2 to be string, NULL given'
        );
        f\str_contains_all(['a', 'b', 'c'], null);
    }

    public function test_str_surround()
    {
        $prefixLeftParenthesis = f\str_surround('(');
        $prefixLeftAndRightParenthesis = $prefixLeftParenthesis(')');
        $this->assertEquals('(abc)', $prefixLeftAndRightParenthesis('abc'));

        $parenthesisWrap = f\str_surround('(', ')');
        $this->assertEquals('(abc)', $parenthesisWrap('abc'));
    }

    public function test_str_surround_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'str_surround() expects parameter 2 to be string, NULL given'
        );
        f\str_surround('(', null);
    }

    public function test_str_contains()
    {
        $this->assertTrue(f\str_contains('foo', 'foobar'));
        $this->assertTrue(f\str_contains('bar', 'foobar'));
        $this->assertFalse(f\str_contains('baz', 'foobar'));
        $this->assertFalse(f\str_contains('Foo', 'foobar'));
        $this->assertFalse(f\str_contains('foo', ''));
        $this->assertTrue(f\str_contains('', 'foobar'));
        $this->assertTrue(f\str_contains('', ''));
        $this->assertTrue(f\str_contains('0', '10'));

        $hasNoindex = f\str_contains('noindex');
        $this->assertTrue($hasNoindex('noindex, nofollow'));
        $this->assertFalse($hasNoindex('index, follow'));
    }

    public function test_str_contains_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'str_contains() expects parameter 2 to be string, NULL given'
        );
        f\str_contains('foo', null);
    }

    public function test_str_contains_fail_needle()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'str_contains() expects parameter 1 to be string, array given'
        );
        f\str_contains(['foo'], 'foobar');
    }

    public function test_str_replace_regex()
    {
        $this->assertEquals('ssl-certificates', f\str_replace_regex('/\s+/', '-', 'ssl  certificates'));
        $this->assertEquals('a[1]b[22]', f\str_replace_regex('/(\d+)/', '[$1]', 'a1b22'));
        $this->assertEquals('no-match', f\str_replace_regex('/\d+/', '', 'no-match'));

        $dashify = f\str_replace_regex('/\s+/');
        $dashify = $dashify('-');
        $this->assertEquals('a-b-c', $dashify("a \t b\nc"));

        $stripDigits = f\str_replace_regex('/\d/', '');
        $this->assertEquals('abc', $stripDigits('a1b2c3'));
    }

    public function test_str_replace_regex_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'str_replace_regex() expects parameter 3 to be string, NULL given'
        );
        f\str_replace_regex('/\s+/', '-', null);
    }

    public function test_str_replace_regex_fail_replacement()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'str_replace_regex() expects parameter 2 to be string, NULL given'
        );
        f\str_replace_regex('/\s+/', null);
    }

    public function test_str_trim()
    {
        $this->assertEquals('foo', f\str_trim(" \t\"'", ' "foo" '));
        $this->assertEquals('path/to', f\str_trim('/', '/path/to/'));
        $this->assertEquals('foo', f\str_trim(" \t\n\r\0\x0B", " \n foo \t"));
        $this->assertEquals(' foo ', f\str_trim('', ' foo '));

        $trimSlashes = f\str_trim('/');
        $this->assertEquals('a/b', $trimSlashes('//a/b//'));
    }

    public function test_str_trim_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'str_trim() expects parameter 2 to be string, NULL given'
        );
        f\str_trim(' ', null);
    }

    public function test_tag_normalization_pipe()
    {
        $normalizeTag = f\pipe(
            f\str_trim(" \t\"'"),
            'strtolower',
            f\str_replace_regex('/\s+/', '-')
        );

        $this->assertEquals('ssl-certificates', $normalizeTag(' "SSL   Certificates" '));
        $this->assertEquals('php', $normalizeTag("'PHP'\t"));
    }
}
