<?php

namespace Basko\FunctionalTest\TestCase;

use Basko\Functional as f;
use Basko\FunctionalTest\Helpers\ClassWithPrivateMethod;
use Basko\FunctionalTest\Helpers\Dto;
use Basko\FunctionalTest\Helpers\Repeated;
use Basko\FunctionalTest\Helpers\User;
use Basko\FunctionalTest\Helpers\Value;
use Traversable;

class HelpersTest extends BaseTest
{
    public function test_to_list()
    {
        $this->assertEquals([1, 2, 3], f\to_list(1, 2, 3));
        $this->assertEquals(['Slava', 'Basko'], f\to_list('Slava,Basko'));
        $this->assertEquals(['Slava', 'Basko'], f\to_list('Slava, Basko'));
    }

    public function test_concat_and_join()
    {
        $this->assertEquals('foobar', f\concat('foo', 'bar'));
        $concatWithA = f\concat('a');
        $this->assertEquals('ab', $concatWithA('b'));

        $this->assertEquals('foobarbaz', f\concat_all('foo', 'bar', 'baz'));

        $this->assertEquals('1|2|3', f\join('|', [1, 2, 3]));
    }

    public function test_concat_fail()
    {
        $this->setExpectedException(
            f\Exception\InvalidArgumentException::class,
            'Basko\Functional\concat() expects parameter 2 to be string, NULL given'
        );
        f\concat('bar', null);
    }

    public function test_concat_fail2()
    {
        $f = f\concat('bar');
        $this->setExpectedException(
            f\Exception\InvalidArgumentException::class,
            'Basko\Functional\concat() expects parameter 2 to be string, NULL given'
        );
        $f(null);
    }

    public function test_join_fai()
    {
        $this->setExpectedException(
            f\Exception\InvalidArgumentException::class,
            'Basko\Functional\join() expects parameter 2 to be array or instance of Traversable, NULL given'
        );
        f\join('|', null);
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
        $rep = $this->mock(Repeated::class);
        $rep->expects($this->exactly(5))->method('someMethod');

        $repeatedSomeMethod = f\repeat([$rep, 'someMethod']);
        $repeatedSomeMethod(5);

        $this->setExpectedException(
            f\Exception\InvalidArgumentException::class,
            'Callable created by Basko\Functional\repeat() expects parameter 1 to be integer, string given'
        );
        $repeatedSomeMethod('some');
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

    public function test_try_catch_fail()
    {
        $this->setExpectedException(
            f\Exception\InvalidArgumentException::class,
            'try_catch() expects parameter 2 to be a valid callback, array, string, closure or functor, NULL given'
        );
        f\try_catch(f\F, null);
    }

    public function test_invoker()
    {
        $user1 = new User([
            'id' => 1,
            'active' => true,
        ]);
        $user2 = new User([
            'id' => 2,
            'active' => false,
        ]);

        $activeUsers = f\select(f\invoker('isActive'), [$user1, $user2]);
        $this->assertSame([$user1], $activeUsers);

        $activeUsers = array_filter([$user1, $user2], f\invoker('isActive'));
        $this->assertSame([$user1], $activeUsers);
    }

    public function test_invoker_fail()
    {
        $this->setExpectedException(
            f\Exception\InvalidArgumentException::class,
            'Function created by Basko\Functional\invoker() expects parameter 1 to be object, integer given'
        );
        $f = f\invoker('isActive');
        call_user_func($f, 1);
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

        $object = new \ArrayObject([
            'x' => 101,
        ]);
        $this->assertEquals(101, f\prop('x', $object));

        $this->assertEquals(null, f\prop('x', null));
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

        $this->assertEquals(null, f\prop_path(['x', 'y', 'z'], null));
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

        $this->assertEquals([null, null], f\props(['x', 'y'], null));
    }

    public function test_to_fn()
    {
        $v = new Value('val');

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

        $user = new User([]);
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

    public function test_assoc_fail()
    {
        $this->setExpectedException(
            f\Exception\InvalidArgumentException::class,
            'Basko\Functional\assoc() expects parameter 3 to be array or instance of Traversable, NULL given'
        );
        f\assoc('k', 42, null);
    }

    public function test_assoc_path()
    {
        $data = ['foo' => 'foo', 'bar' => ['baz' => 41]];
        $this->assertEquals(['foo' => 'foo', 'bar' => ['baz' => 42]], f\assoc_path(['bar', 'baz'], 42, $data));

        $this->assertEquals($data, f\assoc_path([], 42, $data));

        $data = ['foo' => 'foo', 'bar' => ['baz' => 43]];
        $bazTo42 = f\assoc_path(['bar', 'baz'], 42);
        $this->assertEquals(['foo' => 'foo', 'bar' => ['baz' => 42]], $bazTo42($data));

        $a = new \stdClass();
        $a->a = 1;
        $a->b = 2;

        $b = new \stdClass();
        $b->a = 3;
        $b->b = $a;

        $c = f\assoc_path(['b', 'a'], 9, $b);
        $this->assertEquals(9, $c->b->a);
    }

    public function test_assoc_path_fail()
    {
        $this->setExpectedException(
            f\Exception\InvalidArgumentException::class,
            'Basko\Functional\assoc_path() expects parameter 3 to be array or instance of Traversable, NULL given'
        );
        f\assoc_path(['k'], 42, null);
    }

    public function test_assoc_element()
    {
        $this->assertEquals([999, 20, 30], f\assoc_element(1, 999, [10, 20, 30]));
        $this->assertEquals([10, 999, 30], f\assoc_element(2, 999, [10, 20, 30]));
        $this->assertEquals([10, 20, 999], f\assoc_element(-1, 999, [10, 20, 30]));
    }

    public function test_assoc_element_fail_object()
    {
        $this->setExpectedException(
            f\Exception\InvalidArgumentException::class,
            'assoc_element() expects parameter 3 to be array or Iterator'
        );
        f\assoc_element(1, 42, new \stdClass());
    }

    public function test_assoc_element_fail()
    {
        $this->setExpectedException(
            f\Exception\InvalidArgumentException::class,
            'assoc_element() expects parameter 1 to be integer, string given'
        );
        f\assoc_element('1', 42, []);
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

        $this->assertEquals([1, null], f\pair(1, null));
    }

    public function test_either()
    {
        $gt10 = f\partial_r(f\gt, 10);
        $f = f\either($gt10, f\is_even);
        $this->assertTrue($f(101));
        $this->assertTrue($f(8));

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

        $emptyTest = [
            'some_prop' => 'some_value',
        ];
        $this->assertEquals([], call_user_func(f\either(
            f\prop('non_existent_prop'),
            f\always([])
        ), $emptyTest));
    }

    public function test_either_fail()
    {
        $this->setExpectedException(
            f\Exception\InvalidArgumentException::class,
            'either() expects "callable ...$functions"'
        );
        f\either();
    }

    public function test_either_strict()
    {
        $tn = f\either_strict(f\prop('tracking_number'), f\always([]));
        $this->assertSame('', $tn([
            'tracking_number' => '',
        ]));
        $this->assertSame([], $tn([
            'unknown_field' => '',
        ]));
    }

    public function test_quote()
    {
        $this->assertEquals('"foo"', f\quote('foo'));
        $this->assertEquals('"foo \"bar\""', call_user_func_array(f\compose(f\quote, 'addslashes'), ['foo "bar"']));
        $this->assertEquals('"foo \"bar\""', f\safe_quote('foo "bar"'));
        $this->assertEquals(['"foo"', '"bar"'], f\map(f\quote, ['foo', 'bar']));
    }

    public function test_only_keys()
    {
        $this->assertEquals(
            ['bar' => 2, 'baz' => 3],
            f\only_keys(['bar', 'baz'], ['foo' => 1, 'bar' => 2, 'baz' => 3])
        );
        $this->assertEquals(
            ['bar' => 2, 'baz' => 3],
            f\only_keys(['bar', 'baz'], new \ArrayIterator(['foo' => 1, 'bar' => 2, 'baz' => 3]))
        );
    }

    public function test_only_keys_fail()
    {
        $this->setExpectedException(
            f\Exception\InvalidArgumentException::class,
            'Basko\Functional\only_keys() expects parameter 2 to be array or instance of Traversable, NULL given'
        );
        f\only_keys(['bar', 'baz'], null);
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

    public function test_omit_keys_fail()
    {
        $this->setExpectedException(
            f\Exception\InvalidArgumentException::class,
            'Basko\Functional\omit_keys() expects parameter 2 to be array or instance of Traversable, NULL given'
        );
        f\omit_keys(['bar', 'baz'], null);
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

    public function test_find_missing_keys_fail()
    {
        $this->setExpectedException(
            f\Exception\InvalidArgumentException::class,
            'find_missing_keys() expects parameter 2 to be array or instance of Traversable, NULL given'
        );
        f\find_missing_keys(['bar', 'baz'], null);
    }

    public function test_copy()
    {
        $dataObj = new \stdClass();
        $dataObj->a = 1;

        $this->assertNotSame($dataObj, f\cp($dataObj));

        global $custom_clone_flag;
        $this->assertFalse($custom_clone_flag);
        define('CLONE_FUNCTION', 'custom_clone');
        $this->assertNotSame($dataObj, f\cp($dataObj));
        $this->assertTrue($custom_clone_flag);
        $custom_clone_flag = false;
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

    public function test_map_keys_fail()
    {
        $this->setExpectedException(
            f\Exception\InvalidArgumentException::class,
            'map_keys() expects parameter 3 to be array or instance of Traversable, NULL given'
        );
        f\map_keys('strtoupper', ['bar'], null);
    }

    public function test_map_elements()
    {
        $f = f\map_elements('strtoupper');
        $f2 = $f([1, -1]);

        $obj = $f2([
            'Slava',
            'Slava Limited LDT',
            '10th ave',
            'vancouver',
            'ca',
        ]);

        $this->assertEquals('SLAVA', $obj[0]);
        $this->assertEquals('CA', $obj[4]);
        $this->assertEquals($obj[4], f\map_elements('strtoupper', [4], $obj)[4]);
        $this->assertEquals('Vancouver', f\map_elements('ucfirst', [4], $obj)[3]);
    }

    public function test_map_elements_fail()
    {
        $this->setExpectedException(
            f\Exception\InvalidArgumentException::class,
            'map_elements() expects parameter 3 to be array or instance of ArrayAccess, NULL given'
        );
        f\map_elements('strtoupper', ['bar'], null);
    }

    public function test_flip_values()
    {
        $data = [
            'key1' => 'val1',
            'key2' => 'val2',
        ];
        $data2 = (object)$data;

        $f = f\flip_values('key1');
        $flipAB = $f('key2');

        $this->assertEquals(
            [
                'key1' => 'val2',
                'key2' => 'val1',
            ],
            $flipAB($data)
        );

        $this->assertEquals('val1', $data2->key1);
        $newData2 = $flipAB($data2);
        $this->assertEquals('val2', $newData2->key1);
        $this->assertEquals('val1', $newData2->key2);
    }

    public function test_flip_values_fail()
    {
        $this->setExpectedException(
            f\Exception\InvalidArgumentException::class,
            'Basko\Functional\flip_values() expects parameter 1 to be string, NULL given'
        );
        f\flip_values(null, 'key', []);
    }

    public function test_flip_values_fail2()
    {
        $this->setExpectedException(
            f\Exception\InvalidArgumentException::class,
            'flip_values() expects parameter 3 to be array or instance of Traversable, NULL given'
        );
        f\flip_values('keyA', 'key', null);
    }

    public function test_is_nth()
    {
        $checkStr = '';

        for ($i = 1; $i <= 20; $i++) {
            if (f\is_nth(10, $i)) {
                $checkStr .= "This is the 10th iteration ($i);";
            }
        }

        $this->assertEquals(
            'This is the 10th iteration (10);This is the 10th iteration (20);',
            $checkStr
        );
    }

    public function test_is_nth_fail()
    {
        $this->setExpectedException(
            f\Exception\InvalidArgumentException::class,
            'is_nth() expects parameter 2 to be integer, NULL given'
        );
        f\is_nth(20, null);
    }

    public function test_publish()
    {
        $privateMethodPublish = f\publish('privateMethod');
        $object = new ClassWithPrivateMethod();
        $f = $privateMethodPublish($object);
        $this->assertEquals('private', call_user_func($f));
    }

    public function test_publish_fail()
    {
        $this->setExpectedException(
            f\Exception\InvalidArgumentException::class,
            'publish() expects parameter 2 to be object, NULL given'
        );
        f\publish('privateMethod', null);
    }

    public function test_combine()
    {
        $this->assertEquals(
            [
                'Slava' => 'Basko',
                'John' => 'Doe',
            ],
            f\combine('name', 'last_name', [
                [
                    'name' => 'Slava',
                    'last_name' => 'Basko',
                ],
                [
                    'name' => 'John',
                    'last_name' => 'Doe',
                ],
            ])
        );
    }

    public function test_combine_fail()
    {
        $this->setExpectedException(
            f\Exception\InvalidArgumentException::class,
            'Basko\Functional\combine() expects parameter 3 to be array or instance of Traversable, NULL given'
        );
        f\combine('name', 'last_name', null);
    }

    public function test_construct()
    {
        $dto = f\construct(Dto::class);
        $this->assertNull($dto->value1);
        $this->assertNull($dto->value2);
    }

    public function test_construct_with_args()
    {
        $user = f\construct_with_args(User::class, ['first_name' => 'Slava', 'last_name' => 'Basko']);
        $this->assertEquals('Slava', $user->first_name);
        $this->assertEquals('Slava Basko', $user->getFullName(' '));

        $dto = f\construct_with_args(Dto::class, 1, 2);
        $this->assertEquals(1, $dto->value1);
        $this->assertEquals(2, $dto->value2);
    }
}
