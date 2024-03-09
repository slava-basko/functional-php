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

        if ($value instanceof static) {
            return $value;
        }

        $m = new static($value);
        $m->aggregation = $aggregation;

        return $m;
    }

    /**
     * @param callable $f
     * @return static
     */
    public function map(callable $f)
    {
        return static::of($this->aggregation, \call_user_func($f, $this->value));
    }

    /**
     * @param callable $f
     * @return static
     * @throws \Basko\Functional\Exception\TypeException
     */
    public function flatMap(callable $f)
    {
        $result = \call_user_func($f, $this->value);

        TypeException::assertReturnType($result, static::class, __METHOD__);

        return $this->concat($result);
    }

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
     * @template M as object
     * @param class-string<M> $m
     * @return M
     */
    public function transform($m)
    {
        $this->assertTransform($m);

        $value = $this->extract();

        if ($m == Maybe::class) {
            return $value === null ? Maybe::nothing() : Maybe::just($value);
        } elseif ($m == Either::class) {
            return Either::right($value);
        } elseif ($m == Optional::class) {
            return Optional::just($value);
        } elseif ($m == Constant::class) {
            return Constant::of($value);
        } elseif ($m == Identity::class) {
            return Identity::of($value);
        } elseif ($m == IO::class) {
            return IO::of(function () use ($value) {
                return $value;
            });
        } elseif ($m == EitherWriter::class) {
            return EitherWriter::right($value);
        }

        $this->cantTransformException($m);
    }

    public function match(callable $value, callable $aggregation)
    {
        \call_user_func($value, $this->extract());
        \call_user_func($aggregation, $this->aggregation);

        return $this;
    }

    public function __toString()
    {
        return \sprintf(
            "%s(aggregation: %s, value: %s)",
            $this->getClass(),
            \var_export($this->aggregation, true),
            \var_export($this->value, true)
        );
    }
}
