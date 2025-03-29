<?php

namespace Basko\Functional\Functor;

use Basko\Functional\Exception\InvalidArgumentException;
use Basko\Functional\Exception\TypeException;

class Optional extends Monad
{
    const of = "Basko\Functional\Functor\Optional::of";

    const just = "Basko\Functional\Functor\Optional::just";

    const nothing = "Basko\Functional\Functor\Optional::nothing";

    /**
     * @var bool
     */
    protected $hasValue = false;

    /**
     * @param bool $hasValue
     * @param mixed $value
     * @return static
     */
    public static function of($hasValue, $value)
    {
        $m = new static($value);
        $m->hasValue = $hasValue;

        return $m;
    }

    /**
     * @param mixed $value
     * @return static
     */
    public static function just($value)
    {
        return static::of(true, $value);
    }

    /**
     * @return static
     */
    public static function nothing()
    {
        return static::of(false, null);
    }

    /**
     * @inheritdoc
     */
    public function map(callable $f)
    {
        if ($this->hasValue) {
            return static::just(\call_user_func($f, $this->value));
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function flatMap(callable $f)
    {
        if ($this->hasValue) {
            $result = \call_user_func($f, $this->value);
        } else {
            $result = static::nothing();
        }

        TypeException::assertReturnType($result, Monad::class, __METHOD__);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function ap(Monad $m)
    {
        TypeException::assertType($m, static::class, __METHOD__);

        if ($this->hasValue) {
            return $this->map($m->extract());
        }

        return static::nothing();
    }

    /**
     * @inheritdoc
     */
    public function flatAp(Monad $m)
    {
        TypeException::assertType($m, static::class, __METHOD__);

        if ($this->hasValue) {
            return $this->flatMap($m->extract());
        }

        return static::nothing();
    }

    /**
     * @param callable $just
     * @param callable $nothing
     * @return static
     */
    public function match(callable $just, callable $nothing)
    {
        if ($this->hasValue) {
            \call_user_func($just, $this->extract());
        } else {
            \call_user_func($nothing);
        }

        return $this;
    }

    /**
     * @param array-key $key
     * @param array|\ArrayAccess|object $data
     * @param callable|null $f
     * @return static
     */
    public static function fromProp($key, $data, $f = null)
    {
        InvalidArgumentException::assertValidArrayKey($key, __METHOD__);
        if (!is_null($f)) {
            InvalidArgumentException::assertCallable($f, __METHOD__, 3);
        }

        if (\is_array($data) && \array_key_exists($key, $data)) {
            return static::just(\is_callable($f) ? \call_user_func($f, $data[$key]) : $data[$key]);
        }

        if (\is_object($data) && property_exists($data, $key)) {
            return static::just(\is_callable($f) ? \call_user_func($f, $data->{$key}) : $data->{$key});
        }

        if ($data instanceof \ArrayAccess && $data->offsetExists($key)) {
            return static::just(
                \is_callable($f) ? \call_user_func($f, $data->offsetGet($key)) : $data->offsetGet($key)
            );
        }

        return static::nothing();
    }

    /**
     * Syntax sugar for more convenience when using in procedural style.
     *
     * @return bool
     */
    public function isJust()
    {
        return $this->hasValue === true;
    }

    /**
     * Syntax sugar for more convenience when using in procedural style.
     *
     * @return bool
     */
    public function isNothing()
    {
        return $this->hasValue === false;
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return $this->isJust() ? 'Just(' . \var_export($this->value, true) . ')' : 'Nothing';
    }
}
