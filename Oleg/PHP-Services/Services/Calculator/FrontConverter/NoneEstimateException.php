<?php

namespace App\Services\Calculators\FrontConverter;

/**
 * The exception when an estimate is undefined.
 */
class NoneEstimateException extends \Exception
{
    protected $message = 'Estimate is undefined.';

    public function __construct()
    {
        parent::__construct($this->message);
    }
}
