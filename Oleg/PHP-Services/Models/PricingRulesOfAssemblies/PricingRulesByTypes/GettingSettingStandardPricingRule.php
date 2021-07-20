<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\PricingRulesByTypes;

use App\Model\Calculator\PricingRulesOfAssemblies\Cost;

/**
 * Handles a standard pricing rule.
 *
 * @property string $name A name of the pricing rule.
 * @property Cost $extraCharge An extra charge.
 * @property Cost $minPrice A minimal price to the pricing rule.
 */
trait GettingSettingStandardPricingRule
{
    /**
     * Returns a name of the pricing rule.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets a name to the pricing rule.
     *
     * @param  string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Returns an extra charge.
     *
     * @return Cost
     */
    public function getExtraCharge(): Cost
    {
        return $this->extraCharge;
    }

    /**
     * Sets an extra charge to the pricing rule.
     *
     * @param  Cost $extraCharge
     * @return void
     */
    public function setExtraCharge(Cost $extraCharge): void
    {
        $this->extraCharge = $extraCharge;
    }

    /**
     * Returns a min price for edition size.
     *
     * @return Cost
     */
    public function getMinPrice(): Cost
    {
        return $this->minPrice;
    }

    /**
     * Sets a minimal price to the pricing rule.
     *
     * @param  Cost $minPrice
     * @return void
     */
    public function setMinPrice(Cost $minPrice): void
    {
        $this->minPrice = $minPrice;
    }
}
