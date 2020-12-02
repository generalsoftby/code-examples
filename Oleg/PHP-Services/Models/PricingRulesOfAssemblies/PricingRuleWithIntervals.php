<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies;

/**
 * The interface for pricing rules of assemblies with intervals.
 */
interface PricingRuleWithIntervals extends StandardPricingRule
{
    /**
     * Checks whether the instance has intervals.
     *
     * @return bool
     */
    public function hasIntervals(): bool;

    /**
     * Returns a IntervalCollection.
     *
     * @return IntervalCollection|IntervalOfProduct[]
     */
    public function getIntervalCollection(): IntervalCollection;

    /**
     * Sets an array with intervals to IntervalCollection.
     *
     * @param  array|IntervalOfProduct[] $intervals
     * @return void
     */
    public function setIntervals(array $intervals): void;
}
