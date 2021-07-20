<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\PricingRulesByTypes\WidePrintedSheets;

use App\Model\Calculator\PricingRulesOfAssemblies\Estimates\EstimateOfPrintedSheet;
use App\Model\Calculator\PricingRulesOfAssemblies\StandardPricingRule;

/**
 * The interface for pricing rules of assemblies.
 */
interface PricingRuleOfWidePrintedSheets extends StandardPricingRule
{
    /**
     * Returns an EstimateOfPrintedSheet.
     *
     * @return EstimateOfPrintedSheet
     */
    public function getEstimateOfPrintedSheet(): ?EstimateOfPrintedSheet;
}
