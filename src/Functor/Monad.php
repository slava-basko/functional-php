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
    final protected function __construct($value)
    {
        if ($this instanceof Type) {
            $vType = \gettype($value);
            $mType = $this::type();
            if (\is_object($value)) {
                /** @var class-string $mType */
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
     * @param static $m
     * @return static
     */
    abstract public function ap(Monad $m);

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

    /**
     * Transforms monad to another monad
     *
     * @template M of \Basko\Functional\Functor\Monad
     * @param class-string<M> $m
     * @return M
     * @phpstan-return new<M[T]>
     */
    abstract public function transform($m);

    /**
     * @param class-string $m
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function assertTransform($m)
    {
        InvalidArgumentException::assertClass($m, static::class, 1);

        $classes = \class_parents($m);
        $possibleMonadClass = (string)\end($classes);

        if ($possibleMonadClass != Monad::class) {
            throw new InvalidArgumentException(
                \sprintf(
                    'Monad::transform() expects parameter %d to be class-string<Monad>, %s (%s) given',
                    1,
                    \gettype($m),
                    \var_export($m, true)
                )
            );
        }
    }

    /**
     * @param class-string $m
     * @return \LogicException
     */
    protected function cantTransformException($m)
    {
        $thisClass = \get_class($this);
        return new \LogicException("Cannot transform $thisClass monad to $m monad");
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
