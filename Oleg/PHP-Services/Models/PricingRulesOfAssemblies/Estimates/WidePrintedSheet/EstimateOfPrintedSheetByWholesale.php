<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\Estimates\WidePrintedSheet;

use App\Services\Calculators\EstimateGroup;
use App\Model\Calculator\PricingRulesOfAssemblies\Values\ValuesOfRunningMeter;
use App\Model\Calculator\PricingRulesOfAssemblies\Cost;
use App\Model\Calculator\PricingRulesOfAssemblies\ValueKeeper;

/**
 * Contains data of calculation for a printed sheet.
 */
class EstimateOfPrintedSheetByWholesale extends BaseEstimateOfWidePrintedSheet
{
    /**
     * An original cost of products.
     *
     * @var float
     */
    protected $originalCostOfProducts;

    /**
     * An original cost of products with extra charge.
     *
     * @var float
     */
    protected $costOfProductsWithExtraCharge;

    /**
     * A state of using the rule of minimal price.
     *
     * @var bool
     */
    protected $ruleOfMinimalPriceWasUsed;

    /**
     * A state of using the rule of minimal cost per unit.
     *
     * @var bool
     */
    protected $ruleOfMinimalCostPerUnitWasUsed;

    /**
     * Initializes an instance of the class with values of the calculation.
     *
     * @param string $nameOfPricingRule A name of the used pricing rule.
     * @param ValuesOfRunningMeter $valueKeeper Values of the calculation.
     * @param float  $priceOfProducts A final price of products.
     * @param float  $costOfProducts A cost of products.
     * @param Cost   $extraCharge An extra charge.
     * @param float  $originalCostOfProducts An original cost of products
     * @param float  $costOfProductsWithExtraCharge An original price of products
     * @param bool   $ruleOfMinimalPriceWasUsed A state of using the rule of minimal price.
     * @param bool   $ruleOfMinimalCostPerUnitWasUsed  A state of using the rule of minimal cost.
     */
    public function __construct(
        string $nameOfPricingRule,
        ValueKeeper $valueKeeper,
        float $priceOfProducts,
        float $costOfProducts,
        Cost $extraCharge,
        float $originalCostOfProducts,
        float $costOfProductsWithExtraCharge,
        bool $ruleOfMinimalPriceWasUsed,
        bool $ruleOfMinimalCostPerUnitWasUsed
    ) {
        parent::__construct(
            $nameOfPricingRule,
            $valueKeeper,
            $priceOfProducts,
            $costOfProducts,
            $extraCharge,
        );

        $this->originalCostOfProducts = $originalCostOfProducts;
        $this->costOfProductsWithExtraCharge = $costOfProductsWithExtraCharge;
        $this->ruleOfMinimalPriceWasUsed = $ruleOfMinimalPriceWasUsed;
        $this->ruleOfMinimalCostPerUnitWasUsed = $ruleOfMinimalCostPerUnitWasUsed;
    }

    /**
     * Returns an original price of products.
     *
     * @return float
     */
    public function getOriginalPriceOfProducts(): float
    {
        return $this->costOfProductsWithExtraCharge;
    }

    /**
     * Returns an original cost of products without extra charge.
     *
     * @return float
     */
    public function getOriginalCostOfProductsWithoutExtraCharge(): float
    {
        return $this->originalCostOfProducts;
    }

    /**
     * Checks whether the rule of minimal price was used.
     *
     * @return bool
     */
    public function wasRuleOfMinimalPriceUsed(): bool
    {
        return $this->ruleOfMinimalPriceWasUsed;
    }

    /**
     * Checks whether the rule of minimal cost was used.
     *
     * @return bool
     */
    public function wasRuleOfMinimalCostPerUnitUsed(): bool
    {
        return $this->ruleOfMinimalCostPerUnitWasUsed;
    }

    /**
     * Returns an instance of EstimateGroup.
     *
     * @return EstimateGroup|null
     */
    public function getEstimateGroup(): ?EstimateGroup
    {
        $group = parent::getEstimateGroup();
        $group->add('rule_of_minimal_price', $this->ruleOfMinimalPriceWasUsed, true);
        $group->add('rule_of_minimal_cost', $this->ruleOfMinimalCostPerUnitWasUsed, true);

        return $group;
    }
}
