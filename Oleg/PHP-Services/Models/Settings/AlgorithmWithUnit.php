<?php

namespace App\Model\Calculator\Settings;

/**
 * The interface to implement classes that keeps algorithms and units.
 */
interface AlgorithmWithUnit extends SettingEntity
{
    /**
     * Sets the given algorithm type.
     *
     * @param string $type
     */
    public function setAlgorithmType(string $type): void;

    /**
     * Returns the current algorithm type.
     *
     * @return string
     */
    public function getAlgorithmType(): string;

    /**
     * Sets the given unit.
     *
     * @param string $unit
     */
    public function setUnit(string $unit): void;

    /**
     * Returns the current unit;
     *
     * @return string
     */
    public function getUnit(): string;

    /**
     * Calculates a price by the given price, its unit, a number of products
     * and a number of material.
     *
     * @param  int    $numberOfProducts
     * @param  float  $numberOfMaterial A number of the material of one product
     *                                  or all products.
     * @param  float  $costOfProduct
     * @param  string $costUnit
     * @return float|null
     */
    public function calculatePrice(
        int $numberOfProducts,
        float $numberOfMaterial,
        float $costOfProduct,
        string $costUnit
    ): ?float;
}
