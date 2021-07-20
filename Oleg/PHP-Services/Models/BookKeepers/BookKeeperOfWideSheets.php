<?php

namespace App\Model\Calculator\BookKeepers;

use App\Model\Calculator\CalculatorAssembly as Assembly;
use App\Model\Calculator\CalculatorPrintedSheet;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRule;
use App\Model\Calculator\PricingRulesOfAssemblies\WidePrintedSheets;
use App\Services\Calculators\Error;

/**
 * Calculates a price of products of wide printed sheets.
 */
abstract class BookKeeperOfWideSheets extends BookKeeperOfPrintedSheets
{
    /**
     * Returns an array with types of pricing rules.
     *
     * @return array|string[]
     */
    public function getTypesOfPricingRules(): array
    {
        return [
            Assembly::COPY_TYPE,
            Assembly::WIDE_PRINTED_SHEETS_TYPE,
        ];
    }

    /**
     * Returns an array with types of printed sheets.
     *
     * @return array|string[]
     */
    public function getTypesOfPrintedSheets(): array
    {
        return [CalculatorPrintedSheet::WIDE_FORMAT];
    }

    /**
     * Checks the PricingRule to calculate a price of products.
     *
     * @param  PricingRule $pricingRule
     * @return bool
     */
    public function checkPricingRule(PricingRule $pricingRule): bool
    {
        if (!($pricingRule instanceof WidePrintedSheets)) {
            $this->throwInvalidPricingRule($pricingRule, [WidePrintedSheets::class]);
            $this->errors->add(trans('calculator_errors.pricing_rule_is_invalid'), Error::SYSTEM_ERROR);
            return false;
        }

        return parent::checkPricingRule($pricingRule);
    }
}
