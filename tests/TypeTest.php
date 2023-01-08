<?php

namespace Tests\Functional;

use Basko\Functional as f;

class TypeTest extends BaseTest
{
    public function getIntValid()
    {
        yield [123, 123];
        yield [0, 0];
        yield [0, '0'];
        yield [123, '123'];
        yield [-321, '-321'];
        yield [-321, -321];
        yield [7, '7'];
        yield [7, '07'];
        yield [7, '007'];
        yield [0, '000'];
        yield [1, 1.0];
        yield [12, new \Value(12)];
    }

    public function getIntInvalid()
    {
        yield [1.23];
        yield ['1.23'];
        yield ['1e123'];
        yield [''];
        yield [[]];
        yield [[123]];
        yield [null];
        yield [false];
        yield ['-007'];
        yield ['9223372036854775808'];
        yield ['-9223372036854775809'];
        yield ['0xFF'];
        yield [''];
        yield [new \Value('Slava Basko')];
    }

    /**
     * @dataProvider getIntValid
     */
    public function test_type_int_valid($expected, $actual)
    {
        $this->assertEquals($expected, f\type_int($actual));
    }

    /**
     * @dataProvider getIntInvalid
     */
    public function test_type_int_invalid($value)
    {
        $this->setExpectedException(
            f\Exception\TypeException::class,
            sprintf('Could not convert "%s" to type "int"', get_debug_type($value))
        );
        f\type_int($value);
    }


    public function getFloatValid()
    {
        yield [123.0, 123];
        yield [0.0, '+0'];
        yield [0.0, +0];
        yield [0.0, 0];
        yield [0.0, '0'];
        yield [123.0, '123'];
        yield [1e2, '1e2'];
        yield [1.23e45, '1.23e45'];
        yield [1.23e-45, '1.23e-45'];
        yield [1.23e+45, '1.23e+45'];
        yield [.23, '.23'];
        yield [3.0, '3.'];
        yield [9223372036854775808.0, '9223372036854775808'];
        yield [7.0, '007'];
        yield [-0.1, '-0.1'];
        yield [-.5, '-.5'];
        yield [-.9e2, '-.9e2'];
        yield [-0.7e2, '-0.7e2'];
        yield [1.23e45, '1.23e45'];
        yield [1.23e-45, '1.23e-45'];
        yield [-33.e-1, '-33.e-1'];
    }

    public function getFloatInvalid()
    {
        yield [''];
        yield ['foo'];
        yield [null];
        yield [false];
        yield ['0xFF'];
        yield ['1a'];
        yield ['e1'];
        yield ['1e'];
        yield ['ee7'];
        yield ['1e2e1'];
        yield ['1ee1'];
        yield ['1,2'];
        yield [''];
    }

    /**
     * @dataProvider getFloatValid
     */
    public function test_type_float_valid($expected, $actual)
    {
        $this->assertEquals($expected, f\type_float($actual));
    }

    /**
     * @dataProvider getFloatInvalid
     */
    public function test_type_float_invalid($value)
    {
        $this->setExpectedException(
            f\Exception\TypeException::class,
            sprintf('Could not convert "%s" to type "float"', get_debug_type($value))
        );
        f\type_float($value);
    }


    public function getStringValid()
    {
        yield ['hello', 'hello'];
        yield ['123', 123];
        yield ['0', 0];
        yield ['0', '0'];
        yield ['123', '123'];
        yield ['1e23', '1e23'];
    }

    public function getStringInvalid()
    {
        yield [1.0];
        yield [1.23];
        yield [[]];
        yield [[1]];
        yield [null];
        yield [false];
        yield [true];
        yield [STDIN];
    }

    /**
     * @dataProvider getStringValid
     */
    public function test_type_string_valid($expected, $actual)
    {
        $this->assertEquals($expected, f\type_string($actual));
    }

    /**
     * @dataProvider getStringInvalid
     */
    public function test_type_string_invalid($value)
    {
        $this->setExpectedException(
            f\Exception\TypeException::class,
            sprintf('Could not convert "%s" to type "string"', get_debug_type($value))
        );
        f\type_string($value);
    }


    public function getBoolValid()
    {
        yield [false, false];
        yield [false, 0];
        yield [false, '0'];
        yield [true, true];
        yield [true, 1];
        yield [true, '1'];
    }

    public function getBoolInValid()
    {
        yield [null];
        yield ['true'];
        yield ['false'];
        yield [1.2];
    }

    /**
     * @dataProvider getBoolValid
     */
    public function test_type_bool_valid($expected, $actual)
    {
        $this->assertEquals($expected, f\type_bool($actual));
    }

    /**
     * @dataProvider getBoolInValid
     */
    public function test_type_bool_invalid($value)
    {
        $this->setExpectedException(
            f\Exception\TypeException::class,
            sprintf('Could not convert "%s" to type "bool"', get_debug_type($value))
        );
        f\type_bool($value);
    }


    public function test_instance_of()
    {
        $this->assertTrue(f\instance_of(\User::class, new \User([])));
        $this->assertFalse(f\instance_of(\User::class, new \stdClass()));
        $this->assertFalse(f\instance_of(\User::class, 'str'));
        $this->assertFalse(f\instance_of(\User::class, null));
    }

    public function test_type_of()
    {
        $typeOfUser = f\type_of(\User::class);
        $user = new \User([]);
        $this->assertSame($user, $typeOfUser($user));

        $typeOfVal = f\type_of(\Value::class);
        $value = new \Value(null);
        $this->assertSame($value, $typeOfVal($value));

        $this->setExpectedException(
            f\Exception\TypeException::class,
            sprintf('Could not convert "null" to type "User"')
        );
        $typeOfUser(null);
    }

    public function test_type_union()
    {
        $t = f\type_union(f\type_int, f\type_float);
        $t2 = f\type_union($t, f\type_bool);
        $t3 = f\type_union(f\type_int, f\type_float, f\type_bool);

        $this->assertEquals(1, $t(1));
        $this->assertEquals(1, $t(1.00));
        $this->assertEquals(1, $t('1'));

        $this->assertEquals(true, $t2('1'));
        $this->assertEquals(true, $t2(1));
        $this->assertEquals(false, $t2(0));

        $this->assertEquals(3, $t3(3));
        $this->assertEquals(3, $t3(3.00));
        $this->assertEquals(3, $t3('3'));

        $this->assertEquals(true, $t3('1'));
        $this->assertEquals(true, $t3(1));
        $this->assertEquals(false, $t3(0));

        $this->setExpectedException(
            f\Exception\TypeException::class,
            'Could not convert "stdClass" to type "int|float|bool"'
        );
        $t3(new \stdClass());
    }
}
