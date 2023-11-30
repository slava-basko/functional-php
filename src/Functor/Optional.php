<?php

namespace Basko\Functional\Functor;

use Basko\Functional\Exception\TypeException;

/**
 * @template-extends \Basko\Functional\Functor\Monad<mixed>
 */
final class Optional extends Monad
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
     * @return \Basko\Functional\Functor\Optional
     */
    public static function of($hasValue, $value)
    {
        if ($value instanceof Optional) {
            return $value;
        }

        $m = new Optional($value);
        $m->hasValue = $hasValue;

        return $m;
    }

    /**
     * @param mixed $value
     * @return \Basko\Functional\Functor\Optional
     */
    public static function just($value)
    {
        return Optional::of(true, $value);
    }

    /**
     * @return \Basko\Functional\Functor\Optional
     */
    public static function nothing()
    {
        return Optional::of(false, null);
    }

    /**
     * @param callable $f
     * @return \Basko\Functional\Functor\Optional
     */
    public function map(callable $f)
    {
        if ($this->hasValue) {
            return Optional::just(\call_user_func_array($f, [$this->value]));
        }

        return Optional::nothing();
    }

    /**
     * @param callable(mixed):\Basko\Functional\Functor\Optional $f
     * @return \Basko\Functional\Functor\Optional
     * @throws \Basko\Functional\Exception\TypeException
     */
    public function flatMap(callable $f)
    {
        if ($this->hasValue) {
            $result = \call_user_func($f, $this->value);
        } else {
            $result = Optional::nothing();
        }

        TypeException::assertReturnType($result, Optional::class, __METHOD__);

        return $result;
    }

    /**
     * @param callable $just
     * @param callable $nothing
     * @return void
     */
    public function match(callable $just, callable $nothing)
    {
        if ($this->hasValue) {
            \call_user_func_array($just, [$this->extract()]);
        } else {
            \call_user_func($nothing);
        }
    }

    /**
     * @param string|int $key
     * @param array|\ArrayAccess|object $data
     * @param callable|null $f
     * @return \Basko\Functional\Functor\Optional
     */
    public static function fromProp($key, $data, callable $f = null)
    {
        if (\is_array($data) && \array_key_exists($key, $data)) {
            return Optional::just(\is_callable($f) ? \call_user_func_array($f, [$data[$key]]) : $data[$key]);
        }

        if (\is_object($data) && property_exists($data, $key)) {
            return Optional::just(\is_callable($f) ? \call_user_func_array($f, [$data->{$key}]) : $data->{$key});
        }

        if ($data instanceof \ArrayAccess && $data->offsetExists($key)) {
            return Optional::just(
                \is_callable($f) ? \call_user_func_array($f, [$data->offsetGet($key)]) : $data->offsetGet($key)
            );
        }

        return Optional::nothing();
    }
}
