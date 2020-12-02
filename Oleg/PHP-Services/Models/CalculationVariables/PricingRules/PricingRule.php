<?php

namespace App\Model\Calculator\CalculationVariables\PricingRules;

use App\Model\Calculator\PricingRulesOfAssemblies\Cost;

/**
 * The base interface for pricing rules of calculation variables.
 */
interface PricingRule
{
    /**
     * Fills the instance from an array.
     *
     * @param  array $array
     * @return void
     */
    public function fillFromArray(array $array);

    /**
     * Checks whether the settings of the pricing rule are correct.
     *
     * @return bool
     */
    public function isCorrect(): bool;

    /**
     * Sets an extra charge of the interval.
     *
     * @param  Cost $cost
     * @return void
     */
    public function setExtraCharge(Cost $cost): void;

    /**
     * Returns an extra charge of the interval.
     *
     * @return Cost
     */
    public function getExtraCharge(): Cost;

    /**
     * Sets an minimal price of the interval.
     *
     * @param  Cost $cost
     * @return void
     */
    public function setMinPrice(Cost $cost): void;

    /**
     * Returns an minimal price of the interval.
     *
     * @return Cost
     */
    public function getMinPrice(): Cost;

    /**
     * Calculates a price by the given edition size.
     *
     * @return int $size
     * @return Cost|null
     */
    public function calculateByEditionSize(int $size): ?Cost;

    /**
     * Returns data of the current instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array;
}
