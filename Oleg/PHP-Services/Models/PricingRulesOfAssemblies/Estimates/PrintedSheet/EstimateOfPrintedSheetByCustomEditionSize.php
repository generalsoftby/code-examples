<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\Estimates\PrintedSheet;

use App\Services\Calculators\EstimateGroup;
use App\Model\Calculator\PricingRulesOfAssemblies\Values\ValuesOfPrintedSheet;
use App\Model\Calculator\PricingRulesOfAssemblies\IntervalOfProduct;
use App\Model\Calculator\PricingRulesOfAssemblies\Cost;

/**
 * Keeps data of calculation for a printed sheet.
 */
class EstimateOfPrintedSheetByCustomEditionSize extends BaseEstimateOfPrintedSheet
{
    /**
     * A cost of products.
     *
     * @var float
     */
    protected $costOfProducts;

    /**
     * A cost of products with extra charge.
     *
     * @var float
     */
    protected $costOfProductsWithExtraCharge;

    /**
     * An appropriate interval of product.
     *
     * @var IntervalOfProduct
     */
    protected $appropriateInterval;

    /**
     * A state of using the rule of minimum.
     *
     * @var bool
     */
    protected $ruleOfMinimumWasUsed;

    /**
     * Initializes an instance of the class with values of calculation.
     *
     * @param string $nameOfPricingRule A name of the pricing rule.
     * @param ValuesOfPrintedSheet $valueKeeper Used values.
     * @param float  $priceOfProducts A final price of products.
     * @param float  $originalCostOfProducts An original cost of products (a cost without an extra charge).
     * @param float  $originalPriceOfProducts An original price of products with an extra charge.
     * @param Cost   $extraCharge An extra charge.
     * @param IntervalOfProduct $appropriateInterval A used interval.
     * @param bool   $ruleOfMinimumWasUsed A state of using of the minimal price.
     */
    public function __construct(
        string $nameOfPricingRule,
        ValuesOfPrintedSheet $valueKeeper,
        float $priceOfProducts,
        float $originalCostOfProducts,
        float $originalPriceOfProducts,
        Cost $extraCharge,
        IntervalOfProduct $appropriateInterval,
        bool $ruleOfMinimumWasUsed
    ) {
        parent::__construct(
            $nameOfPricingRule,
            $valueKeeper,
            $priceOfProducts,
            $originalCostOfProducts,
            $originalPriceOfProducts,
            $extraCharge
        );

        $this->appropriateInterval = $appropriateInterval;
        $this->ruleOfMinimumWasUsed = $ruleOfMinimumWasUsed;
    }

    /**
     * Returns an appropriate interval.
     *
     * @return IntervalOfProduct
     */
    public function getAppropriateInterval(): IntervalOfProduct
    {
        return $this->appropriateInterval;
    }

    /**
     * Checks whether the rule of minimum was used.
     *
     * @return bool
     */
    public function wasRuleOfMinimumUsed(): bool
    {
        return $this->ruleOfMinimumWasUsed;
    }

    /**
     * Returns an instance of EstimateGroup.
     *
     * @return EstimateGroup|null
     */
    public function getEstimateGroup(): ?EstimateGroup
    {
        $group = parent::getEstimateGroup();
        $group->add('rule_of_minimal_price', $this->ruleOfMinimumWasUsed, true);

        return $group;
    }
}
