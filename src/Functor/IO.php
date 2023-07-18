<?php

namespace Basko\Functional\Functor;

use Basko\Functional as f;

class IO extends Monad
{
    const of = "Basko\Functional\Functor\IO::of";

    public static function of(callable $value)
    {
        if ($value instanceof static) {
            return $value;
        }

        return new static($value);
    }

    public function map(callable $f)
    {
        return static::of(f\compose($f, $this->value));
    }

    public function __invoke()
    {
        if (PHP_VERSION_ID >= 70000) {
            try {
                return Either::right(call_user_func_array($this->value, func_get_args()));
            } catch (\Throwable $exception) {
                return Either::left($exception);
            }
        } else {
            try {
                return Either::right(call_user_func_array($this->value, func_get_args()));
            } catch (\Exception $exception) {
                return Either::left($exception);
            }
        }
    }
}
