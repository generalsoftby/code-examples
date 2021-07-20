<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies;

/**
 * The interface for standard pricing rules of assemblies.
 */
interface StandardPricingRule extends PricingRule
{
    /**
     * Sets a name to the pricing rule.
     *
     * @param  string $name
     * @return void
     */
    public function setName(string $name): void;

    /**
     * Returns a name of the pricing rule.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Sets an extra charge to the pricing rule.
     *
     * @param  Cost $extraCharge
     * @return void
     */
    public function setExtraCharge(Cost $extraCharge): void;

    /**
     * Returns an extra charge.
     *
     * @return Cost
     */
    public function getExtraCharge(): Cost;

    /**
     * Sets a minimal price to the pricing rule.
     *
     * @param  Cost $minPrice
     * @return void
     */
    public function setMinPrice(Cost $minPrice): void;

    /**
     * Returns a min price for edition size.
     *
     * @return Cost
     */
    public function getMinPrice(): Cost;
}
