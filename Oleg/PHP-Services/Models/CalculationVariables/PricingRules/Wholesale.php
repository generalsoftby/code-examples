<?php

namespace App\Model\Calculator\CalculationVariables\PricingRules;

use App\Model\Calculator\PricingRulesOfAssemblies\Cost;

/**
 * Keeps settings of wholesale.
 */
class Wholesale implements PricingRule
{
    const DEFAULT_CURRENCY = 'RUB';

    /**
     * A coefficient for changing cost.
     *
     * @var float
     */
    protected $coefficient;

    /**
     * A start cost per a product.
     *
     * @var Cost
     */
    protected $startCostPerProduct;

    /**
     * A minimal cost per product.
     *
     * @var Cost
     */
    protected $minCostPerProduct;

    /**
     * An extra charge.
     *
     * @var Cost
     */
    protected $extraCharge;

    /**
     * A min price for the edition size (products).
     *
     * @var Cost
     */
    protected $minPrice;

    /**
     * Initializes an instance from the given array.
     *
     * @param array|null $array
     */
    public function __construct(array $array = null)
    {
        $this->fillFromArray($array ?? []);
    }

    /**
     * Fills the instance from an array.
     *
     * @param  array $array
     * @return void
     */
    public function fillFromArray(array $array)
    {
        $this->coefficient = (float) ($array['coefficient'] ?? 0);
        $this->startCostPerProduct = new Cost(
            $array['start_cost_per_product']['value'] ?? 0,
            $array['start_cost_per_product']['currency'] ?? self::DEFAULT_CURRENCY
        );
        $this->minCostPerProduct = new Cost(
            $array['min_cost_per_product']['value'] ?? 0,
            $array['min_cost_per_product']['currency'] ?? self::DEFAULT_CURRENCY
        );
        $this->extraCharge = new Cost($array['extra_charge']['value'] ?? 0, $array['extra_charge']['currency'] ?? self::DEFAULT_CURRENCY);
        $this->minPrice = new Cost($array['min_price']['value'] ?? 0, $array['min_price']['currency'] ?? self::DEFAULT_CURRENCY);
    }

    /**
     * Checks whether the settings of the pricing rule are correct.
     *
     * @return bool
     */
    public function isCorrect(): bool
    {
        return isset($this->coefficient);
    }

    /**
     * Sets a coefficient.
     *
     * @param  float $coefficient
     * @return void
     */
    public function setCoefficient(float $coefficient): void
    {
        $this->coefficient = $coefficient;
    }

    /**
     * Returns a coefficient.
     *
     * @return float
     */
    public function getCoefficient(): float
    {
        return $this->coefficient;
    }

    /**
     * Sets a start cost per product.
     *
     * @param  Cost $cost
     * @return void
     */
    public function setStartCostPerProduct(Cost $cost): void
    {
        $this->startCostPerProduct = $cost;
    }

    /**
     * Returns a start cost per product.
     *
     * @return Cost
     */
    public function getStartCostPerProduct(): Cost
    {
        return $this->startCostPerProduct;
    }

    /**
     * Sets an minimal cost per product.
     *
     * @param  Cost $cost
     * @return void
     */
    public function setMinCostPerProduct(Cost $cost): void
    {
        $this->minCostPerProduct = $cost;
    }

    /**
     * Returns an minimal cost per product.
     *
     * @return Cost
     */
    public function getMinCostPerProduct(): Cost
    {
        return $this->minCostPerProduct;
    }

    /**
     * Sets an extra charge of the interval.
     *
     * @param  Cost $cost
     * @return void
     */
    public function setExtraCharge(Cost $cost): void
    {
        $this->extraCharge = $cost;
    }

    /**
     * Returns an extra charge of the interval.
     *
     * @return Cost
     */
    public function getExtraCharge(): Cost
    {
        return $this->extraCharge;
    }

    /**
     * Sets an minimal price of the interval.
     *
     * @param  Cost $cost
     * @return void
     */
    public function setMinPrice(Cost $cost): void
    {
        $this->minPrice = $cost;
    }

    /**
     * Returns an minimal price of the interval.
     *
     * @return Cost
     */
    public function getMinPrice(): Cost
    {
        return $this->minPrice;
    }

    /**
     * Calculates a price by the given edition size.
     *
     * @return int $size
     * @return Cost
     */
    public function calculateByEditionSize(int $size): ?Cost
    {
        $startCost = $this->getStartCostPerProduct()->getValueByCurrency();
        $extraCharge = $this->getExtraCharge()->getValueByCurrency();
        $minPricePerProduct = $this->getMinCostPerProduct()->getValueByCurrency();

        $cost = $this->getCoefficient() * $size * $size + $startCost * $size;
        $price = $cost + $extraCharge;

        if ($price / $size < $minPricePerProduct) {
            $price = $minPricePerProduct * $size + $extraCharge;
        }

        $minPrice = $this->getMinPrice()->getValueByCurrency();

        return $price > $minPrice
            ? new Cost($price, 'RUB')
            : new Cost($minPrice, 'RUB')
        ;
    }

    /**
     * Returns data of the current instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'coefficient' => $this->coefficient,
            'start_cost_per_product' => $this->startCostPerProduct->toArray(),
            'min_cost_per_product' => $this->minCostPerProduct->toArray(),
            'extra_charge' => $this->extraCharge->toArray(),
            'min_price' => $this->minPrice->toArray(),
        ];
    }
}
