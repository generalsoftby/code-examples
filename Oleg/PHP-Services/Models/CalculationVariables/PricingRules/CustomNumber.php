<?php

namespace App\Model\Calculator\CalculationVariables\PricingRules;

use App\Model\Calculator\CalculationVariables\IntervalCollection;
use App\Model\Calculator\PricingRulesOfAssemblies\Cost;

/**
 * Keeps a collection with a custom number of products.
 */
class CustomNumber implements PricingRule
{
    const DEFAULT_CURRENCY = 'RUB';

    /**
     * A collection with intervals of numbers of sheets and their prices.
     *
     * @var IntervalCollection|IntervalOfEditionSize[]
     */
    protected $intervals;

    /**
     * An extra charge.
     *
     * @var Cost
     */
    protected $extraCharge;

    /**
     * A min price for the edition size (products).
     *
     * @var Cost
     */
    protected $minPrice;

    /**
     * Initializes an instance from the given array.
     *
     * @param array|null $array
     */
    public function __construct(array $array = null)
    {
        $this->fillFromArray($array ?? []);
    }

    /**
     * Fills the instance from an array.
     *
     * @param  array $array
     * @return void
     */
    public function fillFromArray(array $array)
    {
        $this->intervals = new IntervalCollection($this->makeIntervalsFromArray($array['intervals'] ?? []));
        $this->extraCharge = new Cost($array['extra_charge']['value'] ?? 0, $array['extra_charge']['currency'] ?? self::DEFAULT_CURRENCY);
        $this->minPrice = new Cost($array['min_price']['value'] ?? 0, $array['min_price']['currency'] ?? self::DEFAULT_CURRENCY);
    }

    /**
     * Checks whether the settings of the pricing rule are correct.
     *
     * @return bool
     */
    public function isCorrect(): bool
    {
        return $this->intervals->count();
    }

    /**
     * Sets a collection with intervals.
     *
     * @param  IntervalCollection|IntervalOfEditionSize[] $intervals
     * @return void
     */
    public function setIntervals(IntervalCollection $intervals): void
    {
        $this->intervals = $intervals;
    }

    /**
     * Returns a collection with intervals.
     *
     * @return IntervalCollection
     */
    public function getIntervals(): IntervalCollection
    {
        return $this->intervals;
    }

    /**
     * Sets an extra charge of the interval.
     *
     * @param  Cost $cost
     * @return void
     */
    public function setExtraCharge(Cost $cost): void
    {
        $this->extraCharge = $cost;
    }

    /**
     * Returns an extra charge of the interval.
     *
     * @return Cost
     */
    public function getExtraCharge(): Cost
    {
        return $this->extraCharge;
    }

    /**
     * Sets an minimal price of the interval.
     *
     * @param  Cost $cost
     * @return void
     */
    public function setMinPrice(Cost $cost): void
    {
        $this->minPrice = $cost;
    }

    /**
     * Returns an minimal price of the interval.
     *
     * @return Cost
     */
    public function getMinPrice(): Cost
    {
        return $this->minPrice;
    }

    /**
     * Finds an interval by a number of sheets.
     *
     * @param  float|int $value
     * @return IntervalOfEditionSize|null
     */
    public function findInterval($value): ?IntervalOfEditionSize
    {
        return $this->intervals->getIntervalByValue($value);
    }

    /**
     * Calculates a price by the given edition size.
     *
     * @return int $size
     * @return Cost|null
     */
    public function calculateByEditionSize(int $size): ?Cost
    {
        $interval = $this->findInterval($size);

        if (!$interval) {
            return null;
        }

        $cost = new Cost($interval->getCost()->getValueByCurrency(), 'RUB');
        $price = $cost->getValueByCurrency() * $size;
        $price += $this->getExtraCharge()->getValueByCurrency();
        $minPrice = $this->getMinPrice()->getValueByCurrency();

        return $price > $minPrice
            ? new Cost($price, 'RUB')
            : new Cost($minPrice, 'RUB')
        ;
    }

    /**
     * Returns data of the current instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'intervals' => $this->intervals->toArray(),
            'extra_charge' => $this->extraCharge->toArray(),
            'min_price' => $this->minPrice->toArray(),
        ];
    }

    /**
     * Makes an array and returns its with intervals
     * from a custom array with intervals.
     *
     * @param array $rawIntervals Raw intervals values.
     *
     * @return array|IntervalOfEditionSize[]
     */
    public function makeIntervalsFromArray(array $rawIntervals): array
    {
        $intervals = array_filter($rawIntervals, function ($interval) {
            return isset(
                $interval['start'],
                $interval['price']['value'],
                $interval['price']['currency'],
            );
        });

        return array_map(function ($item) {
            return new IntervalOfEditionSize($item);
        }, $intervals);
    }
}
