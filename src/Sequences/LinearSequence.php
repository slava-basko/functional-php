<?php

namespace Basko\Functional\Sequences;

/**
 * Infinite iterator that produces a linearly growing integer sequence.
 *
 * Semantics:
 *  - The first yielded value equals $start.
 *  - Each subsequent value is start + n * $step for n >= 1.
 *  - Until rewind() is called (or the iterator is consumed by foreach), current() returns 0 and key() is 0.
 *    This is intentional and models the "iteration has not started yet" state.
 *
 * Notes:
 *  - valid() always returns true (infinite sequence).
 *  - $step can be any integer (including 0 or negative); the resulting sequence may be constant or decreasing.
 *  - No overflow protection is performed.
 *
 * @implements \Iterator<array-key, mixed>
 */
class LinearSequence implements \Iterator
{
    /**
     * @var int
     */
    private $start;

    /**
     * @var int
     */
    private $step;

    /**
     * @var int
     */
    private $key = 0;

    /**
     * @var int
     */
    private $value = 0;

    /**
     * @param int $start
     * @param int $step
     */
    public function __construct($start, $step)
    {
        if (!\is_int($start) || $start < 0) {
            throw new \InvalidArgumentException(
                'LinearSequence expects $start argument to be an integer, greater than or equal to 0'
            );
        }

        if (!\is_int($step)) {
            throw new \InvalidArgumentException(\sprintf(
                'LinearSequence expects $amount argument to be integer, %s given',
                \gettype($step)
            ));
        }

        $this->start = $start;
        $this->step = $step;
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
        $this->value += $this->step;
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
        $this->key = 0;
        $this->value = $this->start;
    }
}
