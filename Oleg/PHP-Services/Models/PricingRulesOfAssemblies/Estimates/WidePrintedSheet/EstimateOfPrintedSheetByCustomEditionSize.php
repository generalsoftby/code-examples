<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\Estimates\WidePrintedSheet;

use App\Services\Calculators\EstimateGroup;
use App\Model\Calculator\PricingRulesOfAssemblies\ValueKeeper;
use App\Model\Calculator\PricingRulesOfAssemblies\IntervalOfProduct;
use App\Model\Calculator\PricingRulesOfAssemblies\Cost;
use App\Model\Calculator\PricingRulesOfAssemblies\Values\ValuesOfWidePrintedSheets;

/**
 * Contains data of calculation for a printed sheet.
 */
class EstimateOfPrintedSheetByCustomEditionSize extends BaseEstimateOfWidePrintedSheet
{
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
     * An original extra charge.
     *
     * @var Cost
     */
    protected $extraCharge;

    /**
     * A state of using the rule of minimal price.
     *
     * @var bool
     */
    protected $ruleOfMinimalPriceWasUsed;

    /**
     * Initializes an instance of the class with values of calculation.
     *
     * @param string $nameOfPricingRule
     * @param ValuesOfWidePrintedSheets $valueKeeper
     * @param float  $priceOfProducts
     * @param float  $costOfProducts
     * @param float  $costOfProductsWithExtraCharge An original price of products.
     * @param IntervalOfProduct $appropriateInterval A used interval.
     * @param bool   $ruleOfMinimalPriceWasUsed A state of using the rule of minimal price.
     */
    public function __construct(
        string $nameOfPricingRule,
        ValueKeeper $valueKeeper,
        float $priceOfProducts,
        float $costOfProducts,
        Cost $extraCharge,
        float $costOfProductsWithExtraCharge,
        IntervalOfProduct $appropriateInterval,
        bool $ruleOfMinimalPriceWasUsed
    ) {
        parent::__construct(
            $nameOfPricingRule,
            $valueKeeper,
            $priceOfProducts,
            $costOfProducts,
            $extraCharge
        );

        $this->appropriateInterval = $appropriateInterval;
        $this->costOfProductsWithExtraCharge = $costOfProductsWithExtraCharge;
        $this->ruleOfMinimalPriceWasUsed = $ruleOfMinimalPriceWasUsed;
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
     * Checks whether the rule of minimal price was used.
     *
     * @return bool
     */
    public function wasRuleOfMinimalPriceUsed(): bool
    {
        return $this->ruleOfMinimalPriceWasUsed;
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

        return $group;
    }
}
