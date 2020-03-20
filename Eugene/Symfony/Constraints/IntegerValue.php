<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class IntegerValue extends Constraint
{
    const INTEGER_VALUE = self::class;

    public $message = 'Should be integer.';

    protected static $errorNames = [
        self::INTEGER_VALUE => self::class,
    ];
}
