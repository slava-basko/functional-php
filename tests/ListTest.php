<?php

namespace Tests\Functional;

use Basko\Functional\Exception\InvalidArgumentException;
use Basko\Functional as f;

class ListTest extends BaseTest
{
    private function getUsersData()
    {
        return [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
            ],
            [
                'first_name' => 'Mark',
                'last_name' => 'Bower',
            ],
        ];
    }

    private function getUsersObjects()
    {
        return array_map(function ($data) {
            return new \User($data);
        }, static::getUsersData());
    }

    public function test_pluck()
    {
        $this->assertEquals(['John', 'Mark'], f\pluck('first_name', static::getUsersData()));

        $lastNamePluck = f\pluck('last_name');
        $this->assertEquals(['Doe', 'Bower'], $lastNamePluck(static::getUsersData()));

        $this->assertEquals(['John', 'Mark'], f\pluck('first_name', static::getUsersObjects()));
    }

    public function test_pluck_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Basko\Functional\pluck() expects parameter 2 to be array or instance of Traversable, NULL given'
        );
        f\pluck('first_name', null);
    }

    public function test_head_and_tail()
    {
        $this->assertNull(f\head([]));
        $this->assertNull(f\head_by(function () {
        }, []));
        $nullFirst = f\head_by(function () {
        });
        $this->assertNull($nullFirst([]));

        $this->assertEquals([], f\tail([]));
        $this->assertEquals([], f\tail_by(function () {
        }, []));
        $tailFirst = f\tail_by(function () {
        });
        $this->assertEquals([], $tailFirst([]));

        $students = [
            ['name' => 'jack', 'score' => 1],
            ['name' => 'mark', 'score' => 9],
            ['name' => 'john', 'score' => 1],
        ];
        $this->assertEquals(['name' => 'jack', 'score' => 1], f\head($students));
        $this->assertEquals(['name' => 'mark', 'score' => 9], f\head_by(function ($student) {
            return $student['score'] >= 9;
        }, $students));

        $this->assertEquals([
            1 => ['name' => 'mark', 'score' => 9],
            2 => ['name' => 'john', 'score' => 1],
        ], f\tail($students));
        $this->assertEquals(
            [
                1 => ['name' => 'mark', 'score' => 9],
            ],
            f\tail_by(
                f\compose(f\partial_r(f\gt, [8]), f\prop('score')),
                $students
            )
        );
    }

    public function test_head_by_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Basko\Functional\head_by() expects parameter 2 to be array or instance of Traversable, integer given'
        );
        f\head_by(f\T, 1);
    }

    public function test_tail_by_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Basko\Functional\tail_by() expects parameter 2 to be array or instance of Traversable, integer given'
        );
        f\tail_by(f\T, 1);
    }

    public function test_select()
    {
        $user1 = new \User([
            'id' => 1,
            'active' => true,
        ]);
        $user2 = new \User([
            'id' => 2,
            'active' => false,
        ]);

        $fnFilter = function ($user, $key, $collection) {
            return $user->isActive();
        };

        $activeUsersSelector = f\select($fnFilter);
        $this->assertSame([$user1], $activeUsersSelector([$user1, $user2]));
    }

    public function test_select_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Basko\Functional\select() expects parameter 2 to be array or instance of Traversable, NULL given'
        );
        f\select(f\T, null);
    }

    public function test_reject()
    {
        $user1 = new \User([
            'id' => 1,
            'active' => true,
        ]);
        $user2 = new \User([
            'id' => 2,
            'active' => false,
        ]);

        $fnFilter = function ($user, $key, $collection) {
            return $user->isActive();
        };

        $inactiveUsers = f\reject($fnFilter, [$user1, $user2]);
        $this->assertSame([1 => $user2], $inactiveUsers);

        $inactiveUsersSelector = f\reject($fnFilter);
        $this->assertSame([1 => $user2], $inactiveUsersSelector([$user1, $user2]));
    }

    public function test_reject_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Basko\Functional\reject() expects parameter 2 to be array or instance of Traversable, NULL given'
        );
        f\reject(f\T, null);
    }

    public function test_contains()
    {
        $this->assertFalse(f\contains('foo', []));
        $this->assertFalse(f\contains('foo', new \ArrayIterator()));

        $this->assertTrue(f\contains('foo', ['foo', 'bar']));
        $this->assertTrue(f\contains('foo', new \ArrayIterator(['foo', 'bar'])));

        $this->assertFalse(f\contains('foo', 'bar'));
        $this->assertTrue(f\contains('foo', 'foo and bar'));
        $this->assertTrue(f\contains('o', 'foo'));

        $containsSome = f\contains('some');
        $this->assertTrue($containsSome('some story'));

        $this->assertTrue(f\contains('', 'abc'));
        $this->assertTrue(f\contains('국', '한국어'));
        $this->assertTrue(f\contains('', '한국어'));
        $this->assertFalse(f\contains('d', 'abc'));
        $this->assertFalse(f\contains('à', 'DÉJÀ'));
        $this->assertFalse(f\contains('à', 'a'));
        $this->assertTrue(f\contains('🙌', '🙌🎉✨🚀'));
    }

    public function test_contains_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Basko\Functional\contains() expects parameter 2 to be string or list, NULL given'
        );
        f\contains('foo', null);
    }

    public function test_take()
    {
        $t = f\take(2);
        $t2 = f\take_r(2);
        $this->assertEquals([1, 2], $t([1, 2, 3]));
        $this->assertEquals([1 => 2, 2 => 3], $t2([1, 2, 3]));
        $this->assertEquals([1, 2], f\take(2, [1, 2, 3]));
        $this->assertEquals([1 => 2, 2 => 3], f\take_r(2, [1, 2, 3]));
        $this->assertEquals([1 => 'b', 2 => 'c'], f\take_r(2, ['a', 'b', 'c']));

        $this->assertEquals('Slav', f\take(4, 'Slava'));
        $this->assertEquals('Slava', f\take(99, 'Slava'));
        $this->assertEquals('lava', f\take_r(4, 'Slava'));
    }

    public function test_take_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Basko\Functional\take() expects parameter 2 to be string or list, NULL given'
        );
        f\take(2, null);
    }

    public function test_take_r_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Basko\Functional\take_r() expects parameter 2 to be string or list, NULL given'
        );
        f\take_r(2, null);
    }

    public function test_nth()
    {
        $nth = f\nth(1);
        $this->assertEquals('foo', $nth(['foo', 'bar', 'baz', 'qwe']));

        $this->assertEquals(null, f\nth(999, ['foo', 'bar', 'baz', 'qwe']));
        $this->assertEquals('qwe', f\nth(-1, ['foo', 'bar', 'baz', 'qwe']));
        $this->assertEquals(null, f\nth(-999, ['foo', 'bar', 'baz', 'qwe']));

        $this->assertEquals('S', f\nth(1, 'Slava'));
        $this->assertEquals('l', f\nth(2, 'Slava'));
        $this->assertEquals('v', f\nth(-2, 'Slava'));
        $this->assertEquals(null, f\nth(999, 'Slava'));
        $this->assertEquals(null, f\nth(-999, 'Slava'));

        $this->assertEquals(null, f\nth(0, ['foo', 'bar', 'baz', 'qwe']));
    }

    public function test_nth_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Basko\Functional\nth() expects parameter 2 to be string or list, NULL given'
        );
        f\nth(1, null);
    }

    public function test_group()
    {
        $users = [
            [
                'name' => 'john',
                'type' => 'admin',
            ],
            [
                'name' => 'mark',
                'type' => 'user',
            ],
            [
                'name' => 'bill',
                'type' => 'user',
            ],
            [
                'name' => 'jack',
                'type' => 'anonymous',
            ],
        ];

        $groupByTypeUser = f\group(f\prop('type'));
        $this->assertEquals([
            'admin' => [
                [
                    'name' => 'john',
                    'type' => 'admin',
                ],
            ],
            'user' => [
                1 => [
                    'name' => 'mark',
                    'type' => 'user',
                ],
                2 => [
                    'name' => 'bill',
                    'type' => 'user',
                ],
            ],
            'anonymous' => [
                3 => [
                    'name' => 'jack',
                    'type' => 'anonymous',
                ],
            ],
        ], $groupByTypeUser($users));
    }

    public function test_group_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Basko\Functional\group() expects parameter 2 to be array or instance of Traversable, NULL given'
        );
        f\group(f\T, null);
    }

    public function test_partition()
    {
        $students = [
            1 => [
                'name' => 'john',
                'score' => 2,
            ],
            2 => [
                'name' => 'mark',
                'score' => 8,
            ],
            3 => [
                'name' => 'bill',
                'score' => 10,
            ],
            4 => [
                'name' => 'jack',
                'score' => 10,
            ],
        ];

        $gt6 = f\partial_r(f\gt, [6]);
        $lt9 = f\partial_r(f\lt, [9]);
        $gte9 = f\partial_r(f\gte, [9]);
        $f = f\partition([
            f\compose($gte9, f\prop('score')),
            f\compose(f\both($gt6, $lt9), f\prop('score')),
        ]);
        list($best, $good_students, $losers) = $f($students);

        $this->assertEquals([
            3 => [
                'name' => 'bill',
                'score' => 10,
            ],
            4 => [
                'name' => 'jack',
                'score' => 10,
            ],
        ], $best);
        $this->assertEquals([
            2 => [
                'name' => 'mark',
                'score' => 8,
            ],
        ], $good_students);
        $this->assertEquals([
            1 => [
                'name' => 'john',
                'score' => 2,
            ],
        ], $losers);
    }

    public function test_partition_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Basko\Functional\partition() expects parameter 2 to be array or instance of Traversable, NULL given'
        );
        f\partition([f\T, f\F], null);
    }

    public function test_flatten()
    {
        $this->assertEquals(
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
            f\flatten([1, 2, [3, 4], 5, [6, [7, 8, [9, [10, 11], 12]]]])
        );

        $this->assertSame(
            [1, "2", "3", 5],
            f\flatten([1 => 1, 'foo' => '2', 3 => '3', ['foo' => 5]])
        );

        $this->assertEquals([new \stdClass()], f\flatten([[new \stdClass()]]));
        $this->assertSame([null, null], f\flatten([[null], null]));
    }

    public function test_flatten_with_keys()
    {
        $this->assertEquals(
            [
                'title' => 'Some title',
                'body' => 'content',
                'comments.0.author' => 'user1',
                'comments.0.body' => 'comment body 1',
                'comments.1.author' => 'user2',
                'comments.1.body' => 'comment body 2',
            ],
            f\flatten_with_keys([
                'title' => 'Some title',
                'body' => 'content',
                'comments' => [
                    [
                        'author' => 'user1',
                        'body' => 'comment body 1',
                    ],
                    [
                        'author' => 'user2',
                        'body' => 'comment body 2',
                    ],
                ],
            ])
        );
    }

    public function test_intersperse()
    {
        $a_intersperse = f\intersperse('a');
        $this->assertEquals(
            ['b', 'a', 'n', 'a', 'n', 'a', 's'],
            $a_intersperse(['b', 'n', 'n', 's'])
        );
    }

    public function test_intersperse_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Basko\Functional\intersperse() expects parameter 2 to be array or instance of Traversable, NULL given'
        );
        f\intersperse('a', null);
    }

    public function test_sort()
    {
        $list = ['cat', 'bear', 'aardvark'];
        $list_iterator = new \ArrayIterator($list);
        $hash = ['c' => 'cat', 'b' => 'bear', 'a' => 'aardvark'];
        $hash_iterator = new \ArrayIterator($hash);
        $sort_callback = function ($left, $right, $collection) {
            InvalidArgumentException::assertList($collection, __FUNCTION__, 3);

            return strcmp($left, $right);
        };

        $this->assertSame([2 => 'aardvark', 1 => 'bear', 0 => 'cat'], f\sort($sort_callback, $list));
        $this->assertSame([2 => 'aardvark', 1 => 'bear', 0 => 'cat'], f\sort(f\binary('strcmp'), $list));
        $this->assertSame([2 => 'aardvark', 1 => 'bear', 0 => 'cat'], f\sort($sort_callback, $list_iterator));
        $this->assertSame(['a' => 'aardvark', 'b' => 'bear', 'c' => 'cat'], f\sort($sort_callback, $hash));
        $this->assertSame(['a' => 'aardvark', 'b' => 'bear', 'c' => 'cat'], f\sort($sort_callback, $hash_iterator));
    }

    public function test_sort_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Basko\Functional\sort() expects parameter 2 to be array or instance of Traversable, NULL given'
        );
        f\sort(f\T, null);
    }

    public function test_append()
    {
        $arr = ['one', 'two'];

        $appendThree = f\append('three');
        $arr2 = $appendThree($arr);
        $this->assertNotEquals($arr, $arr2);
        $this->assertEquals(['one', 'two', 'three'], $arr2);

        $arr3 = f\append('three', new \ArrayIterator($arr));
        $this->assertEquals(['one', 'two', 'three'], $arr3);
    }

    public function test_append_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Basko\Functional\append() expects parameter 2 to be array or instance of Traversable, NULL given'
        );
        f\append('element', null);
    }

    public function test_prepend()
    {
        $arr = ['one', 'two'];

        $prependThree = f\prepend('three');
        $arr2 = $prependThree($arr);
        $this->assertNotEquals($arr, $arr2);
        $this->assertEquals(['three', 'one', 'two'], $arr2);

        $arr3 = f\prepend('three', new \ArrayIterator($arr));
        $this->assertEquals(['three', 'one', 'two'], $arr3);
    }

    public function test_prepend_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Basko\Functional\prepend() expects parameter 2 to be array or instance of Traversable, NULL given'
        );
        f\prepend('element', null);
    }

    public function test_comparator()
    {
        $this->assertEquals(
            [1, 1, 2, 3, 5, 8],
            array_values(f\sort(f\comparator(function ($a, $b) {
                return $a < $b;
            }), [3, 1, 8, 1, 2, 5]))
        );

        $byAge = f\comparator(function ($a, $b) {
            return f\prop('age', $a) < f\prop('age', $b);
        });
        $people = [
            ['name' => 'Emma', 'age' => 70],
            ['name' => 'Peter', 'age' => 78],
            ['name' => 'Mikhail', 'age' => 62],
        ];

        $peopleByYoungestFirst = f\sort($byAge, $people);
        $this->assertEquals(
            [
                2 => ['name' => 'Mikhail', 'age' => 62],
                0 => ['name' => 'Emma', 'age' => 70],
                1 => ['name' => 'Peter', 'age' => 78],
            ],
            $peopleByYoungestFirst
        );
    }

    public function test_ascend()
    {
        $byAge = f\ascend(f\prop('age'));
        $people = [
            ['name' => 'Emma', 'age' => 70],
            ['name' => 'Peter', 'age' => 78],
            ['name' => 'Mikhail', 'age' => 62],
            ['name' => 'Slava', 'age' => 33],
        ];
        $peopleByYoungestFirst = f\sort($byAge, $people);
        $this->assertEquals(
            [
                3 => ['name' => 'Slava', 'age' => 33],
                2 => ['name' => 'Mikhail', 'age' => 62],
                0 => ['name' => 'Emma', 'age' => 70],
                1 => ['name' => 'Peter', 'age' => 78],
            ],
            $peopleByYoungestFirst
        );

        $sortAscByAge = f\sort(f\ascend(f\prop('age')));
        $this->assertEquals(
            [
                2 => ['name' => 'Mikhail', 'age' => 62],
                0 => ['name' => 'Emma', 'age' => 70],
                1 => ['name' => 'Peter', 'age' => 78],
                3 => ['name' => 'Slava', 'age' => 33],
            ],
            $sortAscByAge($people)
        );
    }

    public function test_descend()
    {
        $byAge = f\descend(f\prop('age'));
        $people = [
            ['name' => 'Emma', 'age' => 70],
            ['name' => 'Peter', 'age' => 78],
            ['name' => 'Mikhail', 'age' => 62],
        ];
        $peopleByOldestFirst = f\sort($byAge, $people);
        $this->assertEquals(
            [
                1 => ['name' => 'Peter', 'age' => 78],
                0 => ['name' => 'Emma', 'age' => 70],
                2 => ['name' => 'Mikhail', 'age' => 62],
            ],
            $peopleByOldestFirst
        );
    }

    public function test_uniq_by()
    {
        $uniq_by_abs = f\uniq_by('abs');
        $this->assertEquals(
            [-1, -5, 2, 10],
            $uniq_by_abs([-1, -5, 2, 10, 1, 2])
        );
    }

    public function test_uniq_by_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Basko\Functional\uniq_by() expects parameter 2 to be array or instance of Traversable, NULL given'
        );
        f\uniq_by(f\T, null);
    }

    public function test_uniq()
    {
        $this->assertEquals(
            [1, 2],
            f\uniq([1, 1, 2, 1])
        );
        $this->assertEquals(
            [1, '1'],
            f\uniq([1, '1'])
        );
        $this->assertEquals(
            [[42]],
            f\uniq([[42], [42]])
        );
    }
}
