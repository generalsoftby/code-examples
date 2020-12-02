<?php

namespace App\Model\Calculator\Settings;

use App\Model\Calculator\CalculatorSetting;
use App\Model\Calculator\PricingRulesOfAssemblies\IntervalOfProduct;

/**
 * Implements functions of settings for a calculator.
 * Алгоритмы расчета площади с единицами измерения в квадратах: мм, см, м.
 */
class AlgorithmOfArea implements AlgorithmWithUnit
{
    /**
     * Allowed algorithm types.
     */

    /**
     * An area of a product.
     *
     * @var string
     */
    const AREA_OF_PRODUCT_TYPE = 'area_of_product';

    /**
     * A total area of products of the edition size.
     *
     * @var string
     */
    const TOTAL_AREA_OF_PRODUCTS_TYPE = 'total_area_of_products';

    /**
     * Allowed units.
     */

    /**
     * Square millimeter.
     *
     * @var string
     */
    const SQUARE_MILLIMETER_UNIT = 'square_mm';

    /**
     * Square centimeter.
     *
     * @var string
     */
    const SQUARE_CENTIMETER_UNIT = 'square_cm';

    /**
     * Square meter.
     *
     * @var null
     */
    const SQUARE_METER_UNIT = 'square_m';

    /**
     * A type of algorithm.
     *
     * @var string
     */
    protected $type;

    /**
     * An unit for calculations.
     *
     * @var string
     */
    protected $unit;

    /**
     * Initialises the instance and its data from an instance of the CalculatorSetting.
     *
     * @param CalculatorSetting $instance
     */
    public function __construct(CalculatorSetting $instance)
    {
        $this->fillFromArray($instance->settings ?? []);
    }

    /**
     * Fills new settings from the given array. Defines default values.
     *
     * @param array $array
     */
    public function fillFromArray(array $array): void
    {
        $this->setAlgorithmType($array['type'] ?? self::AREA_OF_PRODUCT_TYPE);
        $this->setUnit($array['unit'] ?? self::SQUARE_MILLIMETER_UNIT);
    }

    /**
     * Sets the given algorithm type.
     *
     * @see See consts of the class with '_TYPE'.
     *
     * @param string $type
     */
    public function setAlgorithmType(string $type): void
    {
        $this->type = $type === self::AREA_OF_PRODUCT_TYPE ? self::AREA_OF_PRODUCT_TYPE : self::TOTAL_AREA_OF_PRODUCTS_TYPE;
    }

    /**
     * Returns the current algorithm type.
     *
     * @return string
     */
    public function getAlgorithmType(): string
    {
        return $this->type;
    }

    /**
     * Sets the given unit.
     *
     * @param string $unit
     *
     * @see See consts of the class that have '_UNIT'.
     */
    public function setUnit(string $unit): void
    {
        $this->unit = in_array($unit, [self::SQUARE_MILLIMETER_UNIT, self::SQUARE_CENTIMETER_UNIT], true)
            ? $unit
            : self::SQUARE_METER_UNIT
        ;
    }

    /**
     * Returns the current unit;
     *
     * @return string
     */
    public function getUnit(): string
    {
        return $this->unit;
    }

    /**
     * Returns a multiplier using the current unit.
     *
     * @return float
     */
    public function getMultiplier(): float
    {
        switch ($this->unit) {
            case self::SQUARE_CENTIMETER_UNIT:
                return 0.01;
            case self::SQUARE_METER_UNIT:
                return 0.000001;
        }

        return 1;
    }

    /**
     * Converts the given value using the current multiplier.
     *
     * @param  int   $value
     * @return float
     */
    public function convertUsingMultiplier(int $value): float
    {
        return $value * $this->getMultiplier();
    }

    /**
     * Converts the given value using the current multiplier and rounds the result.
     *
     * @param  int $value
     * @return int
     */
    public function convertUsingMultiplierAndRound(int $value): int
    {
        return ceil($value * $this->getMultiplier());
    }

    /**
     * Calculates a price by the given price, its unit, a number of products
     * and a number of material.
     *
     * @param  int    $numberOfProducts
     * @param  float  $numberOfMaterial An area of one product or area of all product.
     * @param  float  $costOfProduct
     * @param  string $costUnit
     * @return float|null
     */
    public function calculatePrice(
        int $numberOfProducts,
        float $numberOfMaterial,
        float $costOfProduct,
        string $costUnit
    ): ?float {
        if (
            $costUnit === IntervalOfProduct::PER_ONE_UNIT
            && $this->type === self::TOTAL_AREA_OF_PRODUCTS_TYPE
        ) {
            $costOfProducts = $costOfProduct * $numberOfMaterial;
        } elseif (
            $costUnit === IntervalOfProduct::PER_ONE_UNIT
            && $this->type === self::AREA_OF_PRODUCT_TYPE
        ) {
            $costOfProducts = $costOfProduct * $numberOfMaterial * $numberOfProducts;
        } elseif (
            $costUnit === IntervalOfProduct::PER_ALL_PRODUCTS
            && $this->type === self::TOTAL_AREA_OF_PRODUCTS_TYPE
        ) {
            $costOfProducts = $costOfProduct;
        } elseif (
            $costUnit === IntervalOfProduct::PER_ALL_PRODUCTS
            && $this->type === self::AREA_OF_PRODUCT_TYPE
        ) {
            $costOfProducts = $costOfProduct * $numberOfProducts;
        }

        return $costOfProducts ?? null;
    }

    /**
     * Returns an array of the instance.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'unit' => $this->unit,
        ];
    }
}
