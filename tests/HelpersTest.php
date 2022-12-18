<?php

namespace Tests\Functional;

use Basko\Functional as f;
use Traversable;

class HelpersTest extends BaseTest
{
    public function test_to_list()
    {
        $this->assertEquals([1, 2, 3], f\to_list(1, 2, 3));
    }

    public function test_concat_and_join()
    {
        $this->assertEquals('foobar', f\concat('foo', 'bar'));
        $concatWithA = f\concat('a');
        $this->assertEquals('ab', $concatWithA('b'));

        $this->assertEquals('foobarbaz', f\concat_all('foo', 'bar', 'baz'));

        $this->assertEquals('1|2|3', f\join('|', [1, 2, 3]));
    }

    public function test_with_iterable()
    {
        $f = function () {
            yield 1;
            yield 2;
            yield 3;
        };

        $iter = $f();

        $this->assertTrue($iter instanceof Traversable);

        $this->assertEquals('1 2 3', f\join(' ', $iter));
    }

    public function test_if_else()
    {
        $if_foo = f\if_else(f\eq('foo'), f\always('bar'), f\always('baz'));
        $this->assertEquals('bar', $if_foo('foo'));
        $this->assertEquals('baz', $if_foo('qux'));

        $ifFoo = f\if_else(f\eq('foo'));
        $ifFooAlwaysBar = $ifFoo(f\always('bar'));
        $ifFooAlwaysBarOtherwiseBaz = $ifFooAlwaysBar(f\always('baz'));
        $this->assertEquals('bar', $ifFooAlwaysBarOtherwiseBaz('foo'));
        $this->assertEquals('baz', $ifFooAlwaysBarOtherwiseBaz('qux'));
    }

    public function test_repeat()
    {
        if (PHP_VERSION_ID < 80000 && !\function_exists('Functional\match')) {
            $mockMethod = 'getMock';
        } else {
            $mockMethod = 'createMock';
        }
        $rep = $this->{$mockMethod}(\Repeated::class);
        $rep->expects($this->exactly(5))->method('someMethod');

        $repeatedSomeMethod = f\repeat([$rep, 'someMethod']);
        $repeatedSomeMethod(5);
    }

    public function test_try_catch()
    {
        $val = f\try_catch(function () {
            return 'vvv';
        }, f\always('val'));

        $val2 = f\try_catch(function () {
            throw new \Exception();
        }, f\always('val'));

        $this->assertSame('vvv', $val());
        $this->assertSame('val', $val2());

        $val = f\try_catch(function () {
            return 'vvv';
        });
        $valAndCatch = $val(f\always('val'));
        $this->assertSame('vvv', $valAndCatch());
    }

    public function test_invoker()
    {
        $user1 = new \User([
            'id' => 1,
            'active' => true,
        ]);
        $user2 = new \User([
            'id' => 2,
            'active' => false,
        ]);

        $activeUsers = f\select(f\invoker('isActive'), [$user1, $user2]);
        $this->assertSame([$user1], $activeUsers);

        $activeUsers = array_filter([$user1, $user2], f\invoker('isActive'));
        $this->assertSame([$user1], $activeUsers);
    }

    public function test_len()
    {
        $this->assertEquals(3, f\len('foo'));
        $this->assertEquals(2, f\len(['a', 'b']));
        $this->assertEquals(2, f\len(new \ArrayIterator(['a', 'b'])));
    }

    public function test_prop()
    {
        $this->assertEquals(99, f\prop(0, [99]));

        $this->assertEquals(100, f\prop('x', ['x' => 100]));

        $object = new \stdClass();
        $object->x = 101;
        $this->assertEquals(101, f\prop('x', $object));

        $xInc = f\compose(f\inc, f\prop('x'));
        $this->assertEquals(4, $xInc(['x' => 3]));
    }

    public function test_prop_path()
    {
        $data = [
            'a' => 1,
            'b' => [
                'c' => 2,
            ],
            'x' => [
                'y' => [
                    'z' => 3,
                ],
            ],
        ];
        $this->assertEquals(2, f\prop_path(['b', 'c'], $data));
        $this->assertEquals(3, f\prop_path(['x', 'y', 'z'], $data));
        $this->assertEquals(null, f\prop_path(['x', 'r'], $data));
    }

    public function test_props()
    {
        $this->assertEquals(
            [1, 2],
            f\props(['x', 'y'], ['x' => 1, 'y' => 2])
        );

        $this->assertEquals(
            [null, 1, 2],
            f\props(['c', 'a', 'b'], ['b' => 2, 'a' => 1])
        );

        $fullName = f\compose(f\join(' '), f\props(['first', 'last']));
        $this->assertEquals('Slava Basko', $fullName(['last' => 'Basko', 'age' => 33, 'first' => 'Slava']));

        $object = new \stdClass();
        $object->last = 'Basko';
        $object->age = 101;
        $object->first = 'Slava';
        $this->assertEquals('Slava Basko', $fullName($object));
    }

    public function test_to_fn()
    {
        $v = new \Value('val');

        $inv = f\to_fn($v, 'concatWith', ['ue']);
        $this->assertEquals('value', $inv());

        $inv = f\to_fn($v, 'concatWith2', ['ue', 1]);
        $this->assertEquals('value1', $inv());
    }

    public function test_memoized()
    {
        $randAndSalt = function ($salt) {
            return rand(1, 100) . $salt;
        };
        $memoizedRandAndSalt = f\memoized($randAndSalt);
        $initial = $memoizedRandAndSalt('x');
        $this->assertEquals($initial, $memoizedRandAndSalt('x'));
        $this->assertEquals($initial, $memoizedRandAndSalt('x'));

        $user = new \User([]);
        $memoizedIsActive = f\memoized([$user, 'isActive']);
        self::assertFalse($memoizedIsActive());
    }

    public function test_assoc()
    {
        $data = ['foo' => 'foo', 'bar' => 'bar'];
        $this->assertEquals(['foo' => 'foo', 'bar' => 42], f\assoc('bar', 42, $data));

        $data2 = (object)['foo' => 'foo', 'bar' => 'bar'];
        $this->assertEquals((object)['foo' => 'foo', 'bar' => 'bar', 'baz' => 42], f\assoc('baz', 42, $data2));

        $foo = f\assoc('foo');
        $foo42 = $foo(42);
        $this->assertEquals(['foo' => 42], $foo42(['foo' => 22]));

        $data2 = (object)['foo' => 'foo', 'bar' => 'bar'];
        $this->assertEquals((object)['foo' => 'foo', 'bar' => 'bar', 'baz' => 42], f\assoc('baz', 42, $data2));
        $this->assertEquals((object)['foo' => 'foo', 'bar' => 'bar'], $data2);

        $user = [
            'first_name' => 'Slava',
            'last_name' => 'Basko',
        ];
        $this->assertEquals(
            $user + ['full_name' => 'Slava Basko'],
            f\assoc('full_name', f\compose(f\join(' '), f\props(['first_name', 'last_name'])), $user)
        );
    }

    public function test_assoc_path()
    {
        $data = ['foo' => 'foo', 'bar' => ['baz' => 41]];
        $this->assertEquals(['foo' => 'foo', 'bar' => ['baz' => 42]], f\assoc_path(['bar', 'baz'], 42, $data));

        $this->assertEquals($data, f\assoc_path([], 42, $data));

        $data = ['foo' => 'foo', 'bar' => ['baz' => 43]];
        $bazTo42 = f\assoc_path(['bar', 'baz'], 42);
        $this->assertEquals(['foo' => 'foo', 'bar' => ['baz' => 42]], $bazTo42($data));
    }

    public function test_pair()
    {
        $fooPart = f\pair('foo');
        $this->assertEquals(['foo', 'bar'], $fooPart('bar'));

        $this->assertEquals(['foo', 'bar'], f\pair('foo', 'bar'));

        $product = [
            'value' => 10,
            'qty' => 2,
        ];
        $prdct = f\apply_to($product);
        $this->assertEquals([10, 2], f\pair($prdct(f\prop('value')), $prdct(f\prop('qty'))));
    }

    public function test_either()
    {
        $gt10 = f\gt(10);
        $f = f\either($gt10, f\is_even);
        $this->assertTrue($f(101));
        $this->assertTrue($f(8));

        $this->assertTrue(f\either($gt10, f\is_even, 101));
        $this->assertTrue(f\either($gt10, f\is_even, 8));

        $tn = f\either(f\prop('tracking_number'), f\prop('internal_tracking_number'), f\prop('carrier_tracking_number'));
        $this->assertEquals('AB123', $tn([
            'tracking_number' => 'AB123',
            'internal_tracking_number' => 'CD456',
            'carrier_tracking_number' => 'EF789',
        ]));
        $this->assertEquals('CD456', $tn([
            'tracking_number' => '',
            'internal_tracking_number' => 'CD456',
            'carrier_tracking_number' => 'EF789',
        ]));
        $this->assertEquals('EF789', $tn([
            'internal_tracking_number' => '',
            'carrier_tracking_number' => 'EF789',
        ]));
        $this->assertEquals(
            'CD456',
            f\either(
                f\prop('tracking_number'),
                f\prop('internal_tracking_number'),
                f\prop('carrier_tracking_number'),
                [
                    'tracking_number' => '',
                    'internal_tracking_number' => 'CD456',
                    'carrier_tracking_number' => 'EF789',
                ]
            )
        );

        $data1 = [
            'tracking_number' => '',
        ];
        $data2 = [
            'tracking_number' => 'AB123',
        ];
        $t_prop = f\thunkify(f\prop);
        $this->assertEquals('AB123', call_user_func(f\either(
            $t_prop('tracking_number', $data1),
            $t_prop('tracking_number', $data2)
        )));

        $this->assertEquals('AB123', call_user_func(f\either(
            f\prop_thunk('tracking_number', $data1),
            f\prop_thunk('tracking_number', $data2)
        )));

        $this->assertNull(f\either());
    }

    public function test_quote()
    {
        $this->assertEquals('"foo"', f\quote('foo'));
        $this->assertEquals('"foo \"bar\""', call_user_func_array(f\compose(f\quote, 'addslashes'), ['foo "bar"']));
        $this->assertEquals('"foo \"bar\""', f\safe_quote('foo "bar"'));
        $this->assertEquals(['"foo"', '"bar"'], f\map(f\quote, ['foo', 'bar']));
    }

    public function test_select_keys()
    {
        $this->assertEquals(
            ['bar' => 2, 'baz' => 3],
            f\select_keys(['bar', 'baz'], ['foo' => 1, 'bar' => 2, 'baz' => 3])
        );
        $this->assertEquals(
            ['bar' => 2, 'baz' => 3],
            f\select_keys(['bar', 'baz'], new \ArrayIterator(['foo' => 1, 'bar' => 2, 'baz' => 3]))
        );
    }

    public function test_omit_keys()
    {
        $f = f\omit_keys(['baz']);
        $this->assertEquals(
            ['foo' => 1, 'bar' => 2],
            $f(['foo' => 1, 'bar' => 2, 'baz' => 3])
        );
        $this->assertEquals(
            ['foo' => 1, 'bar' => 2],
            $f(new \ArrayIterator(['foo' => 1, 'bar' => 2, 'baz' => 3]))
        );
    }

    public function test_find_missing_keys()
    {
        $this->assertEquals(
            ['b'],
            f\find_missing_keys(['a', 'b'], ['a' => 123])
        );

        $findUserMissingFields = f\find_missing_keys(['login', 'email']);
        $this->assertEquals(
            [],
            $findUserMissingFields(['login' => 'admin', 'email' => 'admin@example.com'])
        );
        $this->assertEquals(
            ['email'],
            $findUserMissingFields(['login' => 'admin'])
        );
    }

    public function test_copy()
    {
        $dataObj = new \stdClass();
        $dataObj->a = 1;

        $this->assertNotSame($dataObj, f\copy($dataObj));
    }

    public function test_pick_random_value()
    {
        $treasure = [
            'sword',
            'gold',
            'ring',
            'jewel',
        ];

        $this->assertTrue(in_array(f\pick_random_value($treasure), $treasure));
        $this->assertTrue(in_array(f\pick_random_value(new \ArrayIterator($treasure)), $treasure));
    }

    public function test_map_keys()
    {
        $f = f\map_keys('strtoupper');
        $f2 = $f(['shipper_country', 'consignee_country']);

        $obj = $f2([
            'shipper_country' => 'nl',
            'consignee_country' => 'ca',
            'name' => 'John',
        ]);

        $this->assertEquals('NL', f\prop('shipper_country', $obj));
        $this->assertEquals('CA', f\prop('consignee_country', $obj));
        $this->assertEquals('John', f\prop('name', $obj));
    }

    public function test_call()
    {
        $func = function ($one, $two, $three) {
            $this->assertEquals(1, $one);
            $this->assertEquals(2, $two);
            $this->assertEquals(3, $three);

            return true;
        };

        $this->assertTrue(f\call($func, 1, 2, 3));
        $this->assertTrue(f\call($func, [1, 2, 3]));

        $fP = f\call($func);
        $this->assertTrue($fP(1, 2, 3));
        $this->assertTrue($fP([1, 2, 3]));
    }
}
