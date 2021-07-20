<?php

namespace App\Model\Calculator\CalculationVariables;

use App\Model\Calculator\CalculationVariables\PricingRules\CustomNumber;
use App\Model\Calculator\CalculationVariables\PricingRules\PricingRule;
use App\Model\Calculator\CalculationVariables\PricingRules\Wholesale;
use App\Model\Calculator\PricingRulesOfAssemblies\Cost;
use App\Services\Calculators\Error;
use App\Services\Calculators\Errors;
use App\Services\Formula\Formula;
use App\Services\Formula\FormulaService;
use App\Services\Formula\VariableCollection;

/**
 * Keeps a description of the stitching way.
 */
class StitchingWay
{
    /**
     * A name of the way.
     *
     * @var string
     */
    protected $name;

    /**
     * A type of the pricing rule.
     *
     * @var string
     */
    protected $typeOfPricingRule;

    /**
     * An instance of a pricing rule.
     *
     * @var PricingRule
     */
    protected $pricingRule;

    /**
     * A number of products for fitting.
     *
     * @var int
     */
    protected $numberOfProductsForFitting;

    /**
     * A state of using of cover.
     *
     * @var bool
     */
    protected $stateOfCover;

    /**
     * Formula of a cover height.
     *
     * @var string|null
     */
    protected $formulaOfCoverWidth;

    /**
     * Formula of a cover height.
     *
     * @var string|null
     */
    protected $formulaOfCoverHeight;

    /**
     * A state of the substrate.
     *
     * @var bool
     */
    protected $stateOfSubstrate;

    /**
     * Formula of a substrate height.
     *
     * @var string|null
     */
    protected $formulaOfSubstrateWidth;

    /**
     * Formula of a substrate height.
     *
     * @var string|null
     */
    protected $formulaOfSubstrateHeight;

    /**
     * A state of using of block.
     *
     * @var bool
     */
    protected $stateOfBlock;

    /**
     * Formula of a block width.
     *
     * @var string|null
     */
    protected $formulaOfBlockWidth;

    /**
     * Formula of a block height.
     *
     * @var string|null
     */
    protected $formulaOfBlockHeight;

    /**
     * A min number of pages.
     *
     * @var int
     */
    protected $minNumberOfPages;

    /**
     * A max number of the pages.
     *
     * @var int
     */
    protected $maxNumberOfPages;

    /**
     * Frequency of the pages.
     *
     * @var int
     */
    protected $frequencyOfPages;

    /**
     * Formula of a number of copybooks.
     *
     * @var string|null
     */
    protected $formulaOfNumberOfCopybooks;

    /**
     * An instance of FormulaService.
     *
     * @var FormulaService
     */
    protected $formulaService;

    /**
     * A user edition size.
     *
     * @var int|null
     */
    protected $editionSize;

    /**
     * A price by the calculated edition size.
     *
     * @var Cost|null
     */
    protected $priceByCalculatedEditionSize;

    /**
     * A price per a product bu the calculated edition size.
     *
     * @var Cost|null
     */
    protected $pricePerProductByCalculatedEditionSize;

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
     * An instance of Errors.
     *
     * @var Errors
     */
    protected $errors;

    /**
     * Initializes an instance of the stitching way.
     *
     * @param array $values
     */
    public function __construct(array $values = [])
    {
        $this->fillFromArray($values);
        $this->formulaService = new FormulaService;
        $this->errors = new Errors;
    }

    /**
     * Fills the instance from an array.
     *
     * @param  array $values
     * @return void
     */
    public function fillFromArray(array $values): void
    {
        $this->name = $values['name'];
        $this->typeOfPricingRule = $values['price']['type'] ?? null;

        if ($this->typeOfPricingRule === 'custom') {
            $this->pricingRule = new CustomNumber($values['price']['pricing_rule']['custom'] ?? []);
        } elseif ($this->typeOfPricingRule === 'wholesale') {
            $this->pricingRule = new Wholesale($values['price']['pricing_rule']['wholesale'] ?? []);
        } else {
            $this->pricingRule = null;
        }

        $this->numberOfProductsForFitting = (int) ($values['price']['products_for_fitting'] ?? 0);
        $this->stateOfCover = (bool) ($values['cover']['active'] ?? false);
        $this->formulaOfCoverWidth = $values['cover']['formula_of_width'] ?? null;
        $this->formulaOfCoverHeight = $values['cover']['formula_of_height'] ?? null;
        $this->stateOfSubstrate = (bool) ($values['substrate']['active'] ?? false);
        $this->formulaOfSubstrateWidth = $values['substrate']['formula_of_width'] ?? null;
        $this->formulaOfSubstrateHeight = $values['substrate']['formula_of_height'] ?? null;
        // NOTE: The block always is active.
        $this->stateOfBlock = true;
        $this->formulaOfBlockWidth = $values['block']['formula_of_width'] ?? null;
        $this->formulaOfBlockHeight = $values['block']['formula_of_height'] ?? null;
        $this->minNumberOfPages = (int) ($values['block']['min_number_of_pages'] ?? 0);
        $this->maxNumberOfPages = $values['block']['max_number_of_pages']
            ? (int) $values['block']['max_number_of_pages']
            : null
        ;
        $this->frequencyOfPages = (int) ($values['block']['frequency_of_pages'] ?? 1);
        $this->formulaOfNumberOfCopybooks = $values['block']['number_of_copybooks'] ?? null;
    }

    /**
     * Validates the stitching way.
     *
     * @return bool
     */
    public function validate(): bool
    {
        $state = true;

        if (!$this->getTypeOfPricingRule()) {
            $this->errors->add(
                trans('calculator_errors.type_of_pricing_rule_of_stitching_type_is_undefined', [
                    'name' => $this->getName(),
                ]),
                Error::VARIABLE_OF_CALCULATION_ERROR,
            );
            $state = false;
        } elseif (!$this->getPricingRule()) {
            $this->errors->add(
                trans('calculator_errors.pricing_rule_of_stitching_type_not_found', [
                    'name' => $this->getName(),
                ]),
                Error::VARIABLE_OF_CALCULATION_ERROR,
            );
            $state = false;
        } elseif (!$this->getPricingRule()->isCorrect()) {
            $this->errors->add(
                trans('calculator_errors.pricing_rule_of_stitching_type_is_incorrect', [
                    'name' => $this->getName(),
                ]),
                Error::VARIABLE_OF_CALCULATION_ERROR,
            );
            $state = false;
        } elseif ($this->numberOfProductsForFitting < 0) {
            $this->errors->add(
                trans('calculator_errors.number_of_products_for_fitting_in_stitching_way_is_incorrect', [
                    'name' => $this->getName(),
                ]),
                Error::VARIABLE_OF_CALCULATION_ERROR,
            );
            $state = false;
        } elseif (!$this->isCoverCorrect()) {
            $this->errors->add(
                trans('calculator_errors.cover_of_stitching_type_is_incorrect', [
                    'name' => $this->getName(),
                ]),
                Error::VARIABLE_OF_CALCULATION_ERROR,
            );
            $state = false;
        } elseif (!$this->isSubstrateCorrect()) {
            $this->errors->add(
                trans('calculator_errors.substrate_of_stitching_type_is_incorrect', [
                    'name' => $this->getName(),
                ]),
                Error::VARIABLE_OF_CALCULATION_ERROR,
            );
            $state = false;
        } elseif (!$this->isBlockCorrect()) {
            $this->errors->add(
                trans('calculator_errors.block_of_stitching_type_is_incorrect', [
                    'name' => $this->getName(),
                ]),
                Error::VARIABLE_OF_CALCULATION_ERROR,
            );
            $state = false;
        }

        return $state;
    }

    /**
     * Sets a user edition size.
     *
     * @param  int $size
     * @return void
     */
    public function setEditionSize(int $size): void
    {
        $this->editionSize = $size;
        $this->priceByCalculatedEditionSize = $this->calculatePriceByEditionSize();

        if ($this->priceByCalculatedEditionSize) {
            $this->pricePerProductByCalculatedEditionSize = new Cost(
                $this->priceByCalculatedEditionSize->getValue() / $size,
                $this->priceByCalculatedEditionSize->getCurrency()
            );
        } else {
            $this->pricePerProductByCalculatedEditionSize = null;
        }
    }

    /**
     * Returns a user edition size.
     *
     * @return int|null
     */
    public function getEditionSize(): ?int
    {
        return $this->editionSize;
    }

    /**
     * Returns a calculated edition size by the current edition size.
     * It is a sum of the given size and a number of products for fitting.
     *
     * @return int|null
     */
    public function getCalculatedEditionSize(): ?int
    {
        return $this->editionSize
            ? $this->editionSize + $this->getNumberOfProductsForFitting()
            : null
        ;
    }

    /**
     * Returns a price by the calculated edition size.
     *
     * @return Cost|null
     */
    public function getPriceByCalculatedEditionSize(): ?Cost
    {
        return $this->priceByCalculatedEditionSize;
    }

    /**
     * Returns a price per a product by the calculated edition size.
     *
     * @return Cost|null
     */
    public function getPricePerProductByCalculatedEditionSize(): ?Cost
    {
        return $this->pricePerProductByCalculatedEditionSize;
    }

    /**
     * Fills the instance with user values.
     *
     * @param  int      $userHeightOfProduct
     * @param  int      $userWidthOfProduct
     * @param  int|null $userHeightOfCover
     * @param  int|null $userWidthOfCover
     * @param  int|null $userHeightOfSubstrate
     * @param  int|null $userWidthOfSubstrate
     * @param  int|null $userHeightOfBlock
     * @param  int|null $userWidthOfBlock
     * @param  int      $numberOfPagesInBlock
     * @param  int      $editionSize
     * @return void
     */
    public function setUserValues(
        int $userHeightOfProduct,
        int $userWidthOfProduct,
        ?int $userHeightOfCover,
        ?int $userWidthOfCover,
        ?int $userHeightOfSubstrate,
        ?int $userWidthOfSubstrate,
        ?int $userHeightOfBlock,
        ?int $userWidthOfBlock,
        int $numberOfPagesInBlock,
        int $editionSize
    ): void {
        $variables = new VariableCollection;
        $variables->add('user_product_height', '{Высота}', $userHeightOfProduct);
        $variables->add('user_product_width', '{Ширина}', $userWidthOfProduct);
        $variables->add('user_cover_height', '{Высота обложки}', $userHeightOfCover);
        $variables->add('user_cover_width', '{Ширина обложки}', $userWidthOfCover);
        $variables->add('user_substrate_height', '{Высота подложки}', $userHeightOfSubstrate);
        $variables->add('user_substrate_width', '{Ширина подложки}', $userWidthOfSubstrate);
        $variables->add('user_block_height', '{Высота блока}', $userHeightOfBlock);
        $variables->add('user_block_width', '{Ширина блока}', $userWidthOfBlock);
        $variables->add('number_of_pages_in_block', '{Кол-во страниц в блоке}', $numberOfPagesInBlock);
        $variables->add('edition_size', '{Тираж}', $editionSize);

        $this->formulaService->setVariables($variables);
        $this->calculateFormulaeByUserVariables();
    }

    /**
     * Returns a name of the way.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns a type of the pricing rule.
     *
     * @return string|null
     */
    public function getTypeOfPricingRule(): ?string
    {
        return $this->typeOfPricingRule;
    }

    /**
     * Returns an instance of the pricing rule.
     *
     * @return PricingRule|null
     */
    public function getPricingRule(): ?PricingRule
    {
        return $this->pricingRule;
    }

    /**
     * Returns a number of products for fitting.
     *
     * @return int
     */
    public function getNumberOfProductsForFitting(): int
    {
        return $this->numberOfProductsForFitting;
    }

    /**
     * Checks whether the cover is used.
     *
     * @return bool
     */
    public function isCoverUsed(): bool
    {
        return $this->stateOfCover;
    }

    /**
     * Returns formula to calculate a cover width.
     *
     * @return string|null
     */
    public function getFormulaOfCoverWidth(): ?string
    {
        return $this->formulaOfCoverWidth;
    }

    /**
     * Returns a cover width.
     *
     * @return int|null
     */
    public function getCoverWidth(): ?int
    {
        return $this->coverWidth;
    }

    /**
     * Returns formula to calculate a cover height.
     *
     * @return string|null
     */
    public function getFormulaOfCoverHeight(): ?string
    {
        return $this->formulaOfCoverHeight;
    }

    /**
     * Returns a cover height.
     *
     * @return int|null
     */
    public function getCoverHeight(): ?int
    {
        return $this->coverHeight;
    }

    /**
     * Checks whether the cover data are correct.
     *
     * @return bool
     */
    public function isCoverCorrect(): bool
    {
        $areSet = isset($this->formulaOfCoverHeight, $this->formulaOfCoverWidth);

        return !$this->stateOfCover || ($this->stateOfCover && $areSet);
    }

    /**
     * Checks whether the substrate is used.
     *
     * @return bool
     */
    public function isSubstrateUsed(): bool
    {
        return $this->stateOfSubstrate;
    }

    /**
     * Returns formula to calculate a substrate width.
     *
     * @return string|null
     */
    public function getFormulaOfSubstrateWidth(): ?string
    {
        return $this->formulaOfSubstrateWidth;
    }

    /**
     * Returns a substrate width.
     *
     * @return int|null
     */
    public function getSubstrateWidth(): ?int
    {
        return $this->substrateWidth;
    }

    /**
     * Returns formula to calculate a substrate height.
     *
     * @return string|null
     */
    public function getFormulaOfSubstrateHeight(): ?string
    {
        return $this->formulaOfSubstrateHeight;
    }

    /**
     * Returns a substrate height.
     *
     * @return int|null
     */
    public function getSubstrateHeight(): ?int
    {
        return $this->substrateHeight;
    }

    /**
     * Checks whether the substrate data are correct.
     *
     * @return bool
     */
    public function isSubstrateCorrect(): bool
    {
        $areSet = isset($this->formulaOfSubstrateHeight, $this->formulaOfSubstrateWidth);

        return !$this->stateOfSubstrate || ($this->stateOfSubstrate && $areSet);
    }

    /**
     * Checks whether the block is used.
     *
     * @return bool
     */
    public function isBlockUsed(): bool
    {
        return $this->stateOfBlock;
    }

    /**
     * Returns formulat to calculate a block width.
     *
     * @return string|null
     */
    public function getFormulaOfBlockWidth(): ?string
    {
        return $this->formulaOfBlockWidth;
    }

    /**
     * Returns a block width.
     *
     * @return int|null
     */
    public function getBlockWidth(): ?int
    {
        return $this->blockWidth;
    }

    /**
     * Returns formula to calculate a block height.
     *
     * @return string|null
     */
    public function getFormulaOfBlockHeight(): ?string
    {
        return $this->formulaOfBlockHeight;
    }

    /**
     * Returns a block height.
     *
     * @return int|null
     */
    public function getBlockHeight(): ?int
    {
        return $this->blockHeight;
    }

    /**
     * Returns a min number of pages.
     *
     * @return int
     */
    public function getMinNumberOfPages(): int
    {
        return $this->minNumberOfPages;
    }

    /**
     * Returns a max number of pages.
     *
     * @return int|null
     */
    public function getMaxNumberOfPages(): ?int
    {
        return $this->maxNumberOfPages;
    }

    /**
     * Returns frequency of pages.
     *
     * @return int
     */
    public function getFrequencyOfPages(): int
    {
        return $this->frequencyOfPages;
    }

    /**
     * Returns formula to calculate a number of copubooks.
     *
     * @return string|null
     */
    public function getFormulaOfNumberOfCopybooks(): ?string
    {
        return $this->formulaOfNumberOfCopybooks;
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
     * Checks whether the block data are correct.
     *
     * @return bool
     */
    public function isBlockCorrect(): bool
    {
        $areSet = isset(
            $this->formulaOfBlockHeight,
            $this->formulaOfBlockWidth,
            $this->frequencyOfPages,
            $this->minNumberOfPages,
            $this->formulaOfNumberOfCopybooks,
        );

        $areCorrect = false;

        if ($areSet) {
            $frequencyOfPagesIsCorrect = is_int($this->frequencyOfPages) && $this->frequencyOfPages > 0;
            $minNumberOfPagesIsCorrect = is_int($this->minNumberOfPages) && $this->minNumberOfPages >= 0;
            $maxNumberOfPagesIsCorrect = !isset($this->maxNumberOfPages)
                || is_int($this->maxNumberOfPages) && $this->maxNumberOfPages >= 0
            ;
            $areCorrect = $frequencyOfPagesIsCorrect && $minNumberOfPagesIsCorrect && $maxNumberOfPagesIsCorrect;
        }

        return !$this->stateOfBlock || ($this->stateOfBlock && $areSet && $areCorrect);
    }

    /**
     * Returns errors of the settings.
     *
     * @return Errors
     */
    public function getErrors(): Errors
    {
        return $this->errors;
    }

    /**
     * Calculates a price by the given edition size.
     *
     * @return Cost|null
     */
    protected function calculatePriceByEditionSize(): ?Cost
    {
        return $this->pricingRule->calculateByEditionSize($this->getCalculatedEditionSize());
    }

    /**
     * Calculates formulae by user variables.
     *
     * @return void
     */
    protected function calculateFormulaeByUserVariables(): void
    {
        $this->calculateCover();
        $this->calculateSubstrate();
        $this->calculateBlock();
    }

    /**
     * Calculates values of the cover by user variables.
     *
     * @return void
     */
    protected function calculateCover(): void
    {
        if (!$this->stateOfCover) {
            return;
        }

        if ($this->formulaOfCoverWidth) {
            $formula = new Formula($this->formulaOfCoverWidth);
            $this->formulaService->setFormula($formula);

            $this->coverWidth = $this->formulaService->calculate();
        }

        if ($this->formulaOfCoverHeight) {
            $formula = new Formula($this->formulaOfCoverHeight);
            $this->formulaService->setFormula($formula);

            $this->coverHeight = $this->formulaService->calculate();
        }
    }

    /**
     * Calculates values of the substrate by user variables.
     *
     * @return void
     */
    protected function calculateSubstrate(): void
    {
        if (!$this->stateOfSubstrate) {
            return;
        }

        if ($this->formulaOfSubstrateWidth) {
            $formula = new Formula($this->formulaOfSubstrateWidth);
            $this->formulaService->setFormula($formula);

            $this->substrateWidth = $this->formulaService->calculate();
        }

        if ($this->formulaOfSubstrateHeight) {
            $formula = new Formula($this->formulaOfSubstrateHeight);
            $this->formulaService->setFormula($formula);

            $this->substrateHeight = $this->formulaService->calculate();
        }
    }

    /**
     * Calculates values of the block by user variables.
     *
     * @return void
     */
    protected function calculateBlock(): void
    {
        if (!$this->stateOfBlock) {
            return;
        }

        if ($this->formulaOfBlockWidth) {
            $formula = new Formula($this->formulaOfBlockWidth);
            $this->formulaService->setFormula($formula);

            $this->blockWidth = $this->formulaService->calculate();
        }

        if ($this->formulaOfBlockHeight) {
            $formula = new Formula($this->formulaOfBlockHeight);
            $this->formulaService->setFormula($formula);

            $this->blockHeight = $this->formulaService->calculate();
        }

        if ($this->formulaOfNumberOfCopybooks) {
            $formula = new Formula($this->formulaOfNumberOfCopybooks);
            $this->formulaService->setFormula($formula);

            $this->numberOfCopybooks = $this->formulaService->calculate();
        }
    }

    /**
     * Returns data of the current instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $pricingRule = [];

        if ($this->typeOfPricingRule && isset($this->pricingRule)) {
            $pricingRule[$this->typeOfPricingRule] = $this->pricingRule->toArray();
        }

        return [
            'name' => $this->name,
            'price' => [
                'type' => $this->typeOfPricingRule,
                'pricing_rule' => $pricingRule,
                'products_for_fitting' => $this->numberOfProductsForFitting,
            ],
            'cover' => [
                'active' => $this->stateOfCover,
                'formula_of_width' => $this->formulaOfCoverWidth,
                'formula_of_height' => $this->formulaOfCoverHeight,
            ],
            'substrate' => [
                'active' => $this->stateOfSubstrate,
                'formula_of_width' => $this->formulaOfSubstrateWidth,
                'formula_of_height' => $this->formulaOfSubstrateHeight,
            ],
            'block' => [
                'active' => $this->stateOfBlock,
                'formula_of_width' => $this->formulaOfBlockWidth,
                'formula_of_height' => $this->formulaOfBlockHeight,
                'min_number_of_pages' => $this->minNumberOfPages,
                'max_number_of_pages' => $this->maxNumberOfPages,
                'frequency_of_pages' => $this->frequencyOfPages,
                'number_of_copybooks' => $this->formulaOfNumberOfCopybooks,
            ],
        ];
    }
}
