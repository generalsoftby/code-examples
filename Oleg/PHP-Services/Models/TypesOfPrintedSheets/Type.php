<?php

namespace App\Model\Calculator\TypesOfPrintedSheets;

use Dios\System\Multicasting\Interfaces\EntityWithModel;
use Dios\System\Multicasting\Interfaces\ArrayEntity;

/**
 * The interface for entities of printed sheets.
 */
interface Type extends EntityWithModel, ArrayEntity
{
    /**
     * Returns a name of the sheet.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns a size of the sheet.
     *
     * @return string
     */
    public function getSize(): string;

    /**
     * Returns a height of the sheet.
     *
     * @return int
     */
    public function getHeight(): int;

    /**
     * Returns a width of the sheet.
     *
     * @return int
     */
    public function getWidth(): int;

    /**
     * Returns an area of the sheet.
     *
     * @return int
     */
    public function getArea(): int;

    /**
     * Returns a top margin of the sheet.
     *
     * @return int
     */
    public function getTopMargin(): int;

    /**
     * Returns a bottom margin of the sheet.
     *
     * @return int
     */
    public function getBottomMargin(): int;

    /**
     * Returns a left margin of the sheet.
     *
     * @return int
     */
    public function getLeftMargin(): int;

    /**
     * Returns a right of the sheet.
     *
     * @return int
     */
    public function getRightMargin(): int;

    /**
     * Returns a priority of the sheet.
     *
     * @return int
     */
    public function getPriority(): int;

    /**
     * Checks whether the sheet can be used by the product.
     *
     * @param  int  $height
     * @param  int  $width
     * @return bool
     */
    public function canBeUsed(int $height, int $width): bool;
}
