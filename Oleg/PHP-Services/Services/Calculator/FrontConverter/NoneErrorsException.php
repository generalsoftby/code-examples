<?php

namespace App\Services\Calculators\FrontConverter;

/**
 * The exception when an instance of Errors is undefined.
 */
class NoneErrorsException extends \Exception
{
    protected $message = 'An instance of Errors is undefined.';

    public function __construct()
    {
        parent::__construct($this->message);
    }
}
