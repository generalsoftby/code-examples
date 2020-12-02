<?php

namespace App\Services\Calculators\FrontConverter\Traits;

use App\Services\Calculators\FrontConverter\ReactCIDescriber;

/**
 * The default function to convert data of an estimate to an array for
 * the calculation interface of React.
 */
trait ConvertationOfReactFE
{
    use ThrowsNoneEstimateException;

    /**
     * Converts data to an array of the format of React's front-end
     * of the calculator interface.
     *
     * @return array
     *
     * @throws NoneEstimateException
     */
    public function toArrayOfReactFE(): array
    {
        $this->throwNoneEstimateException();
        $decriber = new ReactCIDescriber($this->getEstimate(), $this->getDebugMode());

        return $decriber->describe()->toArray();
    }
}
