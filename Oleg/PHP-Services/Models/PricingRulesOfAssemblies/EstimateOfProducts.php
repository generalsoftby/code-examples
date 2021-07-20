<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies;

use App\Services\Calculators\EstimateGroup;

/**
 * The interface to implement a keeper of data of calculation of products.
 */
interface EstimateOfProducts
{
    /**
     * Returns a name of pricing rule.
     *
     * @return string
     */
    public function getNameOfPricingRule(): string;

    /**
     * Returns a price per all products.
     *
     * @return float
     */
    public function getPriceOfProducts(): float;

    /**
     * Returns a price per one product.
     *
     * @return float
     */
    public function getPricePerProduct(): float;

    /**
     * Returns a number of material for manufacturing.
     *
     * @return float
     */
    public function getNumberOfMaterial(): float;

    /**
     * Returns an instance of EstimateGroup.
     *
     * @return EstimateGroup|null
     */
    public function getEstimateGroup(): ?EstimateGroup;
}
