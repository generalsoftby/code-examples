<?php

namespace App\Model\Calculator\CalculationVariables;

/**
 * Tools a product size.
 *
 * @property int|null $heigthOfProduct A height of the product.
 * @property int|null $widthOfProduct A width of the product.
 */
trait ToolProductSize
{
    /**
     * Sets a size of a product.
     *
     * @param int $width
     * @param int $heigth
     */
    public function setProductSize(int $heigth, int $width): void
    {
        $this->heigthOfProduct = $heigth;
        $this->widthOfProduct = $width;
        $this->defineInterval();
    }

    /**
     * Returns the top length of the product.
     *
     * @return int|null
     */
    public function getTopLengthOfProduct(): ?int
    {
        return $this->widthOfProduct;
    }

    /**
     * Returns the bottom length of the product.
     *
     * @return int|null
     */
    public function getBottomLengthOfProduct(): ?int
    {
        return $this->widthOfProduct;
    }

    /**
     * Returns the left length of the product.
     *
     * @return int|null
     */
    public function getLeftLengthOfProduct(): ?int
    {
        return $this->heigthOfProduct;
    }

    /**
     * Returns the right length of the product.
     *
     * @return int|null
     */
    public function getRightLengthOfProduct(): ?int
    {
        return $this->heigthOfProduct;
    }
}
