<?php

namespace Basko\FunctionalTest\Functor;

use Basko\Functional\Functor\Writer;

class LogWriter extends Writer
{
    const TIME = '2024-02-17 18:47:43';

    protected function concat(Writer $m)
    {
        $newValue = '[' . static::TIME . ']' . $m->aggregation . "\n";

        return static::of($this->aggregation . $newValue, $m->extract());
    }
}
