<?php

namespace App\Model\Calculator\CalculationVariables;

use App\Model\Calculator\PricingRulesOfAssemblies\Cost;

/**
 * Keeps data of the interval: a start value, a value of the end, a cost.
 */
class Interval
{
    /**
     * A value of the interval start.
     *
     * @var float|int
     */
    protected $start;

    /**
     * A value of the interval end.
     *
     * @var float|int|null
     */
    protected $end;

    /**
     * A cost of the interval.
     *
     * @var Cost
     */
    protected $cost;

    /**
     * Initializes an instance from the given array.
     *
     * @param array $array
     */
    public function __construct(array $array = [])
    {
        $this->fillFromArray($array);
    }

    /**
     * Fills the instance from the given array.
     *
     * @param  array $array
     * @return void
     */
    public function fillFromArray(array $array = [])
    {
        $this->start = $array['start'];
        $this->end = $array['end'];
        $this->cost = new Cost($array['price']['value'] ?? 0, $array['price']['currency'] ?? 'RUB');
    }

    /**
     * Returns an interval start.
     *
     * @return float|int
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Returns an interval end.
     *
     * @return float|int|null
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Returns a cost of the interval.
     *
     * @return Cost
     */
    public function getCost(): Cost
    {
        return $this->cost;
    }

    /**
     * Returns a value of the cost.
     *
     * @return string
     */
    public function getValueOfCost(): float
    {
        return $this->cost->getValue();
    }

    /**
     * Returns a currency of the cost.
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
     * Checks the value is included in the interval.
     *
     * @param  float|int $value
     * @return bool
     */
    public function isIncluded($value): bool
    {
        return $this->start <= $value
            && ((isset($this->end) && $this->end >= $value) || empty($this->end))
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
            'start' => $this->start,
            'end' => $this->end,
            'price' => $this->cost->toArray(),
        ];
    }
}
