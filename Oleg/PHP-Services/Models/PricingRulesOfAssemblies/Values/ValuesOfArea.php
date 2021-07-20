<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\Values;

use App\Model\Calculator\CalculationVariables\Eyelets;
use App\Model\Calculator\CalculationVariables\Gluing;
use App\Model\Calculator\CalculationVariables\PrintFormats;
use App\Model\Calculator\CalculationVariables\NumberOfProducts;
use App\Model\Calculator\CalculationVariables\Sides;
use App\Model\Calculator\Settings\AlgorithmOfArea;
use App\Model\Calculator\Settings\AlgorithmWithUnit;
use App\Model\Calculator\Settings\MarkupForNarrowProduct;
use App\Model\Calculator\Settings\PrintByParts;
use App\Services\Calculators\Blocks\UserBlocks;

/**
 * An instance of the class keeps values for calculation of an assembly.
 */
class ValuesOfArea extends ValuesOfWideSheets
{
    /**
     * An instance of UserBlocks.
     *
     * @var UserBlocks
     */
    protected $userBlocks;

    /**
     * An instance of AlgorithmOfArea.
     *
     * @var AlgorithmOfArea
     */
    protected $algorithmOfArea;

    /**
     * An instance of PrintByParts.
     *
     * @var PrintByParts
     */
    protected $printByParts;

    /**
     * An instance of MarkupForNarrowProduct.
     *
     * @var MarkupForNarrowProduct
     */
    protected $markupForNarrowProduct;

    /**
     * Initializes an instance of the class.
     *
     * @param UserBlocks $userBlocks
     * @param AlgorithmOfArea $algorithmOfArea
     * @param PrintByParts $printByParts
     * @param MarkupForNarrowProduct $markupForNarrowProduct
     */
    public function __construct(
        UserBlocks $userBlocks,
        AlgorithmOfArea $algorithmOfArea,
        PrintByParts $printByParts,
        MarkupForNarrowProduct $markupForNarrowProduct
    ) {
        $this->userBlocks = $userBlocks;
        $this->algorithmOfArea = $algorithmOfArea;
        $this->printByParts = $printByParts;
        $this->markupForNarrowProduct = $markupForNarrowProduct;

        $this->defineCalculatedVariables();
    }

    /**
     * Returns an instance of PrintFormats.
     *
     * @return PrintFormats
     */
    public function getPrintFormats(): PrintFormats
    {
        return $this->userBlocks->getUserBlock('standard')->getCalculationVariableEntityByName('print_formats');
    }

    /**
     * Returns a height of the product.
     *
     * @return int
     */
    public function getHeightOfProduct(): int
    {
        return $this->getPrintFormats()->getHeight();
    }

    /**
     * Returns a width of the product.
     *
     * @return int
     */
    public function getWidthOfProduct(): int
    {
        return $this->getPrintFormats()->getWidth();
    }

    /**
     * Returns a length of large side of the product.
     *
     * @return int
     */
    public function getLengthOfLargeSideOfProduct(): int
    {
        return max($this->getHeightOfProduct(), $this->getWidthOfProduct());
    }

    /**
     * Returns a length of a small side of the product.
     *
     * @return int
     */
    public function getLengthOfSmallSideOfProduct(): int
    {
        return min($this->getHeightOfProduct(), $this->getWidthOfProduct());
    }

    /**
     * Returns an area (square mm) of the product.
     *
     * @return int
     */
    public function getAreaOfProduct(): int
    {
        return $this->getPrintFormats()->getArea();
    }

    /**
     * Returns a total area (square mm) of products.
     *
     * @return int
     */
    public function getTotalAreaOfProducts(): int
    {
        return $this->getAreaOfProduct() * $this->getNumberOfProducts();
    }

    /**
     * Returns a number of products.
     *
     * @return int
     */
    public function getNumberOfProducts(): int
    {
        /** @var NumberOfProducts $numberOfProducts */
        $numberOfProducts = $this->userBlocks->getUserBlock('standard')
            ->getCalculationVariableEntityByName('number_of_products')
        ;

        return $numberOfProducts->getValue();
    }

    /**
     * Returns an instance of AlgorithmWithUnit.
     *
     * @return AlgorithmOfArea
     */
    public function getAlgorithmWithUnit(): AlgorithmWithUnit
    {
        return $this->algorithmOfArea;
    }

    /**
     * Returns a unit of a material.
     *
     * @return string
     */
    public function getUnitOfMaterial(): string
    {
        return $this->algorithmOfArea->getUnit();
    }

    /**
     * Returns an algorithm type.
     *
     * @return string
     */
    public function getAlgorithmType(): string
    {
        return $this->algorithmOfArea->getAlgorithmType();
    }

    /**
     * Returns a unrounded number of the material by a unit of the calculator.
     *
     * @return float|null
     */
    public function getRawNumberOfMaterialByUnit(): ?float
    {
        return $this->getAlgorithmType() === AlgorithmOfArea::TOTAL_AREA_OF_PRODUCTS_TYPE
            ? $this->getUnroundedTotalAreaOfProductsByUnit()
            : $this->getUnroundedAreaOfProductByUnit()
        ;
    }

    /**
     * Returns a unrounded total area of products by a unit of the calculator.
     *
     * @return float
     */
    public function getUnroundedTotalAreaOfProductsByUnit(): float
    {
        return $this->algorithmOfArea->convertUsingMultiplier($this->getTotalAreaOfProducts());
    }

    /**
     * Returns an area of product by a unit of the calculator.
     *
     * @return float
     */
    public function getUnroundedAreaOfProductByUnit(): float
    {
        return $this->algorithmOfArea->convertUsingMultiplier($this->getAreaOfProduct());
    }

    /**
     * Returns a rounded number of the material by a unit of the calculator.
     *
     * @return float|null
     */
    public function getNumberOfMaterialByUnit(): ?float
    {
        return $this->getAlgorithmType() === AlgorithmOfArea::TOTAL_AREA_OF_PRODUCTS_TYPE
            ? $this->getTotalAreaOfProductsByUnit()
            : $this->getAreaOfProductByUnit()
        ;
    }

    /**
     * Returns a rounded total area of products by a unit of the calculator.
     *
     * @return int
     */
    public function getTotalAreaOfProductsByUnit(): int
    {
        return $this->algorithmOfArea->convertUsingMultiplierAndRound($this->getTotalAreaOfProducts());
    }

    /**
     * Returns a rounded area of product by a unit of the calculator.
     *
     * @return int
     */
    public function getAreaOfProductByUnit(): int
    {
        return $this->algorithmOfArea->convertUsingMultiplierAndRound($this->getAreaOfProduct());
    }

    /**
     * Checks whether the calculator uses the print by parts.
     *
     * @return bool
     */
    public function doesUsePrintByParts(): bool
    {
        return $this->printByParts->isActive();
    }

    /**
     * Checks whether the calculator uses the coefficient for a narrow product.
     *
     * @return bool
     */
    public function doesUseCoefficientForNarrowProduct(): bool
    {
        return $this->markupForNarrowProduct->isActive();
    }

    /**
     * Returns a value of the coefficient for a narrow product.
     *
     * @return float
     */
    public function getCoefficientForNarrowProduct(): float
    {
        return $this->markupForNarrowProduct->isActive()
            ? $this->markupForNarrowProduct->getCoefficient()
            : 1
        ;
    }

    /**
     * Calculates a price with coefficient for a narrow product.
     *
     * @param  float $price
     * @return float
     */
    public function calculatePriceWithCoefficientForNarrowProduct(float $price): float
    {
        return $this->markupForNarrowProduct->apply($price);
    }

    /**
     * Checks whether the product is small.
     *
     * @return bool
     */
    public function isProductSmall(): bool
    {
        $largeSide = $this->getLengthOfLargeSideOfProduct();
        $smallSide = $this->getLengthOfSmallSideOfProduct();
        $widthOfWorkSheet = $this->getSheet()->getWidthUsingMargins();
        $haltOfWidthOfWorkSheet = $widthOfWorkSheet / 2;

        return $largeSide > $widthOfWorkSheet && $smallSide < $haltOfWidthOfWorkSheet;
    }

    /**
     * Returns an instance of Gluing.
     *
     * @return Gluing
     */
    public function getGluing(): Gluing
    {
        return $this->userBlocks->getUserBlock('standard')->getCalculationVariableEntityByName('gluing');
    }

    /**
     * Checks whether the gluing is used.
     *
     * @return bool
     */
    public function isGluingUsed(): bool
    {
        return $this->getGluing()->isUsed();
    }

    /**
     * Returns a length of the gluing.
     *
     * @return int|null
     */
    public function getLengthOfGluing(): ?int
    {
        return $this->getGluing()->getLengthOfGluing();
    }

    /**
     * Returns a total length of the gluing.
     *
     * @return int|null
     */
    public function getTotalLengthOfGluing(): ?int
    {
        return $this->getGluing()->getTotalLengthOfGluing();
    }

    /**
     * Returns a price of the gluing.
     *
     * @return float|null
     */
    public function getPriceOfGluing(): ?float
    {
        return $this->getGluing()->getPrice();
    }

    /**
     * Returns an instance of Eyelets.
     *
     * @return Eyelets
     */
    public function getEyelets(): Eyelets
    {
        return $this->userBlocks->getUserBlock('standard')->getCalculationVariableEntityByName('eyelets');
    }

    /**
     * Checks whether the eyelets are used.
     *
     * @return bool
     */
    public function areEyeletsUsed(): bool
    {
        return $this->getEyelets()->areEyeletsUsed();
    }

    /**
     * Returns a type of position of the eyelets.
     *
     * @return string|null
     */
    public function getTypeOfPositionOfEyelets(): ?string
    {
        return $this->getEyelets()->getTypeOfPosition();
    }

    /**
     * Returns a step in cm between points of the eyelets.
     *
     * @return int|null
     */
    public function getStepBetweenPointsOfEyelets(): ?int
    {
        return $this->getEyelets()->getStepBetweenPoints();
    }

    /**
     * Returns positions of points of the eyelets.
     *
     * @return array|string[]|null
     */
    public function getPositionsOfPointsOfEyelets(): ?array
    {
        return $this->getEyelets()->getPositionsOfPoints();
    }

    /**
     * Returns a calculated number of eyelets of a product by the current type
     * of the position.
     *
     * @return int|null
     */
    public function getNumberOfEyelets(): ?int
    {
        return $this->getEyelets()->getNumberOfEyelets();
    }

    /**
     * Returns a total number of eyelets of all products.
     *
     * @return int|null
     */
    public function getTotalNumberOfEyelets(): ?int
    {
        return $this->getEyelets()->getTotalNumberOfEyelets();
    }

    /**
     * Returns a price of the eyelets.
     *
     * @return float|null
     */
    public function getPriceOfEyelets(): ?float
    {
        return $this->getEyelets()->getPrice();
    }

    /**
     * Returns an instance of Sides for the pole pocket.
     *
     * @return Sides
     */
    public function getPolePocket(): Sides
    {
        return $this->userBlocks->getUserBlock('standard')->getCalculationVariableEntityByName('pole_pocket');
    }

    /**
     * Checks whether the pole pocket is used.
     *
     * @return bool
     */
    public function isPolePocketUsed(): bool
    {
        return $this->getPolePocket()->areSidesUsed();
    }

    /**
     * Returns a length of sides of the pole pocket.
     *
     * @return int|null
     */
    public function getLengthOfPolePocket(): ?int
    {
        return $this->getPolePocket()->getLengthOfSides();
    }

    /**
     * Returns a total length of sides of the pole pocket.
     *
     * @return int|null
     */
    public function getTotalLengthOfPolePocket(): ?int
    {
        return $this->getPolePocket()->getTotalLengthOfSides();
    }

    /**
     * Returns used sides of the pole pocket.
     *
     * @return array|string[]|null
     */
    public function getSidesOfPolePocket(): ?array
    {
        return $this->getPolePocket()->getUsedSides();
    }

    /**
     * Returns a price of the pole pocket.
     *
     * @return float|null
     */
    public function getPriceOfPolePocket(): ?float
    {
        return $this->getPolePocket()->getPrice();
    }

    /**
     * Returns an instance of Sides for the edge reinforcement.
     *
     * @return Sides
     */
    public function getEdgeReinforcement(): Sides
    {
        return $this->userBlocks->getUserBlock('standard')->getCalculationVariableEntityByName('edge_reinforcement');
    }

    /**
     * Checks whether the edge reinforcement is used.
     *
     * @return bool
     */
    public function isEdgeReinforcementUsed(): bool
    {
        return $this->getEdgeReinforcement()->areSidesUsed();
    }

    /**
     * Returns a length of sides of the edge reinforcement.
     *
     * @return int|null
     */
    public function getLengthOfEdgeReinforcement(): ?int
    {
        return $this->getEdgeReinforcement()->getLengthOfSides();
    }

    /**
     * Returns a total length of sides of the edge reinforcement.
     *
     * @return int|null
     */
    public function getTotalLengthOfEdgeReinforcement(): ?int
    {
        return $this->getEdgeReinforcement()->getTotalLengthOfSides();
    }

    /**
     * Returns used sides of the edge reinforcement.
     *
     * @return array|string[]|null
     */
    public function getSidesOfEdgeReinforcement(): ?array
    {
        return $this->getPolePocket()->getUsedSides();
    }

    /**
     * Returns a price of the edge reinforcement.
     *
     * @return float|null
     */
    public function getPriceOfEdgeReinforcement(): ?float
    {
        return $this->getEdgeReinforcement()->getPrice();
    }

    /**
     * Sets values to calculation variables and recalculates prices
     * of calculation variables.
     *
     * @return void
     */
    protected function defineCalculatedVariables(): void
    {
        $this->defineGluing();
        $this->definePolePocket();
        $this->defineEdgeReinforcement();
        $this->defineEyelets();
    }

    /**
     * Defines the gluing.
     *
     * @return void
     */
    protected function defineGluing(): void
    {
        $gluing = $this->getGluing();
        $gluing->setNumberOfProducts($this->getNumberOfProducts());
    }

    /**
     * Defines the pole pocket.
     *
     * @return void
     */
    protected function definePolePocket(): void
    {
        $polePocket = $this->getPolePocket();
        $polePocket->setNumberOfProducts($this->getNumberOfProducts());
        $polePocket->setProductSize($this->getHeightOfProduct(), $this->getWidthOfProduct());
    }

    /**
     * Defines the edge reinforcement.
     *
     * @return void
     */
    protected function defineEdgeReinforcement(): void
    {
        $edgeReinforcement = $this->getEdgeReinforcement();
        $edgeReinforcement->setNumberOfProducts($this->getNumberOfProducts());
        $edgeReinforcement->setProductSize($this->getHeightOfProduct(), $this->getWidthOfProduct());
    }

    /**
     * Defines the eyelets.
     *
     * @return void
     */
    protected function defineEyelets(): void
    {
        $eyelets = $this->getEyelets();
        $eyelets->setNumberOfProducts($this->getNumberOfProducts());
        $eyelets->setProductSize($this->getHeightOfProduct(), $this->getWidthOfProduct());
    }
}
