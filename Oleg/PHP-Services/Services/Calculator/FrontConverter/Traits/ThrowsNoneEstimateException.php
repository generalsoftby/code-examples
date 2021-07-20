<?php

namespace App\Services\Calculators\FrontConverter\Traits;

use App\Services\Calculators\Estimate;
use App\Services\Calculators\FrontConverter\NoneEstimateException;

/**
 * Throws the exception when an estimate is undefined.
 *
 * @property Estimate $estimate
 */
trait ThrowsNoneEstimateException
{
    public function throwNoneEstimateException(): void
    {
        if (!isset($this->estimate)) {
            throw new NoneEstimateException();
        }
    }
}
