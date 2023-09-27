<?php

namespace Basko\Functional\Functor;

use Basko\Functional as f;

class IO extends Monad
{
    const of = "Basko\Functional\Functor\IO::of";

    /**
     * Wraps unsafe IO function `$f` like: read file, DB fetch, HTTP requests, etc.
     * IMPORTANT: throw Exception in `$f` to clearly show error path.
     *
     * @param callable $f
     * @return \Basko\Functional\Functor\IO
     */
    public static function of(callable $f)
    {
        if ($f instanceof static) {
            return $f;
        }

        return new static($f);
    }

    /**
     * @param callable $f
     * @return \Basko\Functional\Functor\IO
     */
    public function map(callable $f)
    {
        return static::of(f\compose($f, $this->extract()));
    }

    /**
     * Runs IO
     *
     * @return \Basko\Functional\Functor\Monad
     * @throws \ErrorException
     */
    public function __invoke()
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            $errLvl = error_reporting();
            $okLvl = 0; // Prior to PHP 8.0.0 https://www.php.net/manual/en/language.operators.errorcontrol.php
            if (PHP_VERSION_ID >= 80000) {
                $okLvl = E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR | E_PARSE;
            }
            // error was suppressed with the @-operator
            if ($errLvl === $okLvl) {
                return false;
            }

            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        });

        if (PHP_VERSION_ID >= 70000) {
            try {
                $result = Either::right(call_user_func_array($this->extract(), func_get_args()));
            } catch (\Throwable $exception) {
                $result = Either::left($exception);
            }
        } else {
            try {
                $result = Either::right(call_user_func_array($this->extract(), func_get_args()));
            } catch (\Exception $exception) {
                $result = Either::left($exception);
            }
        }

        restore_error_handler();

        return $result;
    }
}
