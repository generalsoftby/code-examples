<?php

namespace App\Services\Calculators\FrontConverter\Traits;

use App\Services\Calculators\Errors;
use App\Services\Calculators\FrontConverter\NoneErrorsException;

/**
 * Throws the exception when an instance of Errors is undefined.
 *
 * @property Errors $errors
 */
trait ThrowsNoneErrorsException
{
    /**
     * Throws the exception.
     *
     * @return void
     *
     * @throws NoneErrorsException
     */
    public function throwNoneErrorsException(): void
    {
        if (!isset($this->errors)) {
            throw new NoneErrorsException();
        }
    }
}
