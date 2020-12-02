<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\PricingRulesByTypes;

use App\Model\Calculator\PricingRulesOfAssemblies\Cost;
use App\Support\Number;

/**
 * Initializes additional prices: an extra charge and an minimal price.
 */
trait InitializationOfAdditionalPrices
{
    /**
     * Initializes a Cost with an extra charge from an array with thier values.
     *
     * @param  array $values
     * @return Cost
     */
    public function newExtraCharge(array $values): Cost
    {
        /** @var float $value */
        $value = isset($values['value'])
            ? Number::normalizeFloat($values['value'])
            : $this->getDefaultValueOfExtraCharge()
        ;

        return new Cost(
            $value,
            $values['currency'] ?? $this->getDefaultCurrencyOfExtraCharge()
        );
    }

    /**
     * Initializes a Cost with a min price from an array with thier values.
     *
     * @param  array $values
     * @return Cost
     */
    public function newMinPrice(array $values): Cost
    {
        return new Cost(
            $values['value'] ?? $this->getDefaultValueOfMinimalPrice(),
            $values['currency'] ?? $this->getDefaultCurrencyOfMinimalPrice()
        );
    }

    /**
     * Returns the default value of a extra charge.
     *
     * @return float
     */
    protected function getDefaultValueOfExtraCharge(): float
    {
        return 0;
    }

    /**
     * Returns the default currency of a extra charge.
     *
     * @return string
     */
    protected function getDefaultCurrencyOfExtraCharge(): string
    {
        return 'RUB';
    }

    /**
     * Returns the default value of a minimal price.
     *
     * @return float
     */
    protected function getDefaultValueOfMinimalPrice(): float
    {
        return 0;
    }

    /**
     * Returns the default currency of a minimal charge.
     *
     * @return string
     */
    protected function getDefaultCurrencyOfMinimalPrice(): string
    {
        return 'RUB';
    }
}
