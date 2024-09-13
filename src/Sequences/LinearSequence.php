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
    private $amount;

    /**
     * @var int
     */
    private $value = 0;

    /**
     * @param int $start
     * @param int $amount
     */
    public function __construct($start, $amount)
    {
        /** @psalm-suppress DocblockTypeContradiction */
        if (!\is_int($start) || $start < 0) {
            throw new \InvalidArgumentException(
                'LinearSequence expects $start argument to be an integer, greater than or equal to 0'
            );
        }

        /** @psalm-suppress DocblockTypeContradiction */
        if (!\is_int($amount)) {
            throw new \InvalidArgumentException(\sprintf(
                'LinearSequence expects $amount argument to be integer, %s given',
                \gettype($amount)
            ));
        }

        $this->start = $start;
        $this->amount = $amount;
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->value;
    }

    #[\ReturnTypeWillChange]
    public function next()
    {
        $this->value += $this->amount;
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return 0;
    }

    #[\ReturnTypeWillChange]
    public function valid()
    {
        return true;
    }

    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->value = $this->start;
    }
}
