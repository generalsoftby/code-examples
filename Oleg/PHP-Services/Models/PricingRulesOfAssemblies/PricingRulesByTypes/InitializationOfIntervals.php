<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\PricingRulesByTypes;

use App\Model\Calculator\PricingRulesOfAssemblies\Cost;
use App\Model\Calculator\PricingRulesOfAssemblies\IntervalOfProduct;
use App\Support\Number;

/**
 * Initializes intervals of products.
 */
trait InitializationOfIntervals
{
    /**
     * Defines an array with IntervalOfProduct.
     *
     * @param  array  $intervals
     * @return array|IntervalOfProduct[]
     */
    protected function defineInvervalOfProductsFromArray(array $intervals): array
    {
        return array_map([$this, 'newIntervalFromArray'], $intervals);
    }

    /**
     * Prepares the given array and initializes an instance of IntervalOfProduct
     * with prepared data.
     *
     * @param  array $interval Data of the interval.
     * @return IntervalOfProduct
     */
    public function newIntervalFromArray(array $interval): IntervalOfProduct
    {
        /** @var float|int $start */
        $start = isset($interval['start']) ? Number::normalizeFloat($interval['start']) : 1;

        /** @var float|null $end */
        $end = isset($interval['end']) ? Number::normalizeFloat($interval['end']) : null;

        /** @var float|null $cost */
        $cost = isset($interval['cost']) ? Number::normalizeFloat($interval['cost']) : $this->getDefaultCostOfInterval();

        /** @var int $days */
        $days = isset($interval['days']) ? (int) $interval['days'] : 0;

        /** @var bool $weekend */
        $weekend = isset($interval['weekend']) ? filter_var($interval['weekend'], FILTER_VALIDATE_BOOLEAN) : false;

        return new IntervalOfProduct(
            $start,
            $end,
            new Cost(
                $cost,
                $interval['currency'] ?? $this->getDefaultCurrentOfInterval()
            ),
            $interval['unit'] ?? $this->getDefaultPriceUnitOfInterval(),
            $days,
            $weekend
        );
    }

    protected function getDefaultCostOfInterval(): int
    {
        return 0;
    }

    protected function getDefaultCurrentOfInterval(): string
    {
        return 'RUB';
    }

    protected function getDefaultPriceUnitOfInterval(): string
    {
        return IntervalOfProduct::PER_ONE_UNIT;
    }
}
