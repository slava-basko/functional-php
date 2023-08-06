<?php

namespace Basko\Functional\Functor;

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
     * @param mixed $value
     */
    final protected function __construct($value)
    {
        parent::__construct($value);
    }

    /**
     * @param bool $hasValue
     * @param mixed $value
     * @return \Basko\Functional\Functor\Optional
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
     * @return \Basko\Functional\Functor\Optional
     */
    public static function just($value)
    {
        return static::of(true, $value);
    }

    /**
     * @return \Basko\Functional\Functor\Optional
     */
    public static function nothing()
    {
        return static::of(false, null);
    }

    /**
     * @param callable $f
     * @return \Basko\Functional\Functor\Optional
     */
    public function map(callable $f)
    {
        if ($this->hasValue) {
            return static::just(call_user_func_array($f, [$this->value]));
        }

        return $this::nothing();
    }

    /**
     * @param callable $just
     * @param callable $nothing
     * @return void
     */
    public function match(callable $just, callable $nothing)
    {
        if ($this->hasValue) {
            call_user_func_array($just, [$this->value]);
        } else {
            call_user_func($nothing);
        }
    }

    /**
     * @param string|int $key
     * @param array $data
     * @param callable|null $f
     * @return \Basko\Functional\Functor\Optional
     */
    public static function fromArrayKey($key, array $data, callable $f = null)
    {
        if (array_key_exists($key, $data)) {
            return static::just(is_callable($f) ? call_user_func_array($f, [$data[$key]]) : $data[$key]);
        }

        return static::nothing();
    }
}
