<?php

namespace Basko\FunctionalTest\TestCase;

use Basko\Functional as f;

class LensTest extends BaseTest
{
    public function test_lens_view()
    {
        $data = [
            'x' => 1,
            'y' => 2,
        ];

        $lens = f\lens(f\prop('x'), f\assoc('x'));
        $this->assertEquals(1, f\view($lens, $data));
    }

    public function test_lens_set()
    {
        $data = [
            'x' => 1,
            'y' => 2,
        ];

        $lens = f\lens(f\prop('x'), f\assoc('x'));
        $data = f\set($lens, 11, $data);
        $this->assertEquals(11, f\view($lens, $data));
    }

    public function test_lens_over()
    {
        $data = [
            'x' => 1,
            'y' => 2,
        ];

        $lens = f\lens(f\prop('x'), f\assoc('x'));
        $data = f\over($lens, f\plus(100), $data);
        $this->assertEquals(101, f\view($lens, $data));
    }

    public function test_lens_composition()
    {
        $data = [
            'a' => [
                'b' => [
                    'c' => 3,
                ],
            ],
        ];

        $aLens = f\lens(f\prop('a'), f\assoc('a'));
        $bLens = f\compose($aLens, f\lens(f\prop('b'), f\assoc('b')));
        $cLens = f\compose($bLens, f\lens(f\prop('c'), f\assoc('c')));

        $this->assertEquals(3, f\view($cLens, $data));
        $this->assertEquals(4, f\view($cLens, f\set($cLens, 4, $data)));
        $this->assertEquals(13, f\view($cLens, f\over($cLens, f\plus(10), $data)));

        $data2 = new \stdClass();
        $data2->a = new \stdClass();
        $data2->a->b = new \stdClass();
        $data2->a->b->c = 3;

        $this->assertEquals(3, f\view($cLens, $data2));
        $this->assertEquals(4, f\view($cLens, f\set($cLens, 4, $data2)));
        $this->assertEquals(13, f\view($cLens, f\over($cLens, f\plus(10), $data2)));
    }

    public function test_lens_prop()
    {
        $data = [
            'x' => 1,
            'y' => 2,
        ];
        $lens = f\lens_prop('y');
        $this->assertEquals(2, f\view($lens, $data));

        $data = f\set($lens, 22, $data);
        $this->assertEquals(22, f\view($lens, $data));

        $data = f\over($lens, f\plus(100), $data);
        $this->assertEquals(122, f\view($lens, $data));
    }

    public function test_lens_prop_path()
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

        $lens = f\lens_prop_path(['x', 'y', 'z']);
        $this->assertEquals(3, f\view($lens, $data));
        $this->assertEquals(4, f\view($lens, f\set($lens, 4, $data)));
        $this->assertEquals(6, f\view($lens, f\over($lens, f\multiply(2), $data)));
    }

    public function test_lens_elements()
    {
        $data = [
            'a' => [
                'b' => [
                    'c' => 3,
                    'd' => [4, 5, 6]
                ],
            ],
        ];

        $listLens = f\lens_prop_path(['a', 'b', 'd']);
        $firstLens = f\lens(f\prop(0), f\assoc(0));
        $middleLens = f\lens(f\nth(2), f\assoc_element(-2));
        $lastLens = f\lens(f\nth(-1), f\assoc_element(-1));

        $firstElementLens = f\compose($listLens, $firstLens);
        $middleElementLens = f\compose($listLens, $middleLens);
        $lastElementLens = f\compose($listLens, $lastLens);

        $this->assertEquals(4, f\view($firstElementLens, $data));
        $this->assertEquals(6, f\view($lastElementLens, $data));

        $this->assertEquals([99, 5, 6], f\view($listLens, f\set($firstElementLens, 99, $data)));
        $this->assertEquals([4, 5, 99], f\view($listLens, f\set($lastElementLens, 99, $data)));

        $this->assertEquals([4, 99, 6], f\view($listLens, f\set($middleElementLens, 99, $data)));
    }

    public function test_lens_element()
    {
        $data = [10, 20, 30];

        $this->assertEquals(10, f\view(f\lens_element(1), $data));
        $this->assertEquals(20, f\view(f\lens_element(2), $data));
        $this->assertEquals(30, f\view(f\lens_element(3), $data));
        $this->assertEquals(30, f\view(f\lens_element(-1), $data));

        $this->assertEquals([99, 20, 30], f\set(f\lens_element(1), 99, $data));
        $this->assertEquals([10, 20, 99], f\set(f\lens_element(-1), 99, $data));
    }
}
