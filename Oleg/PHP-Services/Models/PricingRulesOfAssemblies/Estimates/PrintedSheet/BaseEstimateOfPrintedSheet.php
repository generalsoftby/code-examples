<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\Estimates\PrintedSheet;

use App\Model\Calculator\PricingRulesOfAssemblies\Cost;
use App\Model\Calculator\PricingRulesOfAssemblies\Estimates\InitializationOfEstimateSubgroupOfSheet;
use App\Model\Calculator\PricingRulesOfAssemblies\Values\ValuesOfSheet;
use App\Services\Calculators\EstimateGroup;

/**
 * Implements base functions of the estimate of a printed sheet.
 */
class BaseEstimateOfPrintedSheet implements EstimateOfPrintedSheet
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
     * @var ValuesOfSheet
     */
    protected $valueKeeper;

    /**
     * A final price of products.
     *
     * @var float
     */
    protected $priceOfProducts;

    /**
     * An original cost of products. A start cost without an extra charge.
     *
     * @var float
     */
    protected $originalCostOfProducts;

    /**
     * An original price of products with an extra charge.
     *
     * @var float
     */
    protected $originalPriceOfProducts;

    /**
     * An original extra charge.
     *
     * @var Cost
     */
    protected $extraCharge;

    /**
     * Initializes an instance of the class with values of the calculation.
     *
     * @param string $nameOfPricingRule A name of the pricing rule.
     * @param ValuesOfSheet $valueKeeper Used values.
     * @param float $priceOfProducts A final price of products.
     * @param float $originalCostOfProducts An original cost of products (a start cost without an extra charge).
     * @param float $originalPriceOfProducts An original price of products with an extra charge.
     * @param Cost $extraCharge An extra charge.
     */
    public function __construct(
        string $nameOfPricingRule,
        ValuesOfSheet $valueKeeper,
        float $priceOfProducts,
        float $originalCostOfProducts,
        float $originalPriceOfProducts,
        Cost $extraCharge
    )
    {
        $this->nameOfPricingRule = $nameOfPricingRule;
        // Clones the keeper to save change of values.
        $this->valueKeeper = clone $valueKeeper;
        $this->priceOfProducts = $priceOfProducts;
        $this->originalCostOfProducts = $originalCostOfProducts;
        $this->originalPriceOfProducts = $originalPriceOfProducts;
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
     * Returns a number of printed sheets.
     *
     * @return float
     */
    public function getNumberOfPrintedSheets(): int
    {
        return $this->valueKeeper->getNumberOfPrintedSheets();
    }

    /**
     * Returns a number of material with wastes.
     *
     * @return float
     */
    public function getNumberOfMaterialWithWastes(): float
    {
        return $this->valueKeeper->getNumberOfPrintedSheetsWithSheetsForFitting();
    }

    /**
     * Returns a number of printed sheets.
     *
     * @return float
     */
    public function getNumberOfMaterial(): float
    {
        return $this->getNumberOfPrintedSheets();
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
     * Returns a height of the product.
     *
     * @return int
     */
    public function getHeightOfProduct(): int
    {
        return $this->valueKeeper->getHeightOfProduct();
    }

    /**
     * Returns a width of the product.
     *
     * @return int
     */
    public function getWidthOfProduct(): int
    {
        return $this->valueKeeper->getWidthOfProduct();
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
     * Returns an original cost of products without extra charge.
     *
     * @return float
     */
    public function getOriginalCostOfProducts(): float
    {
        return $this->originalCostOfProducts;
    }

    /**
     * Returns an original price of products.
     *
     * @return float
     */
    public function getOriginalPriceOfProducts(): float
    {
        return $this->originalPriceOfProducts;
    }

    /**
     * Returns an instance of EstimateGroup.
     *
     * @return EstimateGroup|null
     */
    public function getEstimateGroup(): ?EstimateGroup
    {
        $group = new EstimateGroup('printed_sheet');
        $group->add('name_of_pricing_rule', $this->nameOfPricingRule);
        $group->add('printed_sheet', $this->newEstimateSubgroupOfSheet($this->valueKeeper->getSheet()));
        $group->add('number_of_products', $this->valueKeeper->getNumberOfProducts());
        $group->add('number_of_printed_sheets', $this->valueKeeper->getNumberOfPrintedSheets());
        $group->add(
            'number_of_printed_sheets_with_sheets_for_fitting',
            $this->valueKeeper->getNumberOfPrintedSheetsWithSheetsForFitting(),
            true
        );
        $group->add('number_of_products_on_printed_sheet', $this->valueKeeper->getNumberOfProductsOnSheet());
        $group->add('product_height', $this->getHeightOfProduct());
        $group->add('product_width', $this->getWidthOfProduct());
        $group->add('price', $this->priceOfProducts);
        $group->add('price_per_product', $this->getPricePerProduct());
        $group->add('price_without_extra_charge', $this->getPriceOfProductsWithoutExtraCharge(), true);
        $group->add('price_per_product_without_extra_charge', $this->getPricePerProductWithoutExtraCharge(), true);
        $group->add('extra_charge', $this->getValueOfOriginalExtraCharge());
        $group->add('original_price', $this->getOriginalPriceOfProducts(), true);
        $group->add('original_cost', $this->getOriginalCostOfProducts(), true);
        $group->add('number_of_material_with_wastes', $this->getNumberOfMaterialWithWastes(), true);

        return $group;
    }
}
