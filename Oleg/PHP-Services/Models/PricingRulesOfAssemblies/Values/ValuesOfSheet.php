<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\Values;

use App\Model\Calculator\TypesOfPrintedSheets\SheetType;
use App\Model\Calculator\TypesOfPrintedSheets\Type;

/**
 * Keeps data of a printed sheet with the 'sheet' type. It used to calculate
 * a price of products.
 */
abstract class ValuesOfSheet implements ValuesOfPrintedSheet
{
    /**
     * A WideFormatType.
     *
     * @var WideFormatType|null
     */
    protected $sheet;

    /**
     * Sets an instance of SheetType.
     *
     * @param  SheetType $sheet
     * @return void
     */
    public function setSheet(Type $sheet): void
    {
        if ($sheet instanceof SheetType) {
            $this->sheet = $sheet;
        }
    }

    /**
     * Returns an instance of a printed sheet.
     *
     * @return SheetType|null
     */
    public function getSheet(): ?Type
    {
        return $this->sheet;
    }

    /**
     * Returns a number of printed sheets.
     *
     * @return int|null
     */
    abstract public function getNumberOfPrintedSheets(): ?int;

    /**
     * Returns a number of printed sheets with a number of sheets for fitting.
     *
     * @return int|null
     */
    abstract public function getNumberOfPrintedSheetsWithSheetsForFitting(): ?int;

    /**
     * Returns a number of products on the sheet.
     *
     * @return int
     */
    abstract public function getNumberOfProductsOnSheet(): int;

    /**
     * Sets a number of printed sheets for fitting.
     *
     * @param  int $value
     * @return void
     */
    abstract public function setNumberOfSheetsForFitting(int $value): void;

    /**
     * Returns a number of sheets for fitting.
     *
     * @return int|null
     */
    abstract public function getNumberOfSheetsForFitting(): ?int;
}
