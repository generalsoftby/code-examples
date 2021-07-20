<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\Estimates\PrintedSheet;

use App\Model\Calculator\PricingRulesOfAssemblies\Cost;
use App\Model\Calculator\PricingRulesOfAssemblies\Values\ValuesOfPrintedSheet;
use App\Services\Calculators\EstimateGroup;

/**
 * Keeps data of calculation for a printed sheet.
 */
class EstimateOfPrintedSheetByWholesale extends BaseEstimateOfPrintedSheet
{
    /**
     * A final cost of products before the rule of a minimal cost.
     *
     * @var float
     */
    protected $finalCostOfProducts;

    /**
     * A state of using the rule of minimal price.
     *
     * @var bool
     */
    protected $ruleOfMinimalPriceWasUsed;

    /**
     * A state of using the rule of minimal cost per a printed sheet.
     *
     * @var bool
     */
    protected $ruleOfMinimalCostPerPrintedSheetWasUsed;

    /**
     * Initializes an instance of the class with values of calculation.
     *
     * @param string $nameOfPricingRule A name of the pricing rule.
     * @param ValuesOfPrintedSheet $valueKeeper Used values.
     * @param float  $priceOfProducts A final price of products.
     * @param float  $originalCostOfProducts An original cost of products (a start cost without an extra charge).
     * @param float  $originalPriceOfProducts An original price of products with an extra charge.
     * @param float  $finalCostOfProducts A final cost of products before the rule of a minimal cost.
     * @param Cost   $extraCharge An extra charge.
     * @param bool   $ruleOfMinimalPriceWasUsed A state of using of the minimal price.
     * @param bool   $ruleOfMinimalCostPerPrintedSheetWasUsed  A state of using of the minimal cost per a printed sheet.
     */
    public function __construct(
        string $nameOfPricingRule,
        ValuesOfPrintedSheet $valueKeeper,
        float $priceOfProducts,
        float $originalCostOfProducts,
        float $originalPriceOfProducts,
        float $finalCostOfProducts,
        Cost $extraCharge,
        bool $ruleOfMinimalPriceWasUsed,
        bool $ruleOfMinimalCostPerPrintedSheetWasUsed
    ) {
        parent::__construct(
            $nameOfPricingRule,
            $valueKeeper,
            $priceOfProducts,
            $originalCostOfProducts,
            $originalPriceOfProducts,
            $extraCharge
        );

        $this->finalCostOfProducts = $finalCostOfProducts;
        $this->ruleOfMinimalPriceWasUsed = $ruleOfMinimalPriceWasUsed;
        $this->ruleOfMinimalCostPerPrintedSheetWasUsed = $ruleOfMinimalCostPerPrintedSheetWasUsed;
    }

    /**
     * Returns a final cost of products.
     *
     * @return float
     */
    public function getFinalCostOfProducts(): float
    {
        return $this->finalCostOfProducts;
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
     * Checks whether the rule of minimal cost of a printed sheet was used.
     *
     * @return bool
     */
    public function wasRuleOfMinimalCostPerPrintedSheetUsed(): bool
    {
        return $this->ruleOfMinimalCostPerPrintedSheetWasUsed;
    }

    /**
     * Returns an instance of EstimateGroup.
     *
     * @return EstimateGroup|null
     */
    public function getEstimateGroup(): ?EstimateGroup
    {
        $group = parent::getEstimateGroup();
        $group->add('final_cost', $this->getFinalCostOfProducts(), true);
        $group->add('rule_of_minimal_price', $this->ruleOfMinimalPriceWasUsed, true);
        $group->add('rule_of_minimal_cost', $this->ruleOfMinimalCostPerPrintedSheetWasUsed, true);

        return $group;
    }
}
