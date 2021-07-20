<?php

namespace App\Model\Calculator\CalculationVariables;

/**
 * Contains a size of a sheet.
 */
class SheetSize
{
    /**
     * A height of the sheet.
     *
     * @var int
     */
    protected $height;

    /**
     * A width of the sheet.
     *
     * @var int
     */
    protected $width;

    /**
     * Initializes an instance of the class with a sheet size.
     *
     * @param int $height
     * @param int $width
     */
    public function __construct(int $height = 0, int $width = 0)
    {
        $this->height = $height;
        $this->width = $width;
    }

    /**
     * Sets a size of the sheet.
     *
     * @param int $height
     * @param int $width
     */
    public function setSheetSize(int $height, int $width)
    {
        $this->height = $height;
        $this->width = $width;
    }

    /**
     * Sets a height of the sheet.
     *
     * @param int $height
     */
    public function setHeight(int $height)
    {
        $this->height = $height;
    }

    /**
     * Returns a height of the sheet.
     *
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * Sets a width of the sheet.
     *
     * @param int $width
     */
    public function setWidth(int $width)
    {
        $this->width = $width;
    }

    /**
     * Returns a width of the sheet.
     *
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * Returns an area of the sheet.
     *
     * @return int
     */
    public function getArea(): int
    {
        return $this->height * $this->width;
    }
}
