<?php

namespace App\Model\Calculator\TypesOfPrintedSheets;

use Illuminate\Database\Eloquent\Model;
use App\Model\Calculator\CalculatorPrintedSheet as PrintedSheet;

/**
 * The class to handle printed sheets using the sheet type.
 */
class SheetType implements Type
{
    public const FIRST_WAY = 'first';

    public const SECOND_WAY = 'second';

    /**
     * A model of PrintedSheet.
     *
     * @var PrintedSheet
     */
    protected $model;

    /**
     * An attribute name with values.
     *
     * @var string
     */
    protected $attribute;

    /**
     * A top margin.
     *
     * Техническое поле сверху (x1).
     *
     * @var int
     */
    protected $topMargin;

    /**
     * A bottom margin.
     *
     * Техническое поле снизу (x2).
     *
     * @var int
     */
    protected $bottomMargin;

    /**
     * A left margin.
     *
     * Техническое поле слева (y1).
     *
     * @var int
     */
    protected $leftMargin;

    /**
     * A right margin.
     *
     * Техническое поле справа (y2).
     *
     * @var int
     */
    protected $rightMargin;

    /**
     * A horizontal distance between products.
     *
     * Расстояние между изделиями по горизонтали (sw) (по ширине ПЛ).
     *
     * @var int
     */
    protected $horizontalDistanceBetweenProducts;

    /**
     * A vertical distance between products.
     *
     * Расстояние между изделиями по вертикали (sh) (по высоте листа).
     *
     * @var int
     */
    protected $verticalDistanceBetweenProducts;

    /**
     * A horizontal inside indent of the print area.
     *
     * Отступ от края печ области горизонтальный (по ширине ПЛ).
     *
     * @var int
     */
    protected $horizontalInsideIndent;

    /**
     * A vertical inside indent of the print area.
     *
     * Отступ от края печ области вертикальный (по высоте ПЛ).
     *
     * @var int
     */
    protected $verticalInsideIndent;

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
     * @return Model|PrintedSheet
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
        // TODO Переменные массива переименовать в человеческие названия
        $this->topMargin = $array['f3'] ?? 0;
        $this->bottomMargin = $array['f4'] ?? 0;
        $this->leftMargin = $array['f1'] ?? 0;
        $this->rightMargin = $array['f2'] ?? 0;
        $this->horizontalDistanceBetweenProducts = $array['v1'] ?? 0;
        $this->verticalDistanceBetweenProducts = $array['v2'] ?? 0;
        $this->horizontalInsideIndent = $array['inth'] ?? 0;
        $this->verticalInsideIndent = $array['intw'] ?? 0;
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
     * Высота печатного листа (H).
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
     * Ширина печатного листа (W).
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
     * Ширина печатной области (W1).
     *
     * @return int
     */
    public function getWidthUsingMargins(): int
    {
        return $this->getWidth() - $this->leftMargin - $this->rightMargin;
    }

    /**
     * Returns a height using margins (the top and the bottom).
     *
     * Высота печатной области (H1).
     *
     * @return int
     */
    public function getHeightUsingMargins(): int
    {
        return $this->getHeight() - $this->topMargin - $this->bottomMargin;
    }

    /**
     * Checks whether the sheet can be used by the product.
     *
     * @param  int $height A height of the product. Высота изделия (b).
     * @param  int $width A width of the product. Ширина изделия (a).
     * @return bool
     */
    public function canBeUsed(int $height, int $width): bool
    {
        return $this->countNumberOfProductsOnSheet($height, $width) > 0;
    }

    /**
     * Counts an optimal number of products on the sheet.
     *
     * @param  int $height A height of the product. Высота изделия (b).
     * @param  int $width A width of the product. Ширина изделия (a).
     * @return int
     */
    public function countNumberOfProductsOnSheet(int $height, int $width): int
    {
        return max(
            $this->countNumberOfProductsOnSheetByFirstWay($height, $width),
            $this->countNumberOfProductsOnSheetBySecondWay($height, $width)
        );
    }

    /**
     * Counts a number of products on the sheet by the first way.
     *
     * Количество изделий на листе N1.
     * Раскладка 1 (W1/a) * (H1/b).
     *
     * @param  int $height A height of the product. Высота изделия (b).
     * @param  int $width A width of the product. Ширина изделия (a).
     * @return int
     */
    public function countNumberOfProductsOnSheetByFirstWay(int $height, int $width): int
    {
        $numberOfProductsByHeight = $this->possibleNumberOfProductsByHeightOfPrintedSheetByFirstWay($height);
        $numberOfProductsByWidth = $this->possibleNumberOfProductsByWidthOfPrintedSheetByFirstWay($width);

        return $numberOfProductsByHeight * $numberOfProductsByWidth;
    }

    /**
     * Calculates a number of products by width without a distance between products by the first way.
     *
     * Кол-во изделий по ширине без учета расстояний между изделиями.
     *
     * @param  int $width A width of the product. Ширина изделия (a).
     * @return int
     */
    public function numberOfProductsByWidthWithoutDistanceBetweenProductsByFirstWay(int $width): int
    {
        return floor(($this->getWidthUsingMargins() - $this->horizontalInsideIndent * 2) / $width);
    }

    /**
     * Calculates a number of products by width with the distance between products by the first way.
     *
     * Кол-во изделий по ширине с учетом расстояний между изделиями (W1/(a+sw)).
     *
     * @param  int $width A width of the product. Ширина изделия (a).
     * @return int
     */
    public function numberOfProductsByWidthWithDistanceBetweenProductsByFirstWay(int $width): int
    {
        return floor($this->getWidthUsingMargins() / ($width + $this->horizontalDistanceBetweenProducts));
    }

    /**
     * Calculates a number of products by height without a distance between products by the first way.
     *
     * Кол-во изделий по высоте без учета расстояний между изделиями.
     *
     * @param  int $height A height of the product. Высота изделия (b).
     * @return int
     */
    public function numberOfProductsByHeightWithoutDistanceBetweenProductsByFirstWay(int $height): int
    {
        return floor(($this->getHeightUsingMargins() - $this->verticalInsideIndent * 2) / $height);
    }

    /**
     * Calculates a number of products by height with the distance between products by the first way.
     *
     * Кол-во изделий по высоте с учетом расстояний между изделиями (H1/(b+sh))
     *
     * @param  int $height A height of the product. Высота изделия (b).
     * @return int
     */
    public function numberOfProductsByHeightWithDistanceBetweenProductsByFirstWay(int $height): int
    {
        return floor($this->getHeightUsingMargins() / ($height + $this->verticalDistanceBetweenProducts));
    }

    /**
     * Calculates a needed number of minimal horizontal indents by the first way.
     *
     * Необходимое кол-во минимальных отступов по горизонтали.
     *
     * @param  int $width A width of the product. Ширина изделия (a).
     * @return int
     */
    public function neededNumberOfMinimalHorizontalIndentsByFirstWay(int $width): int
    {
        return $this->numberOfProductsByWidthWithDistanceBetweenProductsByFirstWay($width) - 1;
    }

    /**
     * Calculates a needed number of minimal vertical indents by the first way.
     *
     * Необходимое кол-во минимальных отступов по вертикали.
     *
     * @param  int $height A height of the product. Высота изделия (b).
     * @return int
     */
    public function neededNumberOfMinimalVerticalIndentsByFirstWay(int $height): int
    {
        return $this->numberOfProductsByHeightWithDistanceBetweenProductsByFirstWay($height) - 1;
    }

    /**
     * Calculates a possible number of products by width of the printed sheet by the first way.
     *
     * Возможное количество изделий на листе по ширине (W1/a).
     *
     * @param  int $width A width of the product. Ширина изделия (a).
     * @return int
     */
    public function possibleNumberOfProductsByWidthOfPrintedSheetByFirstWay(int $width): int
    {
        $productsByWidthWithoutDistance = $this
            ->numberOfProductsByWidthWithoutDistanceBetweenProductsByFirstWay($width);
        $productsByWidthWithDistance = $this->numberOfProductsByWidthWithDistanceBetweenProductsByFirstWay($width);
        $neededIndents = $this->neededNumberOfMinimalHorizontalIndentsByFirstWay($width);
        $hDistance = $this->horizontalDistanceBetweenProducts;
        $vIndent = $this->verticalInsideIndent;

        if ($productsByWidthWithoutDistance !== 1) {
            return (($productsByWidthWithDistance * $width) + ($neededIndents * $hDistance) + ($vIndent * 2)) > $this->getWidthUsingMargins()
                ? $neededIndents
                : $productsByWidthWithDistance
            ;
        }

        return 1;
    }

    /**
     * Calculates a possible number of products by height of the printed sheet by the first way.
     *
     * Возможное количество изделий на листе по высоте (H1/b).
     *
     * @param  int $height A height of the product. Высота изделия (b).
     * @return int
     */
    public function possibleNumberOfProductsByHeightOfPrintedSheetByFirstWay(int $height): int
    {
        $productsByHeightWithoutDistance = $this
            ->numberOfProductsByHeightWithoutDistanceBetweenProductsByFirstWay($height);
        $productByHeightWithDistance = $this->numberOfProductsByHeightWithDistanceBetweenProductsByFirstWay($height);
        $neededIndents = $this->neededNumberOfMinimalVerticalIndentsByFirstWay($height);
        $vDistance = $this->verticalDistanceBetweenProducts;
        $hIndent = $this->horizontalInsideIndent;

        if ($productsByHeightWithoutDistance !== 1) {
            return (($productByHeightWithDistance * $height) + ($neededIndents * $vDistance) + ($hIndent * 2)) > $this->getHeightUsingMargins()
                ? $neededIndents
                : $productByHeightWithDistance
            ;
        }

        return 1;
    }

    /**
     * Counts a number of products on the sheet by the second way.
     *
     * Количество изделий на листе N2.
     * Раскладка 2 (W1/b) * (H1/a).
     *
     * @param  int $height A height of the product. Высота изделия (b).
     * @param  int $width A width of the product. Ширина изделия (a).
     * @return int
     */
    public function countNumberOfProductsOnSheetBySecondWay(int $height, int $width): int
    {
        $numberOfProductsByHeight = $this->possibleNumberOfProductsByHeightOfPrintedSheetBySecondaWay($width);
        $numberOfProductsByWidth = $this->possibleNumberOfProductsByWidthOfPrintedSheetBySecondWay($height);

        return $numberOfProductsByHeight * $numberOfProductsByWidth;
    }

    /**
     * Calculates a number of products by width without a distance between products by the second way.
     *
     * Кол-во изделий по ширине без учета расстояний между изделиями.
     *
     * @param  int $height A height of the product. Высота изделия (b).
     * @return int
     */
    public function numberOfProductsByWidthWithoutDistanceBetweenProductsBySecondWay(int $height): int
    {
        return (($this->getWidthUsingMargins() - $this->horizontalInsideIndent * 2) / $height);
    }

    /**
     * Calculates a preliminary number of products on the printed sheet by width by the second way.
     *
     * Предварительное количество изделий на листе по ширине (W1/(b+sw)).
     *
     * @param  int $height A height of the product. Высота изделия (b).
     * @return int
     */
    public function preliminaryNumberOfProductsOnPrintedSheetByWidth(int $height): int
    {
        return floor($this->getWidthUsingMargins() / ($height + $this->horizontalDistanceBetweenProducts));
    }

    /**
     * Calculates a number of products by height without a distance between products by the second way.
     *
     * Кол-во изделий по высоте без учета расстояний между изделиями.
     *
     * @param  int $width A width of the product. Ширина изделия (a).
     * @return int
     */
    public function numberOfProductsByHeightWithoutDistanceBetweenProductsBySecondWay(int $width): int
    {
        return ($this->getHeightUsingMargins() - $this->verticalInsideIndent * 2) / $width;
    }

    /**
     * Calculates a number of products on the printed sheet by height.
     *
     * Предварительное количество изделий на листе по высоте (H1/(a+sh)).
     *
     * @param  int $width A width of the product. Ширина изделия (a).
     * @return int
     */
    public function preliminaryNumberOfProductsOnPrintedSheetByHeight(int $width): int
    {
        return floor($this->getHeightUsingMargins() / ($width + $this->verticalDistanceBetweenProducts));
    }

    /**
     * Calculates a number of minimal horizontal indents by the second way.
     *
     * Необходимое кол-во минимальных отступов по горизонтали.
     *
     * @param  int $height A height of the product. Высота изделия (b).
     * @return int
     */
    public function neededNumberOfMinimalHorizontalIndentsBySecondWay(int $height): int
    {
        return $this->preliminaryNumberOfProductsOnPrintedSheetByWidth($height) - 1;
    }

    /**
     * Calculates a number of minimal vertical indents by the second way.
     *
     * Необходимое кол-во минимальных отступов по вертикали.
     *
     * @param  int $width A width of the product. Ширина изделия (a).
     * @return int
     */
    public function neededNumberOfMinimalVerticalIndentsBySecondWay(int $width): int
    {
        return $this->preliminaryNumberOfProductsOnPrintedSheetByHeight($width) - 1;
    }

    /**
     * Calculates a possible number of products by width of the printed sheet by the second way.
     *
     * Возможное количество изделий на листе по ширине (W1/b).
     *
     * @param  int $height A height of the product. Высота изделия (b).
     * @return int
     */
    public function possibleNumberOfProductsByWidthOfPrintedSheetBySecondWay(int $height): int
    {
        $numberOfProductByWidthWithoutDistance = $this
            ->numberOfProductsByWidthWithoutDistanceBetweenProductsBySecondWay($height);
        $preliminaryProducts = $this->preliminaryNumberOfProductsOnPrintedSheetByWidth($height);
        $neededIndents = $this->neededNumberOfMinimalHorizontalIndentsBySecondWay($height);
        $hDistance = $this->horizontalDistanceBetweenProducts;
        $hIndent = $this->horizontalInsideIndent;

        if ($numberOfProductByWidthWithoutDistance !== 1) {
            return (($preliminaryProducts * $height) + ($neededIndents * $hDistance) + (2 * $hIndent)) > $this->getWidthUsingMargins()
                ? $neededIndents
                : $preliminaryProducts
            ;
        }

        return 1;
    }

    /**
     * Calculates a possible number of products by height of the printed sheet by the second way.
     *
     * Возможное количество изделий на листе по высоте (H1/a).
     *
     * @param  int $width A width of the product. Ширина изделия (a).
     * @return int
     */
    public function possibleNumberOfProductsByHeightOfPrintedSheetBySecondaWay(int $width): int
    {
        $numberOfProductsByHeightWithoutDistance = $this
            ->numberOfProductsByHeightWithoutDistanceBetweenProductsBySecondWay($width);
        $preliminaryProducts = $this->preliminaryNumberOfProductsOnPrintedSheetByHeight($width);
        $neededIntends = $this->neededNumberOfMinimalVerticalIndentsBySecondWay($width);
        $vDistance = $this->verticalDistanceBetweenProducts;
        $vIndent = $this->verticalInsideIndent;

        if ($numberOfProductsByHeightWithoutDistance !== 1) {
            return (($preliminaryProducts * $width) + ($neededIntends * $vDistance) + (2 * $vIndent)) > $this->getHeightUsingMargins()
                ? $neededIntends
                : $preliminaryProducts
            ;
        }

        return 1;
    }

    /**
     * Counts a number of printed sheets for the given edition size.
     *
     * @param  int $height A height of the product.
     * @param  int $width A width of the product.
     * @param  int $editionSize A edition size of the products.
     * @return int
     */
    public function countNumberOfPrintedSheetsForEditionSize(int $height, int $width, int $editionSize): int
    {
        $numberOfProductsOnSheet = $this->countNumberOfProductsOnSheet($height, $width);

        return $numberOfProductsOnSheet
            ? ceil($editionSize / $this->countNumberOfProductsOnSheet($height, $width))
            : 0
        ;
    }

    /**
     * Returns an optimal way of the placing.
     *
     * @param  int $height
     * @param  int $width
     * @return string
     */
    public function getOptimalWayOfPlacing(int $height, int $width): string
    {
        $firstWay = $this->countNumberOfProductsOnSheetByFirstWay($height, $width);
        $secondWay = $this->countNumberOfProductsOnSheetBySecondWay($height, $width);

        return $firstWay > $secondWay ? self::FIRST_WAY : self::SECOND_WAY ;
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
            'v1' => $this->horizontalDistanceBetweenProducts,
            'v2' => $this->verticalDistanceBetweenProducts,
            'inth' => $this->horizontalInsideIndent,
            'intw' => $this->verticalInsideIndent,
        ];
    }
}
