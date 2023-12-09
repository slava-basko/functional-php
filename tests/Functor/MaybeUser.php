<?php

namespace Basko\FunctionalTest\Functor;


use Basko\Functional\Functor\Maybe;
use Basko\Functional\Functor\Type;
use Basko\FunctionalTest\Helpers\User;

class MaybeUser extends Maybe implements Type
{
    public static function type()
    {
        return User::class;
    }
}