<?php

namespace Basko\FunctionalTest\Functor;


use Basko\Functional\Functor\Maybe;
use Basko\Functional\Functor\Type;

class MaybeString extends Maybe implements Type
{
    public static function type()
    {
        return 'string';
    }
}