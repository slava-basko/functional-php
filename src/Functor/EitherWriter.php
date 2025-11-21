<?php

namespace Basko\Functional\Functor;

use Basko\Functional\Exception\TypeException;

class EitherWriter extends Monad
{
    const of = "Basko\Functional\Functor\EitherWriter::of";
    const right = "Basko\Functional\Functor\EitherWriter::right";
    const left = "Basko\Functional\Functor\EitherWriter::left";

    /**
     * @var bool
     */
    protected $isRight = false;

    /**
     * @var mixed
     */
    protected $aggregation = null;

    /**
     * @param bool $isRight
     * @param mixed $aggregation
     * @param mixed $value
     * @return static
     */
    public static function of($isRight, $aggregation, $value)
    {
        if (
            !\is_string($aggregation) &&
            !\is_array($aggregation) &&
            !\is_int($aggregation) &&
            !\is_float($aggregation) &&
            !\is_bool($aggregation)
        ) {
            throw new \InvalidArgumentException(
                \sprintf(
                    'Argument 2 passed to %s must be of the type string, array, int, float or bool, %s given',
                    __METHOD__,
                    \gettype($aggregation)
                )
            );
        }

        $m = new static($value);
        $m->isRight = $isRight;
        $m->aggregation = $aggregation;

        return $m;
    }

    /**
     * Aka "success"
     *
     * @param mixed $aggregation
     * @param mixed $value
     * @return static
     */
    public static function right($aggregation, $value)
    {
        return static::of(true, $aggregation, $value);
    }

    /**
     * Aka "failure"
     *
     * @param mixed $aggregation
     * @param mixed $value
     * @return static
     */
    public static function left($aggregation, $value)
    {
        return static::of(false, $aggregation, $value);
    }

    /**
     * Creates a new right EitherWriter with an empty aggregation
     *
     * @param mixed $value
     * @return static
     */
    public static function rightEmpty($value)
    {
        // Use empty value based on default aggregation type
        return static::right(static::emptyAggregation(), $value);
    }

    /**
     * Creates a new left EitherWriter with an empty aggregation
     *
     * @param mixed $value
     * @return static
     */
    public static function leftEmpty($value)
    {
        // Use empty value based on default aggregation type
        return static::left(static::emptyAggregation(), $value);
    }

    /**
     * Returns empty aggregation (string by default)
     *
     * @return string
     */
    protected static function emptyAggregation()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function map(callable $f)
    {
        if (!$this->isRight) {
            return static::left($this->aggregation, $this->value);
        }

        try {
            return static::right($this->aggregation, \call_user_func($f, $this->value));
        } catch (\Exception $exception) {
            // Append exception message to aggregation if it's a string
            $aggregation = $this->appendToAggregation(
                $this->aggregation,
                \is_string($this->aggregation) ? $exception->getMessage() : null
            );
            return static::left($aggregation, $exception->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function flatMap(callable $f)
    {
        if (!$this->isRight) {
            return static::left($this->aggregation, $this->value);
        }

        try {
            $result = \call_user_func($f, $this->value);
        } catch (\Exception $exception) {
            // Append exception message to aggregation if it's a string
            $aggregation = $this->appendToAggregation(
                $this->aggregation,
                \is_string($this->aggregation) ? $exception->getMessage() : null
            );
            return static::left($aggregation, $exception->getMessage());
        }

        TypeException::assertReturnType($result, Monad::class, __METHOD__);

        // If result is also an EitherWriter, we need to concatenate the aggregations
        if ($result instanceof self) {
            return $this->concat($result);
        }

        // For non-EitherWriter monads, we keep our aggregation and take their value
        if ($result instanceof Either) {
            return $result->isRight()
                ? static::right($this->aggregation, $result->extract())
                : static::left($this->aggregation, $result->extract());
        } elseif ($result instanceof Writer) {
            // For Writer monad, extract its value but keep our own aggregation for now
            return static::right($this->aggregation, $result->extract());
        }

        // For other monads, just keep our aggregation and use their value
        return static::right($this->aggregation, $result->extract());
    }

    /**
     * @inheritdoc
     */
    public function ap(Monad $m)
    {
        TypeException::assertType($m, static::class, __METHOD__);

        if (!$this->isRight) {
            return static::left($this->aggregation, $this->value);
        }

        try {
            $result = $this->map($m->extract());
            return $this->concat($result);
        } catch (\Exception $exception) {
            $aggregation = $this->appendToAggregation(
                $this->aggregation,
                \is_string($this->aggregation) ? $exception->getMessage() : null
            );
            return static::left($aggregation, $exception->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function flatAp(Monad $m)
    {
        TypeException::assertType($m, static::class, __METHOD__);

        if (!$this->isRight) {
            return static::left($this->aggregation, $this->value);
        }

        try {
            $result = $this->flatMap($m->extract());
            return $this->concat($result);
        } catch (\Exception $exception) {
            $aggregation = $this->appendToAggregation(
                $this->aggregation,
                \is_string($this->aggregation) ? $exception->getMessage() : null
            );
            return static::left($aggregation, $exception->getMessage());
        }
    }

    /**
     * Combine two EitherWriter monads, merging their aggregations
     *
     * @param \Basko\Functional\Functor\EitherWriter $m
     * @return static
     */
    protected function concat(EitherWriter $m)
    {
        $combinedAggregation = $this->combineAggregations($this->aggregation, $m->getAggregation());

        // If either is Left, result is Left
        if (!$this->isRight || !$m->isRight()) {
            // Take the value from the left-most Left
            $value = !$this->isRight ? $this->value : $m->extract();
            return static::left($combinedAggregation, $value);
        }

        // Both are Right, so result is Right
        return static::right($combinedAggregation, $m->extract());
    }

    /**
     * Combines two aggregation values based on their type
     *
     * @param mixed $agg1
     * @param mixed $agg2
     * @return mixed
     */
    protected function combineAggregations($agg1, $agg2)
    {
        if (\is_string($agg1) && \is_string($agg2)) {
            return $agg1 . $agg2;
        } elseif (\is_array($agg1) && \is_array($agg2)) {
            return \array_merge($agg1, $agg2);
        } elseif ((\is_int($agg1) || \is_float($agg1)) && (\is_int($agg2) || \is_float($agg2))) {
            return $agg1 + $agg2;
        } elseif (\is_bool($agg1) && \is_bool($agg2)) {
            return $agg1 || $agg2;
        }

        // Fallback for mixed types - convert to string
        return (string)$agg1 . (string)$agg2;
    }

    /**
     * Appends a value to an aggregation if both are non-null
     *
     * @param mixed $aggregation
     * @param mixed $value
     * @return mixed
     */
    protected function appendToAggregation($aggregation, $value)
    {
        if ($value === null) {
            return $aggregation;
        }

        if (\is_string($aggregation) && \is_string($value)) {
            return $aggregation . $value;
        } elseif (\is_array($aggregation)) {
            $result = $aggregation;
            $result[] = $value;
            return $result;
        }

        return $aggregation;
    }

    /**
     * Get the aggregation value
     *
     * @return mixed
     */
    public function getAggregation()
    {
        return $this->aggregation;
    }

    /**
     * Pattern matching function for EitherWriter
     *
     * @param callable $right Function to process right value: function($value, $aggregation) {}
     * @param callable $left Function to process left value: function($value, $aggregation) {}
     * @return static
     */
    public function match(callable $right, callable $left)
    {
        if ($this->isRight) {
            \call_user_func($right, $this->extract(), $this->aggregation);
        } else {
            \call_user_func($left, $this->extract(), $this->aggregation);
        }

        return $this;
    }

    /**
     * Syntax sugar for more convenience when using in procedural style.
     *
     * @return bool
     */
    public function isRight()
    {
        return $this->isRight === true;
    }

    /**
     * Syntax sugar for more convenience when using in procedural style.
     *
     * @return bool
     */
    public function isLeft()
    {
        return $this->isRight === false;
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        $type = $this->isRight ? 'Right' : 'Left';
        return \sprintf(
            "%s(aggregation: %s, value: %s)",
            $type,
            \var_export($this->aggregation, true),
            \var_export($this->value, true)
        );
    }
}
