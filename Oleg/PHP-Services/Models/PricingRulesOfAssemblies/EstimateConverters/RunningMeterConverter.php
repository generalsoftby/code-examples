<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\EstimateConverters;

use App\Services\Calculators\FrontConverter\AbstractEstimateConverter;

/**
 * Implements convertation of RunningMeter's estimate.
 */
class RunningMeterConverter extends AbstractEstimateConverter
{
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

        // $this->estimate

        // TODO Преобразование сметы в массив

        return [];
    }
}
