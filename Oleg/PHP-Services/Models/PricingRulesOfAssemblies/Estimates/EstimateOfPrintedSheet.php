<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\Estimates;

use App\Model\Calculator\PricingRulesOfAssemblies\EstimateOfProducts;

/**
 * The interface to implement classes which keeps data of calculation
 * for a printed sheet.
 */
interface EstimateOfPrintedSheet extends EstimateOfProducts
{
    /**
     * Returns a number of material with wastes.
     *
     * @return float
     */
    public function getNumberOfMaterialWithWastes(): float;

    /**
     * Returns a priority of the printed sheet.
     *
     * @return int
     */
    public function getPriorityOfPrintedSheet(): int;
}
