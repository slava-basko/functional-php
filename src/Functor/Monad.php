<?php

namespace Basko\Functional\Functor;

use Basko\Functional\Exception\InvalidArgumentException;

/**
 * @template T
 */
abstract class Monad
{
    /**
     * @var T
     */
    protected $value;

    /**
     * @param T $value
     */
    protected function __construct($value)
    {
        if ($this instanceof Type) {
            $vType = \gettype($value);
            $mType = $this::type();
            if ($vType === 'object') {
                InvalidArgumentException::assertType(\get_class($value), $mType, static::class, 1);
            } elseif ($mType !== $vType) {
                throw new InvalidArgumentException(
                    sprintf(
                        '%s() expects parameter %d to be %s, %s (%s) given',
                        static::class,
                        1,
                        $mType,
                        $vType,
                        $value
                    )
                );
            }
        }
        $this->value = $value;
    }

    /**
     * @param callable(T):mixed $f
     * @return static
     */
    abstract public function map(callable $f);

    /**
     * @param callable(T):static $f
     * @return static
     */
    abstract public function flatMap(callable $f);

    /**
     * @return T
     */
    public function extract()
    {
        if ($this->value instanceof self) {
            return $this->value->extract();
        }

        return $this->value;
    }
}
