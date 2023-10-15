<?php

namespace Tests\Functional;

use Basko\Functional as f;
use Basko\Functional\Exception\InvalidArgumentException;

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
        yield ['1, 2, 3', [1, 2, 3]];
    }

    public function getStringInvalid()
    {
        yield [1.0];
        yield [1.23];
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


    public function test_is_type_of()
    {
        $this->assertTrue(f\is_type_of(\User::class, new \User([])));
        $this->assertFalse(f\is_type_of(\User::class, new \stdClass()));
        $this->assertFalse(f\is_type_of(\User::class, 'str'));
        $this->assertFalse(f\is_type_of(\User::class, null));
    }

    public function test_type_of()
    {
        $typeOfUser = f\type_of(\User::class);
        $user = new \User([]);
        $this->assertSame($user, $typeOfUser($user));

        $typeOfVal = f\type_of(\Value::class);
        $value = new \Value(null);
        $this->assertSame($value, $typeOfVal($value));
    }

    public function test_type_of_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'type_of() expects parameter 2 to be object, string given'
        );
        f\type_of(\User::class, 'str');
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

    public function test_type_union_fail()
    {
        $t = f\type_union(f\type_float, function ($value) {
            throw new \Exception();
        });

        $this->setExpectedException(
            f\Exception\TypeException::class,
            'Basko\Functional\type_union() fail and there no \Basko\Functional\Exception\TypeException exception was thrown'
        );
        $t('some');
    }

    public function test_type_union_fail_2()
    {
        $t = f\type_union(f\type_float, function ($value) {
            throw new f\Exception\TypeException();
        });

        $this->setExpectedException(
            f\Exception\TypeException::class,
            'One of type in Basko\Functional\type_union() fail and TypeException::forValue() never called'
        );
        $t('some');
    }

    public function test_type_positive_int_valid()
    {
        $this->assertEquals(2, f\type_positive_int(2));
        $this->assertEquals(2, f\type_positive_int('2'));
    }

    public function test_type_positive_int_invalid()
    {
        $this->setExpectedException(
            f\Exception\TypeException::class,
            sprintf('Could not convert "%s" to type "positive_int"', get_debug_type(-1))
        );
        f\type_positive_int(-1);
    }

    public function test_type_positive_int_zero_invalid()
    {
        $this->setExpectedException(
            f\Exception\TypeException::class,
            sprintf('Could not convert "%s" to type "positive_int"', get_debug_type(0))
        );
        f\type_positive_int(0);
    }

    public function test_type_array_key()
    {
        $this->assertEquals(1, f\type_array_key(1));
        $this->assertEquals('some_key', f\type_array_key('some_key'));
    }

    public function test_type_list()
    {
        $intList = f\type_list(f\type_int);
        $this->assertEquals([1, 2], $intList([1, 2]));
        $this->assertEquals([1, 2], $intList([1, '2']));
        $this->assertEquals([1, 2], $intList([1, 2.0]));

        $u1 = new \User([]);
        $u2 = new \User([]);
        $this->assertEquals([$u1, $u2], f\type_list(f\type_of(\User::class), [$u1, $u2]));
    }

    public function test_type_list_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'type_list() expects parameter 2 to be array or instance of Traversable, NULL given'
        );
        f\type_list(f\type_int, null);
    }

    public function test_type_list_false()
    {
        $this->setExpectedException(
            f\Exception\TypeException::class,
            'List element \'1\': Could not convert "string" to type "int"'
        );
        f\type_list(f\type_int, [1, 'two']);
    }

    public function test_type_array()
    {
        $t = f\type_array(f\type_array_key);
        $t2 = $t(f\type_int);

        $this->assertEquals(['one' => 1], $t2(['one' => 1]));
        $this->assertEquals(['one' => 1], $t2(['one' => 1.0]));

        $specificMap = [
            'user1' => new \User([]),
            'user2' => new \User([]),
        ];
        $this->assertEquals(
            $specificMap,
            f\type_array(
                f\type_array_key,
                f\type_of(\User::class),
                $specificMap
            )
        );
    }

    public function test_type_shape()
    {
        $parcelShape = f\type_shape([
            'description' => f\type_string,
            'value' => f\type_union(f\type_int, f\type_float),
        ]);

        $parcel = [
            'description' => 'some goods',
            'value' => 200,
        ];

        $this->assertEquals($parcel, $parcelShape($parcel));
    }

    public function test_type_shape_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'type_shape() expects parameter 2 to be array or instance of ArrayAccess, NULL given'
        );
        f\type_shape([], null);
    }

    public function test_type_shape_fail2()
    {
        $this->setExpectedException(
            f\Exception\TypeException::class,
            'Exception on shape element \'description\': error on description'
        );

        $parcelShape = f\type_shape([
            'description' => function($v) {
                throw new \Exception('error on description');
            },
            'value' => f\type_union(f\type_int, f\type_float),
        ]);

        $parcel = [
            'description' => 'some goods',
            'value' => 200,
        ];

        $this->assertEquals($parcel, $parcelShape($parcel));
    }

    public function test_type_shape_complex()
    {
        $parcelShape = f\type_shape([
            'description' => f\type_string,
            'value' => f\type_positive_int,
            'dimensions' => f\type_shape([
                'width' => f\type_union(f\type_int, f\type_float),
                'height' => f\type_union(f\type_int, f\type_float),
            ]),
            'products' => f\type_list(f\type_shape([
                'description' => f\type_string,
                'qty' => f\type_positive_int,
                'price' => f\type_union(f\type_int, f\type_float),
            ]))
        ]);

        $parcel = [
            'description' => 'some goods',
            'value' => 200,
            'dimensions' => [
                'width' => 0.1,
                'height' => 2.4,
            ],
            'products' => [
                [
                    'description' => 'product 1',
                    'qty' => 2,
                    'price' => 50,
                ],
                [
                    'description' => 'product 2',
                    'qty' => 2,
                    'price' => 50,
                ],
            ],
            'additional' => 'some additional element value tha should not present in result'
        ];

        $comparableParcel = $parcel;
        array_pop($comparableParcel);
        $this->assertEquals($comparableParcel, $parcelShape($parcel));
    }

    public function test_type_shape_complex_fail()
    {
        $parcelShape = f\type_shape([
            'products' => f\type_list(f\type_shape([
                'description' => f\type_string,
                'qty' => f\type_positive_int,
                'price' => f\type_union(f\type_int, f\type_float),
            ]))
        ]);

        $parcel = [
            'products' => [
                [
                    'description' => 'product 1',
                    'qty' => 'aaa',
                    'price' => 50,
                ]
            ]
        ];

        $this->setExpectedException(
            f\Exception\TypeException::class,
            'Shape element \'products\': List element \'0\': Shape element \'qty\': Could not convert "string" to type "int"'
        );
        $parcelShape($parcel);
    }
}
