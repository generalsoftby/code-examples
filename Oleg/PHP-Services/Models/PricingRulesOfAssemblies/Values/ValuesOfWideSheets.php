<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\Values;

use App\Model\Calculator\Settings\AlgorithmWithUnit;
use App\Model\Calculator\TypesOfPrintedSheets\Type;
use App\Model\Calculator\TypesOfPrintedSheets\WideFormatType;

/**
 * Keeps data of a wide printed sheet. It used to calculate prices of a service.
 */
abstract class ValuesOfWideSheets implements ValuesOfPrintedSheet
{
    /**
     * A WideFormatType.
     *
     * @var WideFormatType|null
     */
    protected $sheet;

    /**
     * Sets an instance of sheet type.
     *
     * @param WideFormatType $sheet
     */
    public function setSheet(Type $sheet): void
    {
        if ($sheet instanceof WideFormatType) {
            $this->sheet = $sheet;
        }
    }

    /**
     * Returns an instance of a printed sheet.
     *
     * @return WideFormatType|null
     */
    public function getSheet(): ?Type
    {
        return $this->sheet;
    }

    /**
     * Returns an instance of AlgorithmWithUnit.
     *
     * @return AlgorithmWithUnit
     */
    abstract public function getAlgorithmWithUnit(): AlgorithmWithUnit;

    /**
     * Returns an algorithm type.
     *
     * @return string
     */
    abstract public function getAlgorithmType(): string;

    /**
     * Returns a unit of the material.
     *
     * @return string
     */
    abstract public function getUnitOfMaterial(): string;

    /**
     * Returns a raw number of the material by a unit of the calculator.
     *
     * @return float|null
     */
    abstract public function getRawNumberOfMaterialByUnit(): ?float;

    /**
     * Returns a rounded number of the material by a unit of the calculator.
     *
     * @return float|null
     */
    abstract public function getNumberOfMaterialByUnit(): ?float;
}
