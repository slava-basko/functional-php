<?php

namespace Basko\Functional\Functor;

use Basko\Functional as f;
use Basko\Functional\Exception\TypeException;

/**
 * @template-extends \Basko\Functional\Functor\Monad<mixed>
 */
class IO extends Monad
{
    const of = "Basko\Functional\Functor\IO::of";

    /**
     * Wraps unsafe IO function `$f` like: read file, DB fetch, HTTP requests, etc.
     * IMPORTANT: throw Exception in `$f` to clearly show error path.
     *
     * @param callable $f
     * @return static
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
     * @return static
     */
    public function map(callable $f)
    {
        return static::of(f\compose($f, $this->value));
    }

    /**
     * @param callable(mixed):static $f
     * @return static
     * @throws \Basko\Functional\Exception\TypeException
     */
    public function flatMap(callable $f)
    {
        $result = \call_user_func($f, $this->__invoke());

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

        if ($m == Maybe::class) {
            try {
                $value = \call_user_func($this);
                return $value === null ? Maybe::nothing() : Maybe::just($value);
            } catch (\Exception $e) {
                return Maybe::nothing();
            }
        } elseif ($m == Either::class) {
            try {
                return Either::right(\call_user_func($this));
            } catch (\Exception $e) {
                return Either::left($e);
            }
        } elseif ($m == Optional::class) {
            try {
                return Optional::just(\call_user_func($this));
            } catch (\Exception $e) {
                return Optional::nothing();
            }
        } elseif ($m == Constant::class) {
            return Constant::of(\call_user_func($this));
        } elseif ($m == Identity::class) {
            return Identity::of(\call_user_func($this));
        } elseif ($m == Writer::class) {
            return Writer::of([], \call_user_func($this));
        } elseif ($m == EitherWriter::class) {
            try {
                return EitherWriter::right(\call_user_func($this));
            } catch (\Exception $e) {
                return EitherWriter::left($e);
            }
        }

        $this->cantTransformException($m);
    }

    /**
     * Runs IO
     *
     * @return mixed
     */
    public function __invoke()
    {
        return \call_user_func_array($this->value, \func_get_args());
    }
}
