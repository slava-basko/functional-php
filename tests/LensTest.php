<?php

namespace Tests\Functional;

use Basko\Functional as f;

class LensTest extends BaseTest
{
    public function test_lens_view()
    {
        $data = [
            'x' => 1,
            'y' => 2
        ];

        $lens = f\lens(f\prop('x'), f\assoc('x'));
        $this->assertEquals(1, f\view($lens, $data));
    }

    public function test_lens_set()
    {
        $data = [
            'x' => 1,
            'y' => 2
        ];

        $lens = f\lens(f\prop('x'), f\assoc('x'));
        $data = f\set($lens, 11, $data);
        $this->assertEquals(11, f\view($lens, $data));
    }

    public function test_lens_over()
    {
        $data = [
            'x' => 1,
            'y' => 2
        ];

        $lens = f\lens(f\prop('x'), f\assoc('x'));
        $data = f\over($lens, f\plus(100), $data);
        $this->assertEquals(101, f\view($lens, $data));
    }

    public function test_lens_prop()
    {
        $data = [
            'x' => 1,
            'y' => 2
        ];
        $lens = f\lens_prop('y');
        $this->assertEquals(2, f\view($lens, $data));

        $data = f\set($lens, 22, $data);
        $this->assertEquals(22, f\view($lens, $data));

        $data = f\over($lens, f\plus(100), $data);
        $this->assertEquals(122, f\view($lens, $data));
    }

    public function test_lens_path()
    {
        $data = [
            'a' => 1,
            'b' => [
                'c' => 2
            ],
            'x' => [
                'y' => [
                    'z' => 3
                ]
            ],
        ];

        $lens = f\lens_path(['x', 'y', 'z']);
        $this->assertEquals(3, f\view($lens, $data));
        $this->assertEquals(4, f\view($lens, f\set($lens, 4, $data)));
        $this->assertEquals(6, f\view($lens, f\over($lens, f\multiply(2), $data)));
    }
}
