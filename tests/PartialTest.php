<?php

namespace Tests\Functional;

use Basko\Functional as f;

class PartialTest extends BaseTest
{
    public function test_partial()
    {
        $implode_coma = f\partial('implode', ',');
        $implode_pipe = f\partial('implode', '|');
        $this->assertEquals('1,2', $implode_coma([1, 2]));
        $this->assertEquals('1|2', $implode_pipe([1, 2]));

        $sub = f\partial('substr', 'abcdef', 0);
        $this->assertEquals('ab', $sub(2));
    }

    public function test_partial_r()
    {
        $implode12 = f\partial_r('implode', [1, 2]);
        $this->assertEquals('1,2', $implode12(','));
        $this->assertEquals('1;2', $implode12(';'));

        $sub = f\partial_r('substr', 0, 2);
        $this->assertEquals('ab', $sub('abcdef'));
    }

    public function test_partial_p()
    {
        $sub = f\partial_p('substr', [
            1 => 'abcdef',
            3 => 2
        ]);
        $this->assertEquals('ab', $sub(0));
    }
}
