<?php

namespace Basko\Functional\Sequences;

/**
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
     * @param int $start
     * @param int $percentage
     */
    public function __construct($start, $percentage)
    {
        /** @psalm-suppress DocblockTypeContradiction */
        if (!\is_int($start) || $start < 1) {
            throw new \InvalidArgumentException(
                'ExponentialSequence expects $start argument to be an integer, greater than or equal to 1'
            );
        }

        /** @psalm-suppress DocblockTypeContradiction */
        if (!\is_int($percentage) || $percentage < 1 || $percentage > 100) {
            throw new \InvalidArgumentException(
                'ExponentialSequence expects $percentage argument to be an integer, between 1 and 100'
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
        $this->value = (int)\round(\pow($this->start * (1 + $this->percentage / 100), $this->times));
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
