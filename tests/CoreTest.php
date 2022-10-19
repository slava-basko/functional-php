<?php

namespace Tests\Functional;

use Basko\Functional as f;

class CoreTest extends BaseTest
{
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
    }

    public function test_identical()
    {
        $this->assertTrue(f\identical(1, 1));
        $identicalToTwo = f\identical(2);
        $this->assertTrue($identicalToTwo(2));
        $this->assertFalse(f\identical(1, '1'));
    }

    public function test_lt()
    {
        $this->assertTrue(f\lt(1, 2));
        $less_than_10 = f\lt(10);
        $this->assertTrue($less_than_10(9));
        $this->assertFalse(f\lt(2, 1));
        $this->assertFalse(f\lt(2, 2));
        $this->assertTrue(f\lt(2, 3));
        $this->assertTrue(f\lt('a', 'z'));
        $this->assertFalse(f\lt('z', 'a'));
    }

    public function test_lte()
    {
        $this->assertTrue(f\lte(2, 2));
        $this->assertTrue(f\lte(1, 2));
        $less_than_or_equal10 = f\lte(10);
        $this->assertTrue($less_than_or_equal10(10));
    }

    public function test_gt()
    {
        $this->assertTrue(f\gt(2, 1));
        $greater_than_10 = f\gt(10);
        $this->assertTrue($greater_than_10(11));
    }

    public function test_gte()
    {
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

    function test_not()
    {
        $notString = f\not('is_string');
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
            f\partial('array_map', 'trim'),
            f\partial_r('array_filter', 'strlen'),
            f\partial('implode', ', '),
            f\partial_r('substr', 0, 34),
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
                f\compose('count', $getTeam1GoalsTimes),
                f\compose('count', $getTeam2GoalsTimes),
            ]
        ));
        $this->assertEquals('2-1', $scoreboardInfo());
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
            [f\gte(12), $secondary],
            [f\gte(5), $primary],
            [f\gte(4), $preschool],
        ]);

        $this->assertEquals('At 0 you unborn', $stage(0));
        $this->assertEquals('At 4 you go to preschool', $stage(4));
        $this->assertEquals('At 5 you go to primary school', $stage(5));
        $this->assertEquals('At 13 you go to secondary school', $stage(13));

        $cond = f\cond([
            [f\eq(0), f\always('water freezes')],
            [f\gte(100), f\always('water boils')],
            [f\T, function ($t) {
                return "nothing special happens at $t °C";
            }],
        ]);

        $this->assertEquals('water freezes', $cond(0));
        $this->assertEquals('water boils', $cond(100));
        $this->assertEquals('nothing special happens at 50 °C', $cond(50));

        $emptyCond = f\cond([]);
        $this->assertNull($emptyCond(2));
    }

    public function test_flipped()
    {
        $mergeStrings = function ($head, $tail) {
            return $head . $tail;
        };

        $flippedMergeStrings = f\flipped($mergeStrings);
        $this->assertSame($mergeStrings('one', 'two'), $flippedMergeStrings('two', 'one'));
    }

    public function test_on()
    {
        $containsInsensitive = f\on(f\contains, 'strtolower');
        $this->assertTrue($containsInsensitive('o', 'FOO'));

        $onContains = f\on(f\contains);
        $containsInsensitive2 = $onContains('strtolower');
        $this->assertTrue($containsInsensitive2('o', 'FOO'));
    }

    public function test_both()
    {
        $this->assertTrue(f\both(f\T(), f\T()));
        $true = f\both(f\T());
        $this->assertTrue($true(f\T()));

        $this->assertFalse(f\both(f\F(), f\T()));

        $between6And9 = f\both(f\gt(6), f\lt(9));
        $this->assertTrue($between6And9(7));
        $this->assertTrue($between6And9(8));
        $this->assertFalse($between6And9(10));
    }

    public function test_null()
    {
        $this->assertNull(f\NULL());
    }
}
