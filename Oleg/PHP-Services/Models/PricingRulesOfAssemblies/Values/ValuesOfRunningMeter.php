<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\Values;

use App\Model\Calculator\CalculationVariables\PrintFormats;
use App\Model\Calculator\CalculationVariables\NumberOfProducts;
use App\Model\Calculator\Settings\AlgorithmOfLength;
use App\Model\Calculator\Settings\AlgorithmWithUnit;
use App\Model\Calculator\TypesOfPrintedSheets\Type;
use App\Model\Calculator\TypesOfPrintedSheets\WideFormatType;
use App\Services\Calculators\Blocks\UserBlocks;

/**
 * An instance of the class keeps values for calculation of an assembly.
 */
class ValuesOfRunningMeter extends ValuesOfWideSheets
{
    /**
     * A PrintFormats.
     *
     * @var PrintFormats
     */
    protected $printFormats;

    /**
     * A NumberOfProducts.
     *
     * @var NumberOfProducts
     */
    protected $numberOfProducts;

    /**
     * An AlgorithmOfLength.
     *
     * @var AlgorithmOfLength
     */
    protected $algorithmOfLength;

    /**
     * A length of the material of one product.
     *
     * @var int|null
     */
    protected $lengthOfMaterialOfProduct;

    /**
     * A length of the material of products.
     *
     * @var int|null
     */
    protected $lengthOfMaterialOfProducts;

    /**
     * Initializes an instance of the class using values.
     *
     * @param UserBlocks        $userBlocks
     * @param AlgorithmOfLength $algorithmOfLength
     */
    function __construct(
        UserBlocks $userBlocks,
        AlgorithmOfLength $algorithmOfLength
    ) {
        $this->userBlocks = $userBlocks;
        $this->algorithmOfLength = $algorithmOfLength;
    }

    /**
     * Sets an instance of sheet type.
     *
     * @param WideFormatType $sheet
     */
    public function setSheet(Type $sheet): void
    {
        parent::setSheet($sheet);
        $this->defineLengthsOfMaterial();
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
     * Returns a length (mm) of the material of one product.
     *
     * @return int|null
     */
    public function getLengthOfProduct(): ?int
    {
        return $this->lengthOfMaterialOfProduct;
    }

    /**
     * Returns a length (mm) of material of products.
     *
     * @return int|null
     */
    public function getLengthOfMaterialOfProducts(): ?int
    {
        return $this->lengthOfMaterialOfProducts;
    }

    /**
     * Returns an instance of AlgorithmWithUnit.
     *
     * @return AlgorithmOfLength
     */
    public function getAlgorithmWithUnit(): AlgorithmWithUnit
    {
        return $this->algorithmOfLength;
    }

    /**
     * Returns a name of the algorithm.
     *
     * @return string
     */
    public function getAlgorithmType(): string
    {
        return $this->algorithmOfLength->getAlgorithmType();
    }

    /**
     * Returns a unit for material.
     *
     * @return string
     */
    public function getUnitOfMaterial(): string
    {
        return $this->algorithmOfLength->getUnit();
    }

    /**
     * Returns an unrounded length of the material by a unit of the calculator.
     *
     * @return float|null
     */
    public function getRawNumberOfMaterialByUnit(): ?float
    {
        return $this->getAlgorithmType() === AlgorithmOfLength::LENGTH_OF_MATERIAL_TYPE
            ? $this->getUnroundedLengthOfMaterialOfProductsByUnit()
            : $this->getUnroundedLengthOfMaterialOfProductByUnit()
        ;
    }

    /**
     * Returns an unrounded length of the material of products by a unit.
     *
     * @return float|null
     */
    public function getUnroundedLengthOfMaterialOfProductsByUnit(): ?float
    {
        $lengthOfMaterial = $this->getLengthOfMaterialOfProducts();

        return isset($lengthOfMaterial)
            ? $this->algorithmOfLength->convertUsingMultiplier($lengthOfMaterial)
            : null
        ;
    }

    /**
     * Returns an unrounded length of material of one product by a unit.
     *
     * @return float|null
     */
    public function getUnroundedLengthOfMaterialOfProductByUnit(): ?float
    {
        $lengthOfProduct = $this->getLengthOfProduct();

        return isset($lengthOfProduct)
            ? $this->algorithmOfLength->convertUsingMultiplier($lengthOfProduct)
            : null
        ;
    }

    /**
     * Returns a rounded number of a length of the material
     * by a unit of the calculator and by the algorithm type.
     * It is an actual number of material for manufacturing.
     *
     * @return float|null
     */
    public function getNumberOfMaterialByUnit(): ?float
    {
        /** @var float|null $number */
        $lengthOfMaterial = $this->getRawNumberOfMaterialByUnit();

        return $lengthOfMaterial ? ceil($lengthOfMaterial) : null;
    }

    /**
     * Returns a priority of the printed sheet.
     *
     * @return int|null
     */
    public function getPriorityOfPrintedSheet(): ?int
    {
        return $this->sheet ? $this->sheet->getPriority() : null;
    }

    /**
     * Calculates a length (mm) of the material of one product
     * by the instance of wide format sheet.
     *
     * @param  WideFormatType $instanceOfType
     * @return int
     */
    public function calculateLengthOfProductByWideFormatSheet(WideFormatType $instanceOfType): int
    {
        return $instanceOfType->countLengthOfMaterial($this->getHeightOfProduct(), $this->getWidthOfProduct(), 1);
    }

    /**
     * Calculates a length (mm) of the material of products
     * by the given wide format sheet.
     *
     * @param  WideFormatType $instanceOfType
     * @return int
     */
    public function calculateLengthOfMaterialByWideFormatSheet(WideFormatType $instanceOfType): int
    {
        return $instanceOfType->countLengthOfMaterial(
            $this->getHeightOfProduct(),
            $this->getWidthOfProduct(),
            $this->getNumberOfProducts()
        );
    }

    /**
     * Defines lengths of the material.
     *
     * @return void
     */
    protected function defineLengthsOfMaterial(): void
    {
        if (isset($this->sheet)) {
            $this->lengthOfMaterialOfProduct = $this->calculateLengthOfProductByWideFormatSheet($this->sheet);
            $this->lengthOfMaterialOfProducts = $this->calculateLengthOfMaterialByWideFormatSheet($this->sheet);
        }
    }
}
