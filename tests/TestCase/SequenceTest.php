<?php

namespace Basko\FunctionalTest\TestCase;

use Basko\Functional as f;

class SequenceTest extends BaseTest
{
    /**
     * @param \Iterator $sequence
     * @param int $limit
     * @return array
     */
    protected function sequenceToArray(\Iterator $sequence, $limit)
    {
        $values = [];
        $sequence->rewind();
        for ($a = 0; $a < $limit; $a++) {
            $values[] = $sequence->current();
            $sequence->next();
        }

        return $values;
    }

    /**
     * @param string $message
     * @return void
     */
    protected function expectArgumentError($message)
    {
        $this->setExpectedException(\InvalidArgumentException::class, $message);
    }

    public function testConstantIncrements()
    {
        $sequence = f\sequence_constant(1);

        $values = $this->sequenceToArray($sequence, 10);

        $this->assertSame([1, 1, 1, 1, 1, 1, 1, 1, 1, 1], $values);
    }

    public function testSequenceConstantArgumentMustBePositiveInteger()
    {
        $this->expectArgumentError(
            'sequence_constant() expects $value argument to be an integer, greater than or equal to 0'
        );
        f\sequence_constant(-1);
    }

    public function testLinearIncrements()
    {
        $sequence = f\sequence_linear(0, 1);
        $values = $this->sequenceToArray($sequence, 10);
        $this->assertSame([0, 1, 2, 3, 4, 5, 6, 7, 8, 9], $values);

        $sequence = f\sequence_linear(5, 5);
        $values = $this->sequenceToArray($sequence, 3);
        $this->assertSame([5, 10, 15], $values);
    }

    public function testSequenceLinearLinearNegativeIncrements()
    {
        $sequence = f\sequence_linear(0, -1);

        $values = $this->sequenceToArray($sequence, 10);

        $this->assertSame([0, -1, -2, -3, -4, -5, -6, -7, -8, -9], $values);
    }

    public function testSequenceLinearArgumentMustBePositiveInteger()
    {
        $this->expectArgumentError(
            'LinearSequence expects $start argument to be an integer, greater than or equal to 0'
        );
        f\sequence_linear(-1, 1);
    }

    public function testSequenceLinearAmountArgumentMustBeInteger()
    {
        $this->expectArgumentError(
            'LinearSequence expects $amount argument to be integer, double given'
        );
        f\sequence_linear(0, 1.1);
    }

    public function testSequenceExponentialExponentialIncrementsWith100PercentGrowth()
    {
        $sequence = f\sequence_exponential(1, 100);
        $values = $this->sequenceToArray($sequence, 10);
        $this->assertSame([1, 2, 4, 8, 16, 32, 64, 128, 256, 512], $values);

        $sequence = f\sequence_exponential(200000, 100);
        $values = $this->sequenceToArray($sequence, 3);
        $this->assertSame([200000, 400000, 800000], $values);
    }

    public function testSequenceExponentialExponentialIncrementsWith50PercentGrowth()
    {
        $sequence = f\sequence_exponential(1, 50);

        $values = $this->sequenceToArray($sequence, 10);

        $this->assertSame([1, 2, 2, 3, 5, 8, 11, 17, 26, 38], $values);
    }

    public function testSequenceExponentialStartArgumentMustBePositiveInteger()
    {
        $this->expectArgumentError(
            'ExponentialSequence expects $start argument to be an integer, greater than or equal to 1'
        );
        f\sequence_exponential(-1, 1);
    }

    public function testSequenceExponentialGrowthArgumentMustBePositiveInteger()
    {
        $this->expectArgumentError(
            'ExponentialSequence expects $percentage argument to be an integer, between 1 and 100'
        );
        f\sequence_exponential(1, 0);
    }

    public function testSequenceExponentialGrowthArgumentMustBePositiveIntegerLessThan100()
    {
        $this->expectArgumentError(
            'ExponentialSequence expects $percentage argument to be an integer, between 1 and 100'
        );
        f\sequence_exponential(1, 101);
    }
}
