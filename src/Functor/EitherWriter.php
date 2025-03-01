<?php

namespace Basko\Functional\Functor;

use Basko\Functional\Exception\TypeException;

/**
 * @template T
 * @extends \Basko\Functional\Functor\Either<T>
 */
class EitherWriter extends Either
{
    const of = "Basko\Functional\Functor\EitherWriter::of";

    const right = "Basko\Functional\Functor\EitherWriter::right";

    const left = "Basko\Functional\Functor\EitherWriter::left";

    /**
     * @var array<array-key, mixed>
     */
    protected $aggregation = [];

    /**
     * Aka "failure"
     *
     * @param mixed $value
     * @return static
     */
    public static function left($value)
    {
        $m = static::of(false, $value);
        $m->aggregation[] = $value;

        return $m;
    }

    /**
     * @inheritdoc
     */
    public function flatMap(callable $f)
    {
        try {
            $result = \call_user_func($f, $this->value);
        } catch (\Exception $exception) {
            $result = static::left($exception->getMessage());
        }

        TypeException::assertReturnType($result, static::class, __METHOD__);

        if ($result->isLeft() || $this->isLeft()) {
            $result->aggregation = array_merge($this->aggregation, $result->aggregation);
            $result->validValue = false;
            $result->value = null; // @phpstan-ignore assign.propertyType
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function transform($m)
    {
        $this->assertTransform($m);

        $value = $this->extract();

        if ($m === Maybe::class) {
            return $value === null ? Maybe::nothing() : Maybe::just($value);
        } elseif ($m === Either::class) {
            return $this->isLeft() ? Either::left($value) : Either::right($value);
        } elseif ($m === Optional::class) {
            return Optional::just($value);
        } elseif ($m === Constant::class) {
            return Constant::of($value);
        } elseif ($m === Identity::class) {
            return Identity::of($value);
        } elseif ($m === IO::class) {
            return IO::of(function () use ($value) {
                return $value;
            });
        } elseif ($m === Writer::class) {
            return Writer::of($this->aggregation, $value);
        }

        throw $this->cantTransformException($m);
    }

    /**
     * @param callable(T):void $right
     * @param callable(array<array-key, mixed>):void $left
     * @return static
     */
    public function match(callable $right, callable $left)
    {
        if ($this->validValue) {
            \call_user_func($right, $this->extract());
        } else {
            \call_user_func($left, $this->aggregation);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        if ($this->isRight()) {
            $str = 'Right(' . \var_export($this->value, true) . ')';
        } else {
            $str = 'Left(' . \var_export($this->value, true) . ')';
        }

        return \sprintf(
            "%s(aggregation: %s, %s)",
            $this->getClass(),
            \var_export($this->aggregation, true),
            $str
        );
    }
}
