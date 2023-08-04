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
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            // error was suppressed with the @-operator
            if (error_reporting() === 0) {
                return false;
            }

            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        });

        if (PHP_VERSION_ID >= 70000) {
            try {
                $result = Either::right(call_user_func_array($this->value, func_get_args()));
            } catch (\Throwable $exception) {
                $result = Either::left($exception);
            }
        } else {
            try {
                $result = Either::right(call_user_func_array($this->value, func_get_args()));
            } catch (\Exception $exception) {
                $result = Either::left($exception);
            }
        }

        restore_error_handler();

        return $result;
    }
}
