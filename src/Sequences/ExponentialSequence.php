<?php

namespace Basko\Functional\Sequences;

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
    private $value;

    /**
     * @var int
     */
    private $times;

    public function __construct($start, $percentage)
    {
        if (!\is_int($start) || $start < 1) {
            throw new \InvalidArgumentException(
                'ExponentialSequence expects $start argument to be an integer, greater than or equal to 1'
            );
        }

        if (!\is_int($percentage) || $percentage < 1 || $percentage > 100) {
            throw new \InvalidArgumentException(
                'ExponentialSequence expects $percentage argument to be an integer, between 1 and 100'
            );
        }

        $this->start = $start;
        $this->percentage = $percentage;
    }

    public function current()
    {
        return $this->value;
    }

    public function next()
    {
        $this->value = (int)\round(\pow($this->start * (1 + $this->percentage / 100), $this->times));
        $this->times++;
    }

    public function key()
    {
        return null;
    }

    public function valid()
    {
        return true;
    }

    public function rewind()
    {
        $this->times = 1;
        $this->value = $this->start;
    }
}
