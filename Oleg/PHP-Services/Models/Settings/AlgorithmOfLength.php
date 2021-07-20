<?php

namespace App\Model\Calculator\Settings;

use App\Model\Calculator\CalculatorSetting;
use App\Model\Calculator\PricingRulesOfAssemblies\IntervalOfProduct;

/**
 * Implements functions of settings for a calculator.
 * Алгоритмы расчета длины с единицами измерения: мм, см, м.
 */
class AlgorithmOfLength implements AlgorithmWithUnit
{
    /**
     * Allowed algorithm types.
     */

    /**
     * Sum of optimal lengths of products.
     * The length of a material to make products.
     *
     * @var string
     */
    const LENGTH_OF_MATERIAL_TYPE = 'length_of_material';

    /**
     * An optimal length of a product.
     *
     * @var string
     */
    const LENGTH_OF_PRODUCT_TYPE = 'length_of_product';

    /**
     * Allowed units.
     */

    /**
     * Millimeter.
     *
     * @var string
     */
    const MILLIMETER_UNIT = 'mm';

    /**
     * Centimeter.
     *
     * @var string
     */
    const CENTIMETER_UNIT = 'cm';

    /**
     * Meter.
     *
     * @var null
     */
    const METER_UNIT = 'm';

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
        $this->setAlgorithmType($array['type'] ?? self::LENGTH_OF_MATERIAL_TYPE);
        $this->setUnit($array['unit'] ?? self::MILLIMETER_UNIT);
    }

    /**
     * Sets algorithm type.
     *
     * @see See consts of the class with '_TYPE'.
     *
     * @param string $type
     */
    public function setAlgorithmType(string $type): void
    {
        $this->type = $type === self::LENGTH_OF_MATERIAL_TYPE ? self::LENGTH_OF_MATERIAL_TYPE : self::LENGTH_OF_PRODUCT_TYPE;
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
     * Sets a new unit.
     *
     * @param string $unit
     *
     * @see See consts of the class that have '_UNIT'.
     */
    public function setUnit(string $unit): void
    {
        $this->unit = in_array($unit, [self::MILLIMETER_UNIT, self::CENTIMETER_UNIT], true)
            ? $unit
            : self::METER_UNIT
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
            case self::CENTIMETER_UNIT:
                return 0.1;
            case self::METER_UNIT:
                return 0.001;
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
     * Calculates a price by the given price, its unit, a number of products
     * and a length of material.
     *
     * @param  int    $numberOfProducts
     * @param  float  $numberOfMaterial A length of an optimal side of one
     *                                  product or a total length of an optimal
     *                                  side of all products.
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
            && $this->type === self::LENGTH_OF_MATERIAL_TYPE
        ) {
            // Is used the length is a length of material
            $costOfProducts = $costOfProduct * $numberOfMaterial;
        } elseif (
            $costUnit === IntervalOfProduct::PER_ONE_UNIT
            && $this->type === self::LENGTH_OF_PRODUCT_TYPE
        ) {
            // Is used the length is a length of product
            $costOfProducts = $costOfProduct * $numberOfMaterial * $numberOfProducts;
        } elseif (
            $costUnit === IntervalOfProduct::PER_ALL_PRODUCTS
            && $this->type === self::LENGTH_OF_MATERIAL_TYPE
        ) {
            $costOfProducts = $costOfProduct;
        } elseif (
            $costUnit === IntervalOfProduct::PER_ALL_PRODUCTS
            && $this->type === self::LENGTH_OF_PRODUCT_TYPE
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
