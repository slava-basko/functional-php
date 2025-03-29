<?php

namespace Basko\Functional\Functor;

use Basko\Functional\Exception\TypeException;

class EitherWriter extends Either
{
    const of = "Basko\Functional\Functor\EitherWriter::of";

    const right = "Basko\Functional\Functor\EitherWriter::right";

    const left = "Basko\Functional\Functor\EitherWriter::left";

    /**
     * @var array
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

        TypeException::assertReturnType($result, Monad::class, __METHOD__);

        if ($result instanceof EitherWriter && ($result->isLeft() || $this->isLeft())) {
            $result->aggregation = array_merge($this->aggregation, $result->aggregation);
            $result->validValue = false;
            $result->value = null;
        }

        return $result;
    }

    /**
     * @param callable $right
     * @param callable $left
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
