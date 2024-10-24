<?php

namespace Basko\Functional\Sequences;

/**
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
        /** @psalm-suppress DocblockTypeContradiction */
        if (!\is_int($start) || $start < 0) {
            throw new \InvalidArgumentException(
                'LinearSequence expects $start argument to be an integer, greater than or equal to 0'
            );
        }

        /** @psalm-suppress DocblockTypeContradiction */
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
