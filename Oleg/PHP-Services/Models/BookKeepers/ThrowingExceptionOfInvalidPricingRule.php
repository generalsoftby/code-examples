<?php

namespace App\Model\Calculator\BookKeepers;

use App\Model\Calculator\PricingRulesOfAssemblies\PricingRule;

/**
 * Throws the Exception when an invalid PricingRule is used and debugging is enabled.
 */
trait ThrowingExceptionOfInvalidPricingRule
{
    /**
     * Throws the Exception when the given PricingRule is invalid.
     *
     * @param  PricingRule    $rule
     * @param  array|string[] $validClasses
     * @return void
     * 
     * @throws InvalidPricingRuleException
     */
    protected function throwInvalidPricingRule(PricingRule $rule, $validClasses = [])
    {
        if (isset($this->throwExceptions) && $this->throwExceptions) {
            throw new InvalidPricingRuleException($rule, $validClasses);
        }
    }
}
