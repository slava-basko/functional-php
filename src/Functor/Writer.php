<?php

namespace Basko\Functional\Functor;

use Basko\Functional\Exception\InvalidArgumentException;
use Basko\Functional\Exception\TypeException;

class Writer extends Monad
{
    const of = "Basko\Functional\Functor\Writer::of";

    /**
     * @var mixed
     */
    protected $aggregation = null;

    /**
     * @param mixed $aggregation
     * @param mixed $value
     * @return static
     */
    public static function of($aggregation, $value)
    {
        if (
            !\is_string($aggregation) &&
            !\is_array($aggregation) &&
            !\is_int($aggregation) &&
            !\is_float($aggregation) &&
            !\is_bool($aggregation)
        ) {
            throw new InvalidArgumentException(
                \sprintf(
                    'Argument 1 passed to %s must be of the type string, array, int, float or bool, %s given',
                    __METHOD__,
                    \gettype($aggregation)
                )
            );
        }

        $m = new static($value);
        $m->aggregation = $aggregation;

        return $m;
    }

    /**
     * @inheritdoc
     */
    public function map(callable $f)
    {
        return static::of($this->aggregation, \call_user_func($f, $this->value));
    }

    /**
     * @inheritdoc
     */
    public function flatMap(callable $f)
    {
        $result = \call_user_func($f, $this->value);

        TypeException::assertReturnType($result, Monad::class, __METHOD__);

        return $this->concat($result);
    }

    /**
     * @inheritdoc
     */
    public function ap(Monad $m)
    {
        TypeException::assertType($m, static::class, __METHOD__);

        $result = $this->map($m->extract());

        return $this->concat($result);
    }

    /**
     * @inheritdoc
     */
    public function flatAp(Monad $m)
    {
        TypeException::assertType($m, static::class, __METHOD__);

        $result = $this->flatMap($m->extract());

        return $this->concat($result);
    }

    /**
     * @param \Basko\Functional\Functor\Writer $m
     * @return static
     */
    protected function concat(Writer $m)
    {
        if (\is_string($m->aggregation)) {
            return static::of($this->aggregation . $m->aggregation, $m->extract());
        } elseif (\is_array($m->aggregation)) {
            return static::of(\array_merge($this->aggregation, $m->aggregation), $m->extract());
        } elseif (\is_int($m->aggregation) || \is_float($m->aggregation)) {
            return static::of($this->aggregation + $m->aggregation, $m->extract());
        } elseif (\is_bool($m->aggregation)) {
            return static::of($this->aggregation || $m->aggregation, $m->extract());
        }

        throw new \LogicException('Unsupported aggregation type');
    }

    /**
     * @param callable $value
     * @param callable $aggregation
     * @return static
     */
    public function match(callable $value, callable $aggregation)
    {
        \call_user_func($value, $this->extract());
        \call_user_func($aggregation, $this->aggregation);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return \sprintf(
            "%s(aggregation: %s, value: %s)",
            $this->getClass(),
            \var_export($this->aggregation, true),
            \var_export($this->value, true)
        );
    }
}
