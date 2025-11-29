<?php

namespace Basko\Functional\Sequences;

/**
 * Infinite iterator that produces an exponentially growing integer sequence.
 *
 * Semantics:
 *  - The first yielded value equals $start.
 *  - Each subsequent value is round($start * (1 + $percentage/100)^n) for n >= 1.
 *  - Until rewind() is called (or the iterator is consumed by foreach), current() returns 0 and key() is 0.
 *    This is intentional and models the "iteration has not started yet" state.
 *
 * Notes:
 *  - valid() always returns true (infinite sequence).
 *  - Values are rounded with round(); with small percentages repeated values may occur.
 *  - No overflow protection is performed.
 *
 * @implements \Iterator<array-key, mixed>
 */
class ExponentialSequence implements \Iterator
{
    /**
     * @var int
     */
    private $start;

    /**
     * @var int
     */
    private $percentage;

    /**
     * @var int
     */
    private $key = 0;

    /**
     * @var int
     */
    private $value = 0;

    /**
     * @var int
     */
    private $times = 0;

    /**
     * @param int $start Start value, must be >= 1.
     * @param int $percentage Growth per step in percent, must be between 1 and 100.
     * @throws \InvalidArgumentException If arguments are not integers or out of range.
     */
    public function __construct($start, $percentage)
    {
        if (!\is_int($start) || $start < 1) {
            throw new \InvalidArgumentException(
                'ExponentialSequence expects $start argument to be an integer, greater than or equal to 1'
            );
        }

        if (!\is_int($percentage) || $percentage < 1) {
            throw new \InvalidArgumentException(
                'ExponentialSequence expects $percentage argument to be an integer, greater than or equal to 1'
            );
        }

        $this->start = $start;
        $this->percentage = $percentage;
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->value;
    }

    #[\ReturnTypeWillChange]
    public function next()
    {
        $this->key++;
        $this->value = (int) \round($this->start * \pow(1 + $this->percentage / 100, $this->times));
        $this->times++;
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->key;
    }

    #[\ReturnTypeWillChange]
    public function valid()
    {
        return true;
    }

    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->times = 1;
        $this->key = 0;
        $this->value = $this->start;
    }
}
