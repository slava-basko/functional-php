<?php

namespace Basko\Functional\Functor;

use Basko\Functional\Exception\TypeException;

/**
 * @template-extends \Basko\Functional\Functor\Monad<mixed>
 */
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
        if ($value instanceof static) {
            return $value;
        }

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
     * @param callable $f
     * @return static
     */
    public function map(callable $f)
    {
        if ($this->hasValue) {
            return static::just(\call_user_func_array($f, [$this->value]));
        }

        return static::nothing();
    }

    /**
     * @param callable(mixed):static $f
     * @return static
     * @throws \Basko\Functional\Exception\TypeException
     */
    public function flatMap(callable $f)
    {
        if ($this->hasValue) {
            $result = \call_user_func($f, $this->value);
        } else {
            $result = static::nothing();
        }

        TypeException::assertReturnType($result, static::class, __METHOD__);

        return $result;
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
            return $this->isJust()
                ? Either::right($value)
                : Either::left('Nothing');
        } elseif ($m == Constant::class) {
            return Constant::of($value);
        } elseif ($m == Identity::class) {
            return Identity::of($value);
        } elseif ($m == IO::class) {
            return IO::of(function () use ($value) {
                return $value;
            });
        } elseif ($m == Writer::class) {
            return Writer::of([], $value);
        } elseif ($m == EitherWriter::class) {
            return $this->isJust()
                ? EitherWriter::right($value)
                : EitherWriter::left('Nothing');
        }

        $this->cantTransformException($m);
    }

    /**
     * @param callable $just
     * @param callable $nothing
     * @return \Basko\Functional\Functor\Optional
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
     * @param string|int $key
     * @param array|\ArrayAccess|object $data
     * @param callable|null $f
     * @return static
     */
    public static function fromProp($key, $data, callable $f = null)
    {
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

    public function __toString()
    {
        return $this->isJust() ? 'Just(' . \var_export($this->value, true) . ')' : 'Nothing';
    }
}
