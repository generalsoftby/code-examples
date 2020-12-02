<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\PricingRulesByTypes;

use App\Model\Calculator\PricingRulesOfAssemblies\IntervalCollection;
use App\Model\Calculator\PricingRulesOfAssemblies\IntervalOfProduct;

/**
 * Implements base methods of intervals (PricingRuleWithIntervals).
 *
 * @property IntervalCollection $intervals A collection with intervals of products.
 */
trait MethodsOfIntervals
{
    /**
     * Checks whether the instance has intervals.
     *
     * @return bool
     */
    public function hasIntervals(): bool
    {
        return $this->intervals->count();
    }

    /**
     * Returns a IntervalCollection.
     *
     * @return IntervalCollection|IntervalOfProduct[]
     */
    public function getIntervalCollection(): IntervalCollection
    {
        return $this->intervals;
    }

    /**
     * Sets an array with intervals to IntervalCollection.
     *
     * @param  array|IntervalOfProduct[] $intervals
     * @return void
     */
    public function setIntervals(array $intervals): void
    {
        $this->intervals->setIntervals($intervals);
    }
}
