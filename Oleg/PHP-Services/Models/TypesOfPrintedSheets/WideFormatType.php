<?php

namespace App\Model\Calculator\TypesOfPrintedSheets;

use Illuminate\Database\Eloquent\Model;
use App\Model\Calculator\CalculatorPrintedSheet as PrintedSheet;
use App\Model\Calculator\PricingRulesOfAssemblies\PrintedSheets;

/**
 * The class to handle printed sheets using the wide format type.
 */
class WideFormatType implements Type
{
    const UNDEFINED_OPTIMAL_SIZE = 0;

    const LEFT_SIDE_IS_OPTIOMAL = 1;

    const TOP_SIDE_IS_OPTIOMAL = 2;

    const SIZE_IS_ZERO = 3;

    /**
     * A model of PrintedSheet.
     *
     * @var PrintedSheet
     */
    protected $model;

    /**
     * A top margin.
     *
     * @var int
     */
    protected $topMargin;

    /**
     * A bottom margin.
     *
     * @var int
     */
    protected $bottomMargin;

    /**
     * A left margin.
     *
     * @var int
     */
    protected $leftMargin;

    /**
     * A right margin.
     *
     * @var int
     */
    protected $rightMargin;

    /**
     * Initializes an instance of the class using a PrintedSheet.
     *
     * @param Model|PrintedSheet $model
     */
    public function __construct(Model $model)
    {
        if (! ($model instanceof PrintedSheet)) {
            throw new \Exception('An invalid model of the printed sheet.');
        }

        $this->model = $model;
    }

    /**
     * Returns the current Model.
     *
     * @return Model|PrintedSheets
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Fills values from the array.
     *
     * @param  array $array
     * @return void
     */
    public function fillFromArray(array $array)
    {
        $this->topMargin = $array['f3'] ?? 0; // TODO rename to margin
        $this->bottomMargin = $array['f4'] ?? 0; // TODO rename to margin
        $this->leftMargin = $array['f1'] ?? 0; // TODO rename to margin
        $this->rightMargin = $array['f2'] ?? 0; // TODO rename to margin
    }

    /**
     * Returns a name of the sheet.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->model->name;
    }

    /**
     * Returns a size of the sheet.
     *
     * @return string
     */
    public function getSize(): string
    {
        return $this->model->size;
    }

    /**
     * Returns a height of the sheet.
     *
     * @return int
     */
    public function getHeight(): int
    {
        return $this->model->height;
    }

    /**
     * Returns a width of the sheet.
     *
     * @return int
     */
    public function getWidth(): int
    {
        return $this->model->weight; // width
    }

    /**
     * Returns an area of the sheet.
     *
     * @return int
     */
    public function getArea(): int
    {
        return $this->model->height * $this->model->weight; // width
    }

    /**
     * Returns a top margin of the sheet.
     *
     * @return int
     */
    public function getTopMargin(): int
    {
        return $this->topMargin;
    }

    /**
     * Returns a bottom margin of the sheet.
     *
     * @return int
     */
    public function getBottomMargin(): int
    {
        return $this->bottomMargin;
    }

    /**
     * Returns a left margin of the sheet.
     *
     * @return int
     */
    public function getLeftMargin(): int
    {
        return $this->leftMargin;
    }

    /**
     * Returns a right of the sheet.
     *
     * @return int
     */
    public function getRightMargin(): int
    {
        return $this->rightMargin;
    }

    /**
     * Returns a width using margins (the left and the right).
     *
     * @return int
     */
    public function getWidthUsingMargins(): int
    {
        return $this->getWidth() - $this->leftMargin - $this->rightMargin;
    }

    /**
     * Checks whether the sheet can be used by the product.
     *
     * @param  int  $height
     * @param  int  $width
     * @return bool
     */
    public function canBeUsed(int $height, int $width): bool
    {
        /** @var int $code */
        $code = $this->getOptimalSide($height, $width);

        return $code !== self::UNDEFINED_OPTIMAL_SIZE && $code !== self::SIZE_IS_ZERO;
    }

    /**
     * Returns a code of an optimal side for printing by a size of the product.
     *
     * @param  int $height
     * @param  int $width
     * @return int
     */
    public function getOptimalSide(int $height, int $width): int
    {
        if ($height <= 0 || $width <= 0) {
            return self::SIZE_IS_ZERO;
        }

        /** @var float $byWidthUsingMax **/
        $byWidthUsingMax = $this->getWidthUsingMargins() / max($height, $width);

        if ($byWidthUsingMax >= 1) {
            return min($height, $width) === $height
                ? self::TOP_SIDE_IS_OPTIOMAL
                : self::LEFT_SIDE_IS_OPTIOMAL
            ;
        }

        $byWidthUsingMin = $this->getWidthUsingMargins() / min($height, $width);

        if ($byWidthUsingMin >= 1) {
            return max($height, $width) === $height
                ? self::TOP_SIDE_IS_OPTIOMAL
                : self::LEFT_SIDE_IS_OPTIOMAL
            ;
        }

        return self::UNDEFINED_OPTIMAL_SIZE;
    }

    /**
     * Returns an optimal size of a side.
     *
     * @param  int    $height
     * @param  int    $width
     * @return int|null
     */
    public function getOptimalSize(int $height, int $width): ?int
    {
        switch ($this->getOptimalSide($height, $width)) {
            case self::TOP_SIDE_IS_OPTIOMAL:
                return $height;
            case self::LEFT_SIDE_IS_OPTIOMAL:
                return $width;
        }

        return null;
    }

    /**
     * Counts a length of required material.
     *
     * @param  int    $height
     * @param  int    $width
     * @param  int    $numberOfProducts A number of required material.
     * @return int|null
     */
    public function countLengthOfMaterial(int $height, int $width, int $numberOfProducts): ?int
    {
        $optimalSize = $this->getOptimalSize($height, $width);

        return $optimalSize
            ? $optimalSize * $numberOfProducts
            : null
        ;
    }

    /**
     * Returns a priority of the sheet.
     *
     * @return int
     */
    public function getPriority(): int
    {
        return $this->model->priority;
    }

    /**
     * Returns the instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            // TODO rename to margin
            'f3' => $this->topMargin,
            'f4' => $this->bottomMargin,
            'f1' => $this->leftMargin,
            'f2' => $this->rightMargin,
        ];
    }
}
