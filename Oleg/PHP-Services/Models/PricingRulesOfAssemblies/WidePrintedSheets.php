<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies;

use App\Model\Calculator\PricingRulesOfAssemblies\PricingRulesByTypes\WidePrintedSheets\CustomEditionSize;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRulesByTypes\WidePrintedSheets\PricingRuleOfWidePrintedSheets;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRulesByTypes\WidePrintedSheets\Wholesale;
use App\Model\Calculator\PricingRulesOfAssemblies\Values\ValuesOfWideSheets;

/**
 * The class of pricing rules for the 'wide printed sheets' type.
 */
class WidePrintedSheets extends AbstractPrintedSheets
{
    /**
     * Initializes a new rules by the given type with the given rules.
     *
     * @param  string $type
     * @param  array  $rules
     * @return PricingRuleOfWidePrintedSheets
     *
     * @throws UnsupportedPricingRuleException
     */
    public function newPricingRuleFromArray(string $type, array $rules): PricingRule
    {
        switch ($type) {
            case 'custom_edition_size':
                return new CustomEditionSize($rules);
            case 'wholesale':
                return new Wholesale($rules);
        }

        throw new UnsupportedPricingRuleException($type);
    }

    /**
     * Returns allowed types of ValueKeeper.
     *
     * @return array|string[]
     */
    public function getAllowedTypesOfValueKeeper(): array
    {
        return [ValuesOfWideSheets::class];
    }

    /**
     * Checks whether the given ValueKeeper is allowed.
     *
     * @param  ValueKeeper $valueKeeper
     * @return bool
     */
    public function isValueKeeperAllowed(ValueKeeper $valueKeeper): bool
    {
        return $valueKeeper instanceof ValuesOfWideSheets;
    }
}
