<?php

namespace App\Model\Calculator\CalculationVariables\PricingRules;

use App\Model\Calculator\CalculationVariables\Interval;

/**
 * Keeps inverval data for an edition size of products.
 */
class IntervalOfEditionSize extends Interval
{
    /**
     * A start value of the interval.
     *
     * @var int
     */
    protected $start;

    /**
     * An end value of the interval.
     *
     * @var int|null
     */
    protected $end;

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
        parent::fillFromArray($array);

        $this->start = (int) $this->start;
        $this->end = isset($this->end) ? (int) $this->end : null;
    }

    /**
     * Returns a value of the start of the interval.
     *
     * @return int
     */
    public function getStart(): int
    {
        return $this->start;
    }

    /**
     * Returns a value of the end of the interval.
     *
     * @return int|null
     */
    public function getEnd(): ?int
    {
        return $this->end;
    }
}
