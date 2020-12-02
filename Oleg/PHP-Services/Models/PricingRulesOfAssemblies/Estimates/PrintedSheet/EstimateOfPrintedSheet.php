<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\Estimates\PrintedSheet;

use App\Model\Calculator\PricingRulesOfAssemblies\Estimates\EstimateOfPrintedSheet as BaseEstimateOfPrintedSheet;

/**
 * The interface to implement classes which contains data of calculation for a printed sheet.
 */
interface EstimateOfPrintedSheet extends BaseEstimateOfPrintedSheet
{
    /**
     * Returns a number of printed sheets.
     *
     * @return int
     */
    public function getNumberOfPrintedSheets(): int;

    /**
     * Returns a height of the product.
     *
     * @return int
     */
    public function getHeightOfProduct(): int;

    /**
     * Returns a width of the product.
     *
     * @return int
     */
    public function getWidthOfProduct(): int;
}
