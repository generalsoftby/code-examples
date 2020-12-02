<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\PricingRulesByTypes;

use App\Model\Calculator\PricingRulesOfAssemblies\Cost;
use App\Model\Calculator\PricingRulesOfAssemblies\StandardPricingRule;

/**
 * The interface of a standard pricing rule for initialization
 * of the additinal prices of a pricing rule.
 */
interface PricingRuleWithInitializationOfAdditionalPrices extends StandardPricingRule
{
    /**
     * Initializes a Cost with an extra charge from an array with thier values.
     *
     * @param  array $values
     * @return Cost
     */
    public function newExtraCharge(array $values): Cost;

    /**
     * Initializes a Cost with a min price from an array with thier values.
     *
     * @param  array $values
     * @return Cost
     */
    public function newMinPrice(array $values): Cost;
}
