<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\Values;

use App\Model\Calculator\CalculationVariables\PrintFormats;
use App\Model\Calculator\CalculationVariables\NumberOfProducts;
use App\Model\Calculator\CalculationVariables\StitchingType;
use App\Model\Calculator\CalculationVariables\StitchingWay;
use App\Model\Calculator\TypesOfPrintedSheets\SheetType;
use App\Model\Calculator\TypesOfPrintedSheets\Type;
use App\Services\Calculators\Blocks\UserBlock;
use App\Services\Calculators\Blocks\UserBlocks;

/**
 * An instance of the class keeps values for calculation of an assembly.
 */
class ValuesOfMultipleProduct extends ValuesOfSheet implements ValuesWithManyAssemblies
{
    /**
     * An instance of UserBlocks.
     *
     * @var UserBlocks
     */
    protected $userBlocks;

    /**
     * An instance of NumberOfProducts.
     *
     * @var NumberOfProducts
     */
    protected $numberOfProducts;

    /**
     * A calculated edition size.
     *
     * @var int|null
     */
    protected $calculatedEditionSize;

    /**
     * A price of the stitching way by the the edition size.
     *
     * @var float|null
     */
    protected $priceOfStitchingWay;

    /**
     * A cover height.
     *
     * @var int|null
     */
    protected $coverHeight;

    /**
     * A cover width.
     *
     * @var int|null
     */
    protected $coverWidth;

    /**
     * A substrate height.
     *
     * @var int|null
     */
    protected $substrateHeight;

    /**
     * A substrate width.
     *
     * @var int|null
     */
    protected $substrateWidth;

    /**
     * A block height.
     *
     * @var int|null
     */
    protected $blockHeight;

    /**
     * A block width.
     *
     * @var int|null
     */
    protected $blockWidth;

    /**
     * A number of copybooks.
     *
     * @var int|null
     */
    protected $numberOfCopybooks;

    /**
     * An instance of SheetType of the cover.
     *
     * @var SheetType|null
     */
    protected $sheetOfCover;

    /**
     * An instance of SheetType of the substrate.
     *
     * @var SheetType|null
     */
    protected $sheetOfSubstrate;

    /**
     * An instance of SheetType of the block.
     *
     * @var SheetType|null
     */
    protected $sheetOfBlock;

    /**
     * A number of printed sheets for the cover.
     *
     * @var int|null
     */
    protected $numberOfPrintedSheetsForCover;

    /**
     * A number of printed sheets for the substrate.
     *
     * @var int|null
     */
    protected $numberOfPrintedSheetsForSubstrate;

    /**
     * A number of printed sheets for the block.
     *
     * @var int|null
     */
    protected $numberOfPrintedSheetsForBlock;

    /**
     * A number of products on the sheet of the cover.
     *
     * @var int|null
     */
    protected $numberOfProductsOnSheetOfCover;

    /**
     * A number of products on the sheet of the substrate.
     *
     * @var int|null
     */
    protected $numberOfProductsOnSheetOfSubstrate;

    /**
     * A number of products on the sheet of the block.
     *
     * @var int|null
     */
    protected $numberOfProductsOnSheetOfBlock;

    /**
     * A number of sheets for fitting of the cover.
     *
     * @var int
     */
    protected $numberOfSheetsForFittingOfCover;

    /**
     * A number of sheets for fitting of the substrate.
     *
     * @var int
     */
    protected $numberOfSheetsForFittingOfSubstrate;

    /**
     * A number of sheets for fitting of the block.
     *
     * @var int
     */
    protected $numberOfSheetsForFittingOfBlock;

    /**
     * A type of a used block. Influences the algorithm.
     *
     * @var string|null
     */
    protected $type;

    /**
     * Initializes an instance with user blocks.
     *
     * @param UserBlocks $userBlocks
     */
    public function __construct(UserBlocks $userBlocks)
    {
        $this->userBlocks = $userBlocks;
        $this->recalculateValues();
    }

    /**
     * Returns an instance of the stitching type.
     *
     * @return StitchingType
     */
    public function getStitchingType(): StitchingType
    {
        return $this->userBlocks->getUserBlock('basic')->getCalculationVariableEntityByName('stitching_type');
    }

    /**
     * Returns an appropriate stitching way.
     *
     * @return StitchingWay|null
     */
    public function getStitchingWay(): ?StitchingWay
    {
        return $this->getStitchingType()->getAppropriateWay();
    }

    /**
     * Checks whether the values has a stiching way.
     *
     * @return bool
     */
    public function hasStitchingWay(): bool
    {
        return $this->getStitchingWay() !== null;
    }

    /**
     * Returns a number of products for fitting of the stitching way.
     *
     * @return int|null
     */
    public function getNumberOfProductsForFittingOfStitchingWay(): ?int
    {
        return $this->hasStitchingWay()
            ? $this->getStitchingWay()->getNumberOfProductsForFitting()
            : null
        ;
    }

    /**
     * Returns a calculated edition size.
     *
     * @return int|null
     */
    public function getCalculatedEditionSize(): ?int
    {
        return $this->calculatedEditionSize;
    }

    /**
     * Returns a price of the stitching way.
     *
     * @return float|null
     */
    public function getPriceOfStitchingWay(): ?float
    {
        return $this->priceOfStitchingWay;
    }

    /**
     * Returns a price of stitching way per a product.
     *
     * @return float|null
     */
    public function getPriceOfStitchingWayPerProduct(): ?float
    {
        return $this->priceOfStitchingWayPerProduct;
    }

    /**
     * Returns an instance of basic PrintFormats.
     *
     * @return PrintFormats
     */
    public function getPrintFormats(): PrintFormats
    {
        return $this->userBlocks->getUserBlock('basic')->getCalculationVariableEntityByName('print_formats');
    }

    /**
     * Returns a user height of the product.
     *
     * @return int
     */
    public function getHeightOfProduct(): int
    {
        return $this->getPrintFormats()->getHeight();
    }

    /**
     * Returns a user width of the product.
     *
     * @return int
     */
    public function getWidthOfProduct(): int
    {
        return $this->getPrintFormats()->getWidth();
    }

    /**
     * Returns a number of pages in the block.
     *
     * @return int|null
     */
    public function getNumberOfPagesInBlock(): ?int
    {
        /** @var NumberOfProducts $numberOfPages */
        $numberOfPages = $this->userBlocks->getUserBlock('basic')
            ->getCalculationVariableEntityByName('number_of_pages_in_block')
        ;

        return $numberOfPages->getValue();
    }

    /**
     * Returns a number of copybooks.
     *
     * @return int|null
     */
    public function getNumberOfCopybooks(): ?int
    {
        return $this->numberOfCopybooks;
    }

    /**
     * Checks whether the block is used.
     *
     * @return bool
     */
    public function isBlockUsed(): bool
    {
        return $this->hasStitchingWay()
            ? $this->getStitchingWay()->isBlockUsed()
            : false
        ;
    }

    /**
     * Returns an instance of the block PrintFormats.
     *
     * @return PrintFormats|null
     */
    public function getPrintFormatsOfBlock(): ?PrintFormats
    {
        /** @var UserBlock|null $userBlock */
        $userBlock = $this->getUserBlockOfBlock();

        return $userBlock ? $userBlock->getCalculationVariableEntityByName('print_formats_of_block') : null;
    }

    /**
     * Returns a user height of the block.
     *
     * @return int|null
     */
    public function getUserHeightOfBlock(): ?int
    {
        $printFormats = $this->getPrintFormatsOfBlock();

        return $printFormats ? $printFormats->getHeight() : null;
    }

    /**
     * Returns a user width of the block.
     *
     * @return int|null
     */
    public function getUserWidthOfBlock(): ?int
    {
        $printFormats = $this->getPrintFormatsOfBlock();

        return $printFormats ? $printFormats->getWidth() : null;
    }

    /**
     * Returns a height of the block.
     *
     * @return int|null
     */
    public function getHeightOfBlock(): ?int
    {
        return $this->blockHeight;
    }

    /**
     * Returns a width of the block.
     *
     * @return int|null
     */
    public function getWidthOfBlock(): ?int
    {
        return $this->blockWidth;
    }

    /**
     * Checks whether the cover is used.
     *
     * @return bool
     */
    public function isCoverUsed(): bool
    {
        return $this->hasStitchingWay()
            ? $this->getStitchingWay()->isCoverUsed()
            : false
        ;
    }

    /**
     * Returns an instance of the cover PrintFormats.
     *
     * @return PrintFormats|null
     */
    public function getPrintFormatsOfCover(): ?PrintFormats
    {
        /** @var UserBlock|null $userBlock */
        $userBlock = $this->getUserBlockOfCover();

        return $userBlock ? $userBlock->getCalculationVariableEntityByName('print_formats_of_cover') : null;
    }

    /**
     * Returns a user height of the cover.
     *
     * @return int|null
     */
    public function getUserHeightOfCover(): ?int
    {
        $printFormats = $this->getPrintFormatsOfCover();

        return $printFormats ? $printFormats->getHeight() : null;
    }

    /**
     * Returns a user width of the cover.
     *
     * @return int|null
     */
    public function getUserWidthOfCover(): ?int
    {
        $printFormats = $this->getPrintFormatsOfCover();

        return $printFormats ? $printFormats->getWidth() : null;
    }

    /**
     * Returns a height of the cover.
     *
     * @return int|null
     */
    public function getHeightOfCover(): ?int
    {
        return $this->coverHeight;
    }

    /**
     * Returns a width of the cover.
     *
     * @return int|null
     */
    public function getWidthOfCover(): ?int
    {
        return $this->coverWidth;
    }

    /**
     * Checks whether the substrate is used.
     *
     * @return bool
     */
    public function isSubstrateUsed(): bool
    {
        return $this->hasStitchingWay()
            ? $this->getStitchingWay()->isSubstrateUsed()
            : null
        ;
    }

    /**
     * Returns an instance of the substrate PrintFormats.
     *
     * @return PrintFormats|null
     */
    public function getPrintFormatsOfSubstrate(): ?PrintFormats
    {
        /** @var UserBlock|null $userBlock */
        $userBlock = $this->getUserBlockOfSubstrate();

        return $userBlock ? $userBlock->getCalculationVariableEntityByName('print_formats_of_substrate') : null;
    }

    /**
     * Returns a user height of the substrate.
     *
     * @return int|null
     */
    public function getUserHeightOfSubstrate(): ?int
    {
        $printFormats = $this->getPrintFormatsOfSubstrate();

        return $printFormats ? $printFormats->getHeight() : null;
    }

    /**
     * Returns a user width of the substrate.
     *
     * @return int|null
     */
    public function getUserWidthOfSubstrate(): ?int
    {
        $printFormats = $this->getPrintFormatsOfSubstrate();

        return $printFormats ? $printFormats->getWidth() : null;
    }

    /**
     * Returns a height of the substrate.
     *
     * @return int|null
     */
    public function getHeightOfSubstrate(): ?int
    {
        return $this->substrateHeight;
    }

    /**
     * Returns a width of the substrate.
     *
     * @return int|null
     */
    public function getWidthOfSubstrate(): ?int
    {
        return $this->substrateWidth;
    }

    /**
     * Returns an instance of the NumberOfProducts.
     *
     * @return NumberOfProducts
     */
    public function getInstanceOfNumberOfProducts(): NumberOfProducts
    {
        return $this->userBlocks->getUserBlock('edition_size')->getCalculationVariableEntityByName('number_of_products');
    }

    /**
     * Returns a number of products.
     * The alias of the getNumberOfProductsByType().
     *
     * @return int
     */
    public function getNumberOfProducts(): int
    {
        return $this->getNumberOfProductsByType();
    }

    /**
     * Returns an original edition size.
     *
     * @return int|null
     */
    public function getOriginalNumberOfProducts(): ?int
    {
        return $this->getInstanceOfNumberOfProducts()->getValue();
    }

    /**
     * Sets the given type of a block.
     *
     * @return void
     */
    public function setTypeOfBlock(string $type): void
    {
        $this->type = $type;
    }

    /**
     * Returns the current type of a block.
     *
     * @return string|null
     */
    public function getTypeOfBlock(): ?string
    {
        return $this->type;
    }

    /**
     * Checks whether the user block use an assembly.
     *
     * @param  string $blockName
     * @return bool
     */
    public function doesUserBlockUseAssembly(string $blockName): bool
    {
        return $this->userBlocks->doesUserBlockUseAssembly($blockName);
    }

    /**
     * Sets an instance of SheetType to a block of the current type.
     *
     * @param  SheetType $sheet
     * @return void
     */
    public function setSheetByType(SheetType $sheet): void
    {
        switch ($this->type) {
            case 'cover':
                $this->setSheetForCover($sheet);
                break;

            case 'substrate':
                $this->setSheetForSubstrate($sheet);
                break;

            case 'block':
                $this->setSheetForBlock($sheet);
                break;
        }
    }

    /**
     * Sets an instance of SheetType.
     * The alias of the setSheetByType().
     *
     * @param  SheetType $sheet
     * @return void
     */
    public function setSheet(Type $sheet): void
    {
        $this->setSheetByType($sheet);
    }

    /**
     * Returns an instance of printed sheet of the current type.
     *
     * @return SheetType|null
     */
    public function getSheetByType(): ?SheetType
    {
        switch ($this->type) {
            case 'cover':
                return $this->getSheetOfCover();
            case 'substrate':
                return $this->getSheetOfSubstrate();
            case 'block':
                return $this->getSheetOfBlock();
        }

        return null;
    }

    /**
     * Returns an instance of printed sheet of the current type.
     * The alias of the getSheetByType().
     *
     * @return SheetType|null
     */
    public function getSheet(): ?Type
    {
        return $this->getSheetByType();
    }

    /**
     * Sets an instance of SheetType for the cover.
     *
     * @param  SheetType $sheet
     * @return void
     */
    public function setSheetForCover(SheetType $sheet): void
    {
        $this->sheetOfCover = $sheet;
        $this->numberOfPrintedSheetsForCover = $this->sheetOfCover->countNumberOfPrintedSheetsForEditionSize(
            $this->getHeightOfCover(),
            $this->getWidthOfCover(),
            $this->getNumberOfProducts()
        );
        $this->numberOfProductsOnSheetOfCover = $this->sheetOfCover->countNumberOfProductsOnSheet(
            $this->getHeightOfCover(),
            $this->getWidthOfCover()
        );
    }

    /**
     * Returns an instance of printed sheet of the cover.
     *
     * @return SheetType|null
     */
    public function getSheetOfCover(): ?SheetType
    {
        return $this->sheetOfCover;
    }

    /**
     * Sets an instance of SheetType for the substrate.
     *
     * @param  SheetType $sheet
     * @return void
     */
    public function setSheetForSubstrate(SheetType $sheet): void
    {
        $this->sheetOfSubstrate = $sheet;
        $this->numberOfPrintedSheetsForSubstrate = $this->sheetOfSubstrate->countNumberOfPrintedSheetsForEditionSize(
            $this->getHeightOfSubstrate(),
            $this->getWidthOfSubstrate(),
            $this->getNumberOfProducts()
        );
        $this->numberOfProductsOnSheetOfSubstrate = $this->sheetOfSubstrate->countNumberOfProductsOnSheet(
            $this->getHeightOfSubstrate(),
            $this->getWidthOfSubstrate()
        );
    }

    /**
     * Returns an instance of printed sheet of the substrate.
     *
     * @return SheetType|null
     */
    public function getSheetOfSubstrate(): ?SheetType
    {
        return $this->sheetOfSubstrate;
    }

    /**
     * Sets an instance of SheetType for the block.
     *
     * @param  SheetType $sheet
     * @return void
     */
    public function setSheetForBlock(SheetType $sheet): void
    {
        $this->sheetOfBlock = $sheet;
        $this->numberOfPrintedSheetsForBlock = $this->sheetOfBlock->countNumberOfPrintedSheetsForEditionSize(
            $this->getHeightOfBlock(),
            $this->getWidthOfBlock(),
            $this->getNumberOfProducts()
        );
        $this->numberOfProductsOnSheetOfBlock = $this->sheetOfBlock->countNumberOfProductsOnSheet(
            $this->getHeightOfBlock(),
            $this->getWidthOfBlock()
        );
    }

    /**
     * Returns an instance of printed sheet of the block.
     *
     * @return SheetType|null
     */
    public function getSheetOfBlock(): ?SheetType
    {
        return $this->sheetOfBlock;
    }

    /**
     * Sets a number of printed sheets for fitting.
     *
     * @param  int $value
     * @return void
     */
    public function setNumberOfSheetsForFitting(int $value): void
    {
        switch ($this->type) {
            case 'cover':
                $this->setNumberOfSheetsForFittingOfCover($value);

            case 'substrate':
                $this->setNumberOfSheetsForFittingOfSubstrate($value);

            case 'block':
                $this->setNumberOfSheetsForFittingOfBlock($value);
        }
    }

    /**
     * Returns a number of sheets for fitting.
     *
     * @return int|null
     */
    public function getNumberOfSheetsForFitting(): ?int
    {
        switch ($this->type) {
            case 'cover':
                return $this->getNumberOfSheetsForFittingOfCover();

            case 'substrate':
                return $this->getNumberOfSheetsForFittingOfSubstrate();

            case 'block':
                return $this->getNumberOfSheetsForFittingOfBlock();
        }

        return 0;
    }

    /**
     * Sets a number of printed sheets for fitting of the cover.
     *
     * @param  int $value
     * @return void
     */
    public function setNumberOfSheetsForFittingOfCover(int $value): void
    {
        $this->numberOfSheetsForFittingOfCover = $value;
    }

    /**
     * Returns a number of sheets for fitting of the cover.
     *
     * @return int|null
     */
    public function getNumberOfSheetsForFittingOfCover(): ?int
    {
        return $this->numberOfSheetsForFittingOfCover;
    }

    /**
     * Sets a number of printed sheets for fitting of the substrate.
     *
     * @param  int $value
     * @return void
     */
    public function setNumberOfSheetsForFittingOfSubstrate(int $value): void
    {
        $this->numberOfSheetsForFittingOfSubstrate = $value;
    }

    /**
     * Returns a number of sheets for fitting of the substrate.
     *
     * @return int|null
     */
    public function getNumberOfSheetsForFittingOfSubstrate(): ?int
    {
        return $this->numberOfSheetsForFittingOfSubstrate;
    }

    /**
     * Sets a number of printed sheets for fitting of the block.
     *
     * @param  int $value
     * @return void
     */
    public function setNumberOfSheetsForFittingOfBlock(int $value): void
    {
        $this->numberOfSheetsForFittingOfBlock = $value;
    }

    /**
     * Returns a number of sheets for fitting of the block.
     *
     * @return int|null
     */
    public function getNumberOfSheetsForFittingOfBlock(): ?int
    {
        return $this->numberOfSheetsForFittingOfBlock;
    }

    /**
     * Returns a number of printed sheets with a number of sheets for fitting
     * by the type.
     *
     * @return int|null
     */
    public function getNumberOfPrintedSheetsWithSheetsForFitting(): ?int
    {
        $numberOfPrintedSheets = $this->getNumberOfPrintedSheets();

        return $numberOfPrintedSheets
            ? $this->getNumberOfSheetsForFitting() + $this->getNumberOfPrintedSheets()
            : null
        ;
    }

    /**
     * Returns a height of the product by type.
     *
     * @return int|null
     */
    public function getHeightOfProductByType(): ?int
    {
        switch ($this->type) {
            case 'cover':
                return $this->getHeightOfCover();
            case 'substrate':
                return $this->getHeightOfSubstrate();
            case 'block':
                return $this->getHeightOfBlock();
            default:
                return $this->getHeightOfProduct();
        }
    }

    /**
     * Returns a height of the product.
     * The alias of the getHeightOfProductByType().
     *
     * @return int
     */
    public function getProductHeight(): int
    {
        return $this->getHeightOfProductByType();
    }

    /**
     * Returns a width of the product by type.
     *
     * @return int|null
     */
    public function getWidthOfProductByType(): ?int
    {
        switch ($this->type) {
            case 'cover':
                return $this->getWidthOfCover();
            case 'substrate':
                return $this->getWidthOfSubstrate();
            case 'block':
                return $this->getWidthOfBlock();
            default:
                return $this->getWidthOfProduct();
        }
    }

    /**
     * Returns a width of the product.
     * The alias of the getWidthOfProductByType().
     *
     * @return int
     */
    public function getProductWidth(): int
    {
        return $this->getWidthOfProductByType();
    }

    /**
     * Returns a number of products by the type.
     *
     * @return int|null
     */
    public function getNumberOfProductsByType(): ?int
    {
        switch ($this->type) {
            case 'block':
                return $this->getNumberOfProductsOfBlock();
            default:
                return $this->getNumberOfProductsWithProductsForFitting();
        }
    }

    /**
     * Returns a number of products of block.
     * The formula: кол-во тетрадей * (тираж  + количество изделий на приладку)
     *
     * @return int
     */
    public function getNumberOfProductsOfBlock(): int
    {
        return $this->getNumberOfCopybooks()
            * $this->getNumberOfProductsWithProductsForFitting()
        ;
    }

    /**
     * Returns a number of products with a number of products for fitting.
     * The formula: тираж  + количество изделий на приладку.
     *
     * @return int
     */
    public function getNumberOfProductsWithProductsForFitting(): int
    {
        return $this->getOriginalNumberOfProducts()
            + $this->getNumberOfProductsForFittingOfStitchingWay()
        ;
    }

    /**
     * Returns a number of printed sheets for the cover.
     *
     * @return int|null
     */
    public function getNumberOfPrintedSheetsForCover(): ?int
    {
        return $this->numberOfPrintedSheetsForCover;
    }

    /**
     * Returns a number of printed sheets for the substrate.
     *
     * @return int|null
     */
    public function getNumberOfPrintedSheetsForSubstrate(): ?int
    {
        return $this->numberOfPrintedSheetsForSubstrate;
    }

    /**
     * Returns a number of printed sheets for the block.
     *
     * @return int|null
     */
    public function getNumberOfPrintedSheetsForBlock(): ?int
    {
        return $this->numberOfPrintedSheetsForBlock;
    }

    /**
     * Returns a number of printed sheets by the type.
     *
     * @return int|null
     */
    public function getNumberOfPrintedSheetsByType(): ?int
    {
        switch ($this->type) {
            case 'cover':
                return $this->getNumberOfPrintedSheetsForCover();

            case 'substrate':
                return $this->getNumberOfPrintedSheetsForSubstrate();

            case 'block':
                return $this->getNumberOfPrintedSheetsForBlock();
        }

        return null;
    }

    /**
     * Returns a number of printed sheets.
     *
     * @return int|null
     */
    public function getNumberOfPrintedSheets(): ?int
    {
        return $this->getNumberOfPrintedSheetsByType();
    }

    /**
     * Returns a number of products on the sheet by the type.
     *
     * @return int|null
     */
    public function getNumberOfProductsOnSheetByType(): ?int
    {
        switch ($this->type) {
            case 'cover':
                return $this->getNumberOfProductsOnSheetOfCover();

            case 'substrate':
                return $this->getNumberOfProductsOnSheetOfSubstrate();

            case 'block':
                return $this->getNumberOfProductsOnSheetOfBlock();
        }

        return null;
    }

    /**
     * Returns a number of products on the sheet of the cover.
     *
     * @return int|null
     */
    public function getNumberOfProductsOnSheetOfCover(): ?int
    {
        return $this->numberOfProductsOnSheetOfCover;
    }

    /**
     * Returns a number of products on the sheet of the substrate.
     *
     * @return int|null
     */
    public function getNumberOfProductsOnSheetOfSubstrate(): ?int
    {
        return $this->numberOfProductsOnSheetOfSubstrate;
    }

    /**
     * Returns a number of products on the sheet of the block.
     *
     * @return int|null
     */
    public function getNumberOfProductsOnSheetOfBlock(): ?int
    {
        return $this->numberOfProductsOnSheetOfBlock;
    }

    /**
     * Returns a number of products on the sheet.
     * The alias of the getNumberOfProductsOnSheetByType().
     *
     * @return int
     */
    public function getNumberOfProductsOnSheet(): int
    {
        return $this->getNumberOfProductsOnSheetByType();
    }

    /**
     * Checks whether the required values of calculation variables are defined.
     *
     * @return bool
     */
    public function areRequiredValuesDefined(): bool
    {
        return $this->hasStitchingWay()
            && $this->getOriginalNumberOfProducts()
            && $this->getNumberOfPagesInBlock()
        ;
    }

    /**
     * Recalculates dynamical values.
     *
     * @return void
     */
    public function recalculateValues(): void
    {
        $this->setUserValuesToStitchingWay();
        $this->defineCalculatedVariables();
    }

    /**
     * Returns an instance of UserBlock of the block.
     *
     * @return UserBlock|null
     */
    protected function getUserBlockOfBlock(): ?UserBlock
    {
        return $this->userBlocks->getUserBlock('block');
    }

    /**
     * Returns an instance of UserBlock of the cover.
     *
     * @return UserBlock|null
     */
    protected function getUserBlockOfCover(): ?UserBlock
    {
        return $this->userBlocks->getUserBlock('cover');
    }

    /**
     * Returns an instance of UserBlock of the substrate.
     *
     * @return UserBlock|null
     */
    protected function getUserBlockOfSubstrate(): ?UserBlock
    {
        return $this->userBlocks->getUserBlock('substrate');
    }

    /**
     * Sets user values to the current stitching way.
     *
     * @return void
     */
    protected function setUserValuesToStitchingWay(): void
    {
        if (!$this->areRequiredValuesDefined()) {
            return;
        }

        $this->getStitchingWay()->setEditionSize($this->getOriginalNumberOfProducts());
        $this->getStitchingWay()->setUserValues(
            $this->getHeightOfProduct(),
            $this->getWidthOfProduct(),
            $this->getUserHeightOfCover(),
            $this->getUserWidthOfCover(),
            $this->getUserHeightOfSubstrate(),
            $this->getUserWidthOfSubstrate(),
            $this->getUserHeightOfBlock(),
            $this->getUserWidthOfBlock(),
            $this->getNumberOfPagesInBlock(),
            $this->getOriginalNumberOfProducts(),
        );
    }

    /**
     * Defines calculated variables to the instance.
     *
     * @return void
     */
    protected function defineCalculatedVariables(): void
    {
        if (!$this->areRequiredValuesDefined()) {
            return;
        }

        $this->calculatedEditionSize = $this->getStitchingWay()->getCalculatedEditionSize();
        $price = $this->getStitchingWay()->getPriceByCalculatedEditionSize();
        $this->priceOfStitchingWay = $price ? $price->getValueByCurrency() : null;
        $pricePerProduct = $this->getStitchingWay()->getPricePerProductByCalculatedEditionSize();
        $this->priceOfStitchingWayPerProduct = $pricePerProduct ? $pricePerProduct->getValueByCurrency() : null;

        $this->coverWidth = $this->getStitchingWay()->getCoverWidth();;
        $this->coverHeight = $this->getStitchingWay()->getCoverHeight();;
        $this->substrateWidth = $this->getStitchingWay()->getSubstrateWidth();;
        $this->substrateHeight = $this->getStitchingWay()->getSubstrateHeight();
        $this->blockWidth = $this->getStitchingWay()->getBlockWidth();
        $this->blockHeight = $this->getStitchingWay()->getBlockHeight();
        $this->numberOfCopybooks = $this->getStitchingWay()->getNumberOfCopybooks();
    }
}
