<?php

namespace Basko\Functional\Functor;

use Basko\Functional\Exception\InvalidArgumentException;

abstract class Monad
{
    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param mixed $value
     */
    final protected function __construct($value)
    {
        if ($this instanceof Type) {
            $vType = \gettype($value);
            $mType = $this::type();
            if (\is_object($value)) {
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
     * @param callable $f
     * @return static
     */
    abstract public function map(callable $f);

    /**
     * @param callable $f
     * @return \Basko\Functional\Functor\Monad
     */
    abstract public function flatMap(callable $f);

    /**
     * @param static<callable> $m
     * @return static
     */
    abstract public function ap(Monad $m);

    /**
     * @param static<callable> $m
     * @return \Basko\Functional\Functor\Monad
     */
    abstract public function flatAp(Monad $m);

    /**
     * @return mixed
     */
    public function extract()
    {
        if ($this->value instanceof self) {
            return $this->value->extract();
        }

        return $this->value;
    }

    /**
     * @return string
     */
    protected function getClass()
    {
        $classParts = \explode('\\', static::class);

        return (string)\end($classParts);
    }

    /**
     * String representation of monad
     * Magic method `__toString` can't be used because php will use it to cast the object to string
     * when passing it as an argument into `someFunction(string $param)`
     *
     * @return string
     */
    public function toString()
    {
        return $this->getClass() . '(' . \var_export($this->value, true) . ')';
    }
}
