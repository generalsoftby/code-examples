<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\PricingRulesByTypes\PrintedSheets;

use App\Model\Calculator\PricingRulesOfAssemblies\Estimates\PrintedSheet\EstimateOfPrintedSheet;
use App\Model\Calculator\PricingRulesOfAssemblies\StandardPricingRule;

/**
 * The interface for pricing rules of assemblies.
 */
interface PricingRuleOfPrintedSheets extends StandardPricingRule
{
    /**
     * Returns an EstimateOfPrintedSheet.
     *
     * @return EstimateOfPrintedSheet
     */
    public function getEstimateOfPrintedSheet(): ?EstimateOfPrintedSheet;
}
