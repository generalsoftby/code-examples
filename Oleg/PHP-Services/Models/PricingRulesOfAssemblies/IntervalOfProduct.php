<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies;

/**
 * Contains data of an inverval for products.
 */
class IntervalOfProduct
{
    /**
     * Types of an unit of a cost of the interval.
     */

    /**
     * The cost per one unit.
     *
     * @var string
     */
    const PER_ONE_UNIT = 'one_unit';

    /**
     * The cost per all products.
     *
     * @var string
     */
    const PER_ALL_PRODUCTS = 'all_products';

    /**
     * A start value of the interval.
     *
     * @var float
     */
    protected $start;

    /**
     * An end value of the interval.
     *
     * @var float|null
     */
    protected $end;

    /**
     * A cost per the interval.
     *
     * @var Cost
     */
    protected $cost;

    /**
     * A cost unit of the interval.
     *
     * @var string
     */
    protected $costUnit;

    /**
     * Days for implementation of the order
     *
     * @var int
     */
    protected $days;

    /**
     * Working on weekends.
     *
     * @var bool
     */
    protected $weekend;

    /**
     * Initializes an instance with the interval.
     *
     * @param float       start     A start value of the interval
     * @param float|null  $end      An end value of the interval
     * @param Cost        $cost     A cost per the interval
     * @param string      $costUnit A cost unit of the interval
     * @param int         $days     Days for implementation of the order
     * @param bool        $weekend  Working on weekends
     */
    public function __construct(
        float $start,
        float $end = null,
        Cost $cost,
        string $costUnit,
        int $days = 0,
        bool $weekend = false
    ) {
        $this->start = $start > 0 ? $start : 1;
        $this->end = isset($end) && $end > $this->start ? $end : null;
        $this->cost = $cost;
        $this->costUnit = $costUnit;
        $this->days = $days > 0 ? $days : 0;
        $this->weekend = $weekend;
    }

    /**
     * Checks whether the value is included in the interval.
     *
     * @param  float $value
     * @return bool
     */
    public function isIncluded(float $value): bool
    {
        return $this->start <= $value
            && ((isset($this->end) && $this->end >= $value) || empty($this->end))
        ;
    }

    /**
     * Returns a value of the start of the interval.
     *
     * @return float
     */
    public function getStart(): float
    {
        return $this->start;
    }

    /**
     * Returns a value of the end of the interval.
     *
     * @return float|null
     */
    public function getEnd(): ?float
    {
        return $this->end;
    }

    /**
     * Returns a Cost of the interval.
     *
     * @return Cost
     */
    public function getCost(): Cost
    {
        return $this->cost;
    }

    /**
     * Returns a value of the Cost.
     *
     * @return float
     */
    public function getValueOfCost(): float
    {
        return $this->cost->getValue();
    }

    /**
     * Returns a currency of the Cost.
     *
     * @return string
     */
    public function getCurrencyOfCost(): string
    {
        return $this->cost->getCurrency();
    }

    /**
     * Returns a value of the cost in the form of Russian rubles.
     *
     * @return float
     */
    public function getValueByCurrency(): float
    {
        return $this->cost->getValueByCurrency();
    }

    /**
     * Returns a cost unit of the interval.
     *
     * @return string
     */
    public function getCostUnit(): string
    {
        return $this->costUnit;
    }

    /**
     * Returns a number of days for making products.
     *
     * @return int
     */
    public function getDays(): int
    {
        return $this->days;
    }

    /**
     * Checks whether the workers makes products on weekends.
     *
     * @return bool
     */
    public function worksOnWeekends(): bool
    {
        return $this->weekend;
    }

    /**
     * Returns an instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'start' => $this->start,
            'end' => $this->end,
            'cost' => $this->cost->getValue(),
            'currency' => $this->cost->getCurrency(),
            'unit' => $this->costUnit,
            'days' => $this->days,
            'weekend' => $this->weekend,
        ];
    }
}
