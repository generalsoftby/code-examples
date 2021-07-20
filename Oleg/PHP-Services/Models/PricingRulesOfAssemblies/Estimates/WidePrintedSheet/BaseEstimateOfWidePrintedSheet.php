<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\Estimates\WidePrintedSheet;

use App\Services\Calculators\EstimateGroup;
use App\Model\Calculator\PricingRulesOfAssemblies\ValueKeeper;
use App\Model\Calculator\PricingRulesOfAssemblies\Cost;
use App\Model\Calculator\PricingRulesOfAssemblies\Estimates\EstimateOfPrintedSheet;
use App\Model\Calculator\PricingRulesOfAssemblies\Estimates\InitializationOfEstimateSubgroupOfSheet;
use App\Model\Calculator\PricingRulesOfAssemblies\Values\ValuesOfWidePrintedSheets;

/**
 * Keeps data of calculation for a wide printed sheet.
 */
class BaseEstimateOfWidePrintedSheet implements EstimateOfPrintedSheet
{
    use InitializationOfEstimateSubgroupOfSheet;

    /**
     * A name of pricing rule.
     *
     * @var string
     */
    protected $nameOfPricingRule;

    /**
     * A ValueKeeper.
     *
     * @var ValuesOfWidePrintedSheets
     */
    protected $valueKeeper;

    /**
     * A price of products.
     *
     * @var float
     */
    protected $priceOfProducts;

    /**
     * A cost of products.
     *
     * @var float
     */
    protected $costOfProducts;

    /**
     * An original extra charge.
     *
     * @var Cost
     */
    protected $extraCharge;

    /**
     * Initializes an instance of the class with values of the calculation.
     *
     * @param string $nameOfPricingRule A name of the used pricing rule.
     * @param ValuesOfWidePrintedSheets $valueKeeper Values of the calculation.
     * @param float  $priceOfProducts A final price of products.
     * @param float  $costOfProducts A cost of products.
     * @param Cost   $extraCharge An extra charge.
     */
    public function __construct(
        string $nameOfPricingRule,
        ValueKeeper $valueKeeper,
        float $priceOfProducts,
        float $costOfProducts,
        Cost $extraCharge
    ) {
        // Clones an keeper that contains a SheetWithRules.
        // Different SheetWithRules are assigned in a loop.
        $this->valueKeeper = clone $valueKeeper;

        $this->nameOfPricingRule = $nameOfPricingRule;
        $this->priceOfProducts = $priceOfProducts;
        $this->costOfProducts = $costOfProducts;
        $this->extraCharge = $extraCharge;
    }

    /**
     * Returns a name of pricing rule.
     *
     * @return string
     */
    public function getNameOfPricingRule(): string
    {
        return $this->nameOfPricingRule;
    }

    /**
     * Returns a price per all products.
     *
     * @return float
     */
    public function getPriceOfProducts(): float
    {
        return $this->priceOfProducts;
    }

    /**
     * Returns a price of products without an original extra charge.
     *
     * @return float
     */
    public function getPriceOfProductsWithoutExtraCharge(): float
    {
        return $this->priceOfProducts - $this->extraCharge->getValueByCurrency();
    }

    /**
     * Returns a price per one product.
     *
     * @return float
     */
    public function getPricePerProduct(): float
    {
        return $this->priceOfProducts / $this->valueKeeper->getNumberOfProducts();
    }

    /**
     * Returns a price per product without extra charge.
     *
     * @return float
     */
    public function getPricePerProductWithoutExtraCharge(): float
    {
        return $this->getPriceOfProductsWithoutExtraCharge() / $this->valueKeeper->getNumberOfProducts();
    }

    /**
     * Returns an actual number of material.
     *
     * @return float
     */
    public function getNumberOfMaterial(): float
    {
        return $this->valueKeeper->getNumberOfMaterialByUnit();
    }

    /**
     * Returns a number of material with wastes.
     *
     * @return float
     */
    public function getNumberOfMaterialWithWastes(): float
    {
        return $this->valueKeeper->getNumberOfMaterialByUnit();
    }

    /**
     * Returns a priority of the printed sheet.
     *
     * @return int
     */
    public function getPriorityOfPrintedSheet(): int
    {
        return $this->valueKeeper->getSheet()->getPriority();
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
        return $this->costOfProducts;
    }

    /**
     * Returns a value of the original extra charge.
     *
     * @return float
     */
    public function getValueOfOriginalExtraCharge(): float
    {
        return $this->extraCharge->getValueByCurrency();
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
        $group = new EstimateGroup('assembly');
        $group->add('printed_sheet', $this->newEstimateSubgroupOfSheet($this->valueKeeper->getSheet()));
        $group->add('number_of_products', $this->valueKeeper->getNumberOfProducts());
        $group->add('algorithm_type', trans('individual_settings.' . $this->valueKeeper->getAlgorithmType()), true);
        $group->add('number_of_material', $this->getNumberOfMaterial());
        $group->add('raw_number_of_material', $this->valueKeeper->getRawNumberOfMaterialByUnit(), true);
        $group->add('material_unit', trans('units.' . $this->valueKeeper->getUnitOfMaterial()));
        $group->add('name_of_pricing_rule', $this->nameOfPricingRule);
        $group->add('price', $this->priceOfProducts);
        $group->add('price_per_product', $this->getPricePerProduct());
        $group->add('price_without_extra_charge', $this->getPriceOfProductsWithoutExtraCharge(), true);
        $group->add('price_per_product_without_extra_charge', $this->getPricePerProductWithoutExtraCharge(), true);
        $group->add('extra_charge', $this->getValueOfOriginalExtraCharge(), true);
        $group->add('original_price', $this->getOriginalPriceOfProducts(), true);
        $group->add('original_cost', $this->getOriginalCostOfProductsWithoutExtraCharge(), true);

        return $group;
    }
}
