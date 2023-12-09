<?php

namespace Basko\FunctionalTest\TestCase;

use Basko\Functional as f;
use Basko\Functional\Exception\InvalidArgumentException;
use Basko\FunctionalTest\Helpers\User;
use Basko\FunctionalTest\Helpers\Value;

class CoreTest extends BaseTest
{
    /**
     * @doesNotPerformAssertions
     */
    public function test_noop()
    {
        f\noop();
    }

    public function test_identity()
    {
        $this->assertSame('value', f\identity('value'));
        $this->assertSame(1, f\identity(1));
        $this->assertSame('1', f\identity('1'));
        $this->assertSame([1, 2], f\identity([1, 2]));
        $obj = new \stdClass;
        $this->assertSame($obj, f\identity($obj));
    }

    public function testTF()
    {
        $this->assertTrue(f\T());
        $this->assertFalse(f\F());
    }

    public function test_eq()
    {
        $this->assertTrue(f\eq(1, 1));
        $equal_10 = f\eq(10);
        $this->assertTrue($equal_10(10));
        $this->assertTrue(f\eq(1, '1'));

        $this->assertFalse(f\eq(1, null));
    }

    public function test_identical()
    {
        $this->assertTrue(f\identical(1, 1));
        $identicalToTwo = f\identical(2);
        $this->assertTrue($identicalToTwo(2));
        $this->assertFalse(f\identical(1, '1'));
        $this->assertFalse(f\identical(1, null));
    }

    public function test_lt()
    {
        $this->assertTrue(f\lt(1, 2));
        $less_than_10 = f\partial_r(f\lt, 10);
        $this->assertTrue($less_than_10(9));
        $this->assertFalse(f\lt(2, 1));
        $this->assertFalse(f\lt(2, 2));
        $this->assertTrue(f\lt(2, 3));
        $this->assertTrue(f\lt('a', 'z'));
        $this->assertFalse(f\lt('z', 'a'));
        $this->assertFalse(f\lt('z', null));
    }

    public function test_lte()
    {
        $this->assertFalse(f\lte(2, null));
        $this->assertTrue(f\lte(2, 2));
        $this->assertTrue(f\lte(1, 2));
        $less_than_or_equal10 = f\lte(10);
        $this->assertTrue($less_than_or_equal10(10));
    }

    public function test_gt()
    {
        $this->assertTrue(f\gt(2, null));
        $this->assertTrue(f\gt(2, 1));
        $greater_than_10 = f\partial_r(f\gt, 10);
        $this->assertTrue($greater_than_10(11));
    }

    public function test_gte()
    {
        $this->assertTrue(f\gte(2, null));
        $this->assertTrue(f\gte(2, 2));
        $this->assertTrue(f\gte(2, 1));
        $greater_than_or_equal_10 = f\gte(10);
        $this->assertTrue($greater_than_or_equal_10(10));
    }

    public function test_tail_recursion()
    {
        $fact = f\tail_recursion(function ($n, $acc = 1) use (&$fact) {
            if ($n == 0) {
                return $acc;
            }

            return $fact($n - 1, $acc * $n);
        });
        $this->assertEquals(3628800, $fact(10));

        $sum_of_range = f\tail_recursion(function ($from, $to, $acc = 0) use (&$sum_of_range) {
            if ($from > $to) {
                return $acc;
            }

            return $sum_of_range($from + 1, $to, $acc + $from);
        });
        $this->assertEquals(50005000, $sum_of_range(1, 10000));
    }

    public function test_map()
    {
        $this->assertEquals([2, 3, 4], f\map(f\plus(1), [1, 2, 3]));
        $this->assertEquals(['a1', 'a2', 'a3'], f\map(f\concat('a'), [1, 2, 3]));

        $func = function ($v) {
            return $v . '0';
        };
        $this->assertEquals([10, 20, 30], f\map($func, [1, 2, 3]));
        $this->assertEquals([10, 20, 30], f\map(f\multiply(10), [1, 2, 3]));
    }

    public function test_map_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Basko\Functional\map() expects parameter 2 to be array or instance of Traversable, NULL given'
        );
        f\map(f\plus(1), null);
    }

    public function test_flat_map()
    {
        $numArray5 = [1, 2, 3, 4, 5];
        $numArray5Nested = [[1, 2], [3, 4], [5]];

        $flatten = f\flat_map(function ($n) {
            return $n;
        }, $numArray5Nested);
        $this->assertEquals($numArray5, $flatten);

        $curriedDoubles = f\flat_map(function ($n) {
            return [$n, $n];
        });
        $doubles = $curriedDoubles($numArray5);

        $this->assertEquals([1, 1, 2, 2, 3, 3, 4, 4, 5, 5], $doubles);

        $nothing = f\flat_map(function ($x) {
            return $x;
        }, []);

        $this->assertEquals([], $nothing);

        $flatEmpty = f\flat_map(function ($_x) {
            return [];
        }, $numArray5);

        $this->assertEquals([], $flatEmpty);

        $this->assertEquals([2, 3, 4], f\flat_map(f\plus(1), [1, 2, 3]));
    }

    public function test_flat_map_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Basko\Functional\flat_map() expects parameter 2 to be array or instance of Traversable, NULL given'
        );
        f\flat_map(f\plus(1), null);
    }

    public function test_each()
    {
        $calls = 0;
        $func = function ($v) use (&$calls) {
            $calls++;

            return $v . '-other-value';
        };

        $each = f\each($func);

        $this->assertEquals([1, 2, 3], $each([1, 2, 3]));
        $this->assertEquals(3, $calls);
    }

    public function test_each_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Basko\Functional\each() expects parameter 2 to be array or instance of Traversable, NULL given'
        );
        f\each(f\tap, null);
    }

    public function test_not()
    {
        $this->assertFalse(f\not(true));
        $this->assertTrue(f\not(false));
        $this->assertTrue(f\not(0));
        $this->assertFalse(f\not(1));
    }

    function test_complement()
    {
        $notString = f\complement('is_string');
        $this->assertTrue($notString(1));
    }

    public function test_tap()
    {
        $input = new \stdClass();
        $input->property = 'foo';
        $output = f\tap(function ($o) {
            $o->property = 'bar';
        }, $input);
        $this->assertSame($input, $output);
        $this->assertSame('foo', $input->property);

        $inc = 0;
        $incFn = function ($v) use (&$inc) {
            $inc++;
        };
        $incTap = f\tap($incFn);
        $this->assertSame('val', $incTap('val'));
    }

    public function test_fold()
    {
        $value = f\fold(f\plus, 0, [2, 2, 2]);
        $this->assertEquals(6, $value);

        $foldPlus = f\fold(f\plus);
        $foldPlus0 = $foldPlus(0);
        $this->assertEquals(6, $foldPlus0([2, 2, 2]));

        $this->assertEquals(5, f\fold(f\div, 100, [4, 5, 1]));
        $this->assertEquals(451, f\fold(f\concat, '4', [5, 1]));

        $sc = function ($a, $b) {
            return "($a+$b)";
        };
        $this->assertEquals(
            '(((((((((((((0+1)+2)+3)+4)+5)+6)+7)+8)+9)+10)+11)+12)+13)',
            f\fold($sc, '0', range(1, 13))
        );
    }

    public function test_fold_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Basko\Functional\fold() expects parameter 3 to be array or instance of Traversable, NULL given'
        );
        f\fold(f\plus, 0, null);
    }

    public function test_ford_r()
    {
        $this->assertEquals(2, f\fold_r(f\minus, 0, [1, 4, 5]));
        $this->assertEquals(514, f\fold_r(f\concat, '4', [5, 1]));

        $sc = function ($a, $b) {
            return "($a+$b)";
        };
        $this->assertEquals(
            '(1+(2+(3+(4+(5+(6+(7+(8+(9+(10+(11+(12+(13+0)))))))))))))',
            f\fold_r($sc, '0', range(1, 13))
        );

        $foldRPlus = f\fold_r(f\plus);
        $foldRPlus0 = $foldRPlus(0);
        $this->assertEquals(6, $foldRPlus0([2, 2, 2]));

        $foldRPlus0 = f\fold_r(f\plus, 0);
        $this->assertEquals(6, $foldRPlus0([2, 2, 2]));
    }

    public function test_fold_r_fail()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Basko\Functional\fold_r() expects parameter 3 to be array or instance of Traversable, NULL given'
        );
        f\fold_r(f\plus, 0, null);
    }

    public function testAlways()
    {
        $value = 'a';
        $constA = f\always($value);

        $this->assertSame($value, $constA());
        $this->assertSame($value, $constA());
    }

    public function test_compose()
    {
        $data = [1, 2, 3];
        $plusEach = function ($arr) {
            $res = [];
            foreach ($arr as $item) {
                $res[] = $item + 1;
            }

            return $res;
        };
        $powerEach = function ($arr) {
            $res = [];
            foreach ($arr as $item) {
                $res[] = $item * $item;
            }

            return $res;
        };
        $composed = f\compose($plusEach, $powerEach);
        $this->assertEquals([2, 5, 10], $composed($data));

        $composed2 = f\map(f\compose(f\plus(1), f\power));
        $this->assertEquals([2, 5, 10], $composed2($data));
    }

    public function test_pipe()
    {
        $products = [
            ['description' => 'CD Player'],
            ['description' => ' Abba Volume 1'],
            ['description' => ''],
            ['description' => 'Kim Wilde'],
        ];
        $pipe = f\pipe(
            f\pluck('description'),
            f\map(f\unary('trim')),
            f\select(f\unary('strlen')),
            f\join(', '),
            f\take(34),
            f\partial_r('trim', ', ')
        );
        $this->assertEquals('CD Player, Abba Volume 1, Kim Wild', $pipe($products));

        $descriptions = [];
        foreach ($products as $product) {
            $descriptions[] = $product['description'];
        }
        $this->assertEquals(
            'CD Player, Abba Volume 1, Kim Wild',
            trim(substr(implode(', ', array_filter(array_map('trim', $descriptions), 'strlen')), 0, 34), ', ')
        );
    }

    public function test_converge()
    {
        $getTeam1GoalsTimes = function () {
            return ['14:45', '14:56'];
        };
        $getTeam2GoalsTimes = function () {
            return ['13:20'];
        };
        $listConverge = f\converge(f\to_list);
        $scoreboardInfo = f\compose(f\join('-'), $listConverge(
            [
                f\compose(f\len, $getTeam1GoalsTimes),
                f\compose(f\len, $getTeam2GoalsTimes),
            ]
        ));
        $this->assertEquals('2-1', $scoreboardInfo());
    }

    public function test_apply()
    {
        $f = f\call('strtoupper');
        $this->assertEquals('SLAVA', $f('slava'));
    }

    public function test_apply_to()
    {
        $call = f\apply_to('some');
        $this->assertEquals('SOME', $call('strtoupper'));

        $call = f\apply_to([5, 3]);
        $this->assertEquals(8, $call(f\sum));

        $call2 = f\apply_to([5, 3, 1]);
        $this->assertEquals(9, $call2(f\sum));
        $this->assertEquals(1, $call2(f\diff));
    }

    public function test_cond()
    {
        $unborn = function ($age) {
            return "At $age you unborn";
        };
        $preschool = function ($age) {
            return "At $age you go to preschool";
        };
        $primary = function ($age) {
            return "At $age you go to primary school";
        };
        $secondary = function ($age) {
            return "At $age you go to secondary school";
        };

        $stage = f\cond([
            [f\eq(0), $unborn],
            [f\partial_r(f\gte, 12), $secondary],
            [f\partial_r(f\gte, 5), $primary],
            [f\partial_r(f\gte, 4), $preschool],
        ]);

        $this->assertEquals('At 0 you unborn', $stage(0));
        $this->assertEquals('At 4 you go to preschool', $stage(4));
        $this->assertEquals('At 5 you go to primary school', $stage(5));
        $this->assertEquals('At 13 you go to secondary school', $stage(13));

        $cond = f\cond([
            [f\eq(0), f\always('water freezes')],
            [f\partial_r(f\gte, 100), f\always('water boils')],
            [f\T, function ($t) {
                return "nothing special happens at $t °C";
            }],
        ]);

        $this->assertEquals('water freezes', $cond(0));
        $this->assertEquals('water boils', $cond(100));
        $this->assertEquals('nothing special happens at 50 °C', $cond(50));

        $emptyCond = f\cond([]);
        $this->assertNull($emptyCond(2));

        $typeOf = f\cond([
            [f\is_type_of(User::class), f\always('user')],
            [f\is_type_of(Value::class), f\always('value')],
            [f\T, f\always('unknown')],
        ]);
        $this->assertEquals('user', $typeOf(new User([])));
        $this->assertEquals('value', $typeOf(new Value(null)));

        $helloLang = f\cond([
            [f\any_pass([f\contains('Welcome'), f\contains('Hello')]), f\always('en')],
            [f\any_pass([f\contains('Bienvenue'), f\contains('Bonjour')]), f\always('fr')],
        ]);

        $this->assertEquals('en', $helloLang('Welcome'));
        $this->assertEquals('fr', $helloLang('Bienvenue'));
    }

    public function test_flipped()
    {
        $mergeStrings = function ($head, $tail) {
            return $head . $tail;
        };

        $flippedMergeStrings = f\flipped($mergeStrings);
        $this->assertSame($mergeStrings('one', 'two'), $flippedMergeStrings('two', 'one'));
    }

    public function test_flip()
    {
        $revGt = f\flip(f\gt);
        $gt9 = $revGt(9);

        $this->assertTrue($revGt(9, 10));
        $this->assertTrue($gt9(10));
    }

    public function test_on()
    {
        $containsInsensitive = f\on(f\contains, 'strtolower');
        $this->assertTrue($containsInsensitive('o', 'FOO'));

        $onContains = f\on(f\contains);
        $containsInsensitive2 = $onContains('strtolower');
        $this->assertTrue($containsInsensitive2('o', 'FOO'));
    }

    public function test_y()
    {
        $factorial = f\y(function ($fact) {
            return function ($n) use ($fact) {
                return ($n <= 1) ? 1 : $n * $fact($n - 1);
            };
        });

        $this->assertEquals(120, $factorial(5));
    }

    public function test_both()
    {
        $this->assertTrue(f\both(f\T(), f\T()));
        $true = f\both(f\T());
        $this->assertTrue($true(f\T()));

        $this->assertFalse(f\both(f\F(), f\T()));

        $gt6 = f\partial_r(f\gt, 6);
        $lt9 = f\partial_r(f\lt, 9);
        $between6And9 = f\both($gt6, $lt9);
        $this->assertTrue($between6And9(7));
        $this->assertTrue($between6And9(8));
        $this->assertFalse($between6And9(10));

        $this->assertFalse(f\both(1, null));
    }

    public function test_all_pass()
    {
        $isQueen = f\pipe(f\prop('rank'), f\eq('Q'));
        $isSpade = f\pipe(f\prop('suit'), f\eq('♠︎'));
        $isQueenOfSpades = f\all_pass([$isQueen, $isSpade]);

        $this->assertFalse($isQueenOfSpades(['rank' => 'Q', 'suit' => '♣︎']));
        $this->assertTrue($isQueenOfSpades(['rank' => 'Q', 'suit' => '♠︎']));

        $this->assertFalse(f\all_pass([$isQueen, $isSpade], null));
    }

    public function test_any_pass()
    {
        $isClub = f\pipe(f\prop('suit'), f\eq('♣'));
        $isSpade = f\pipe(f\prop('suit'), f\eq('♠'));
        $isBlackCard = f\any_pass([$isClub, $isSpade]);

        $this->assertTrue($isBlackCard(['rank' => '10', 'suit' => '♣']));
        $this->assertTrue($isBlackCard(['rank' => 'Q', 'suit' => '♠']));
        $this->assertFalse($isBlackCard(['rank' => 'Q', 'suit' => '♦']));

        $this->assertFalse(f\any_pass([$isClub, $isSpade], null));
    }

    public function test_null()
    {
        $this->assertNull(f\N());
    }

    public function test_ap()
    {
        $f = f\ap([f\multiply(2), f\plus(3)]);
        $this->assertEquals(
            [2, 4, 6, 4, 5, 6],
            $f([1, 2, 3])
        );
        $this->assertEquals(
            ['tasty pizza', 'tasty salad', 'PIZZA', 'SALAD'],
            f\ap([f\concat('tasty '), f\unary('strtoupper')], ['pizza', 'salad'])
        );
    }

    public function test_lift_to()
    {
        $f = function ($arg) {
            if (!is_string($arg)) {
                throw new \InvalidArgumentException();
            }

            return $arg;
        };

        $id = f\Functor\Identity::of('Slava');
        $const = f\Functor\Constant::of('Slava');
        $maybe = f\Functor\Maybe::just('Slava');
        $either = f\Functor\Either::right('Slava');
        $optional = f\Functor\Optional::just('Slava');

        $this->assertEquals($id, f\call(f\lift_m($f), $id));
        $this->assertEquals($const, f\call(f\lift_m($f), $const));
        $this->assertEquals($maybe, f\call(f\lift_m($f), $maybe));
        $this->assertEquals($either, f\call(f\lift_m($f), $either));
        $this->assertEquals($optional, f\call(f\lift_m($f), $optional));
    }

    public function test_lift_m()
    {
        $plusm = f\lift_m(f\plus);
        $this->assertEquals(f\Functor\Maybe::just(5), $plusm(3, f\Functor\Maybe::just(2)));
        $this->assertEquals(
            f\Functor\Maybe::just(5),
            $plusm(f\Functor\Maybe::just(3), f\Functor\Maybe::just(2))
        );

        $containsm = f\lift_m(f\contains);
        $this->assertEquals(
            f\Functor\Maybe::just(true),
            $containsm('foo', f\Functor\Maybe::just('foobar'))
        );
    }

    public function test_zip()
    {
        $result = [[1, 'a'], [2, 'b'], [3, 'c']];
        $this->assertEquals($result, f\zip([1, 2, 3], ['a', 'b', 'c']));
        $this->assertEquals($result, f\zip(
            new \ArrayIterator([1, 2, 3]),
            new \ArrayIterator(['a', 'b', 'c'])
        ));

        $this->assertEquals([[1, 'a'], [2, 'b'], [null, 'c']], f\zip([1, 2], ['a', 'b', 'c']));
        $this->assertEquals([[1, 'a', 'A'], [2, 'b', 'B']], f\zip([1, 2], ['a', 'b'], ['A', 'B']));
    }

    public function test_zip_fail()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'Basko\Functional\zip() expects parameter 2 to be array or instance of Traversable, string given'
        );
        f\zip([1, 2], 'some string');
    }

    public function test_zip_with()
    {
        $sumZip = f\zip_with(f\sum);
        $concatZip = f\zip_with(f\join('-'));

        $this->assertEquals([11, 22, 33], $sumZip([1, 2, 3], [10, 20, 30]));
        $this->assertEquals(['one-один', 'two-два'], $concatZip(['one', 'two'], ['один', 'два']));
    }
}
