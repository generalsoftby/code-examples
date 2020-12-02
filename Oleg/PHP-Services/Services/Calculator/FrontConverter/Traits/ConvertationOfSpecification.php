<?php

namespace App\Services\Calculators\FrontConverter\Traits;

use App\Services\Calculators\FrontConverter\SpecificationDescriber;

/**
 * The default function to convert data of an estimate to an array for
 * a specification.
 */
trait ConvertationOfSpecification
{
    use ThrowsNoneEstimateException;

    /**
     * Converts data to an array of the format of the specification:
     * an array with strings.
     *
     * @return array|string[]
     */
    public function toArrayOfSpecification(): array
    {
        $this->throwNoneEstimateException();
        $specificationDescriber = new SpecificationDescriber($this->getEstimate());

        return $specificationDescriber->describe();
    }
}
