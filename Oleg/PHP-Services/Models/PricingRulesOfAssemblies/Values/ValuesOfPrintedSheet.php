<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\Values;

use App\Model\Calculator\PricingRulesOfAssemblies\ValueKeeper;
use App\Model\Calculator\TypesOfPrintedSheets\Type;

/**
 * Keeps data of any printed sheet. It used to calculate a price of the service.
 */
interface ValuesOfPrintedSheet extends ValueKeeper
{
    /**
     * Returns a height of the product.
     *
     * @return int
     */
    public function getHeightOfProduct(): int;

    /**
     * Returns a width of the product.
     *
     * @return int
     */
    public function getWidthOfProduct(): int;

    /**
     * Returns a value of number of products.
     *
     * @return int
     */
    public function getNumberOfProducts(): int;

    /**
     * Sets an instance of SheetType.
     *
     * @param  Type $sheet
     * @return void
     */
    public function setSheet(Type $sheet): void;

    /**
     * Returns an instance of a printed sheet.
     *
     * @return Type|null
     */
    public function getSheet(): ?Type;
}
