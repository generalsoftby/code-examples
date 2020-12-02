<?php

namespace App\Model\Calculator\BookKeepers;

use App\Model\Calculator\CalculationVariables\VariableSettings\NumberOfProductsVariableSettings;
use App\Model\Calculator\Calculator;
use App\Model\Calculator\CalculatorAssembly as Assembly;
use App\Model\Calculator\CalculatorPrintedSheet;
use App\Model\Calculator\OptionCollection;
use App\Model\Calculator\PricingRulesOfAssemblies\AbstractPrintedSheets;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRule;
use App\Model\Calculator\PricingRulesOfAssemblies\PrintedSheets;
use App\Model\Calculator\PricingRulesOfAssemblies\ValueKeeper;
use App\Model\Calculator\PricingRulesOfAssemblies\Values\ValuesOfMultipleProduct;
use App\Model\Calculator\PricingRulesOfAssemblies\Values\ValuesOfPrintedSheet;
use App\Services\Calculators\Blocks\BlockCollection;
use App\Services\Calculators\Blocks\ComponentConfigurations\PrintFormatsConfiguration;
use App\Services\Calculators\Blocks\UserBlocks;
use App\Services\Calculators\Error;
use App\Services\Calculators\EstimateGroup;
use App\Services\Formula\VariableCollection;

/**
 * The class calculates a price for multiple products.
 *
 * Калькулятор "многополосной продукции" или "многополосный".
 */
class MultipleProduct extends BookKeeperWithMultipleAssemblies
{
    use ThrowingExceptionOfInvalidPricingRule;
    use CalculationOfPrintedSheets;

    /**
     * A current calculator.
     *
     * @var Calculator
     */
    protected $calculator;

    /**
     * Initializes an instance of the class.
     *
     * @param Calculator $calculator
     */
    public function __construct(Calculator $calculator)
    {
        parent::__construct();

        $this->calculator = $calculator;
    }

    /**
     * Returns types of individual settings of the calculator.
     *
     * @return array|string[]
     */
    public function getTypesOfIndividualSettings(): array
    {
        return [];
    }

    /**
     * Returns blocks to configure the frontend.
     *
     * @return BlockCollection
     */
    public function getBlocks(): BlockCollection
    {
        return BlockCollection::createFromArray([
            [
                // Базовый блок. Управляет видимостью других блоков.
                'name' => 'basic',
                'contents' => [
                    'calculationVariables' => [
                        'stitching_type',
                        'print_formats' => [
                            'name' => 'print_formats',
                            'type' => 'print_formats',
                            'configuration' => new PrintFormatsConfiguration(
                                'Формат изделия в готовом виде'
                            ),
                        ],
                        'number_of_pages_in_block' => [
                            'name' => 'number_of_pages_in_block',
                            'type' => 'number_of_products',
                            'visibleSettings' => false,
                            'variableSettings' => NumberOfProductsVariableSettings::createFromArray([
                                'label' => 'Страниц в блоке',
                            ]),
                            'position' => 'bottom',
                            'modifyConfigBy' => [
                                [
                                    // Разрешается модификация элемента с помощью
                                    // настроек значения ПР "Способ брошюровки".
                                    // Она передает необходимые значения
                                    // в указанную ПР.
                                    'blockName' => 'basic',
                                    'componentType' => 'calculationVariables',
                                    'elementName' => 'stitching_type',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                // Блок "Обложки". Параметры обложки.
                'name' => 'cover',
                'visualizeBy' => [
                    [
                        // Визуализируется с помощью переменной расчета
                        // базового блока "Способ брошюровки".
                        'blockName' => 'basic',
                        'componentType' => 'calculationVariables',
                        'elementName' => 'stitching_type',
                    ],
                ],
                'visible' => false,
                'contents' => [
                    'calculationVariables' => [
                        'print_formats' => [
                            'name' => 'print_formats_of_cover',
                            'type' => 'print_formats',
                            'visibleSettings' => false,
                            'importSettingsFrom' => 'print_formats',
                            'modifyValuesBy' => [
                                [
                                    // Разрешается модификация элемента с помощью
                                    // выбранного значения ПР "Формат печати".
                                    // При изменении ПР "Формат печати" из
                                    // основного блока должны быть изменены
                                    // значения в других блоках до первого ручного
                                    // изменения значения.
                                    // В отличие от "Способа брошюровки" и
                                    // "Количество листов", задается значение,
                                    // а не настройки.
                                    'blockName' => 'basic',
                                    'componentType' => 'calculationVariables',
                                    'elementName' => 'print_formats',
                                ],
                            ],
                            'configuration' => new PrintFormatsConfiguration(
                                'Формат обложки в готовом виде',
                                PrintFormatsConfiguration::UPDATE_UNIT_FIRST_MANUAL_CHANGE
                            ),
                        ],
                    ],
                    'nameOfAttributeGroup' => 'cover',
                ]
            ],
            [
                // Блок "Подложки". Параметры подложки.
                'name' => 'substrate',
                'visible' => false,
                'visualizeBy' => [
                    [
                        // Визуализируется с помощью переменной расчета
                        // базового блока "Способ брошюровки".
                        'blockName' => 'basic',
                        'componentType' => 'calculationVariables',
                        'elementName' => 'stitching_type',
                    ],
                ],
                'contents' => [
                    'calculationVariables' => [
                        // for the substrate
                        'print_formats' => [
                            'name' => 'print_formats_of_substrate',
                            'type' => 'print_formats',
                            'visibleSettings' => false,
                            'importSettingsFrom' => 'print_formats',
                            'modifyValuesBy' => [
                                [
                                    'blockName' => 'basic',
                                    'componentType' => 'calculationVariables',
                                    'elementName' => 'print_formats',
                                ],
                            ],
                            'configuration' => new PrintFormatsConfiguration(
                                'Формат подложки в готовом виде',
                                PrintFormatsConfiguration::UPDATE_UNIT_FIRST_MANUAL_CHANGE
                            ),
                        ],
                    ],
                    'nameOfAttributeGroup' => 'substrate',
                ],
            ],
            [
                // Блок "Блок". Параметры блока.
                'name' => 'block',
                'visible' => true,
                'contents' => [
                    'calculationVariables' => [
                        'print_formats' => [
                            'name' => 'print_formats_of_block',
                            'type' => 'print_formats',
                            'visibleSettings' => false,
                            'importSettingsFrom' => 'print_formats',
                            'modifyValuesBy' => [
                                [
                                    'blockName' => 'basic',
                                    'componentType' => 'calculationVariables',
                                    'elementName' => 'print_formats',
                                ],
                            ],
                            'configuration' => new PrintFormatsConfiguration(
                                'Формат листов в готовом виде',
                                PrintFormatsConfiguration::UPDATE_UNIT_FIRST_MANUAL_CHANGE
                            ),
                        ],
                    ],
                    'nameOfAttributeGroup' => 'block',
                ],
            ],
            [
                // Блок "Размер издания". Описывает Тираж.
                'name' => 'edition_size',
                'contents' => [
                    'calculationVariables' => [
                        'number_of_products', // Тираж
                    ],
                ],
            ],
        ]);
    }

    /**
     * Returns an array with types of pricing rules.
     *
     * @return array|string[]
     */
    public function getTypesOfPricingRules(): array
    {
        return [
            Assembly::COPY_TYPE,
            Assembly::PRINTED_SHEETS_TYPE,
        ];
    }

    /**
     * Returns an array with types of printed sheets.
     *
     * @return array|string[]
     */
    public function getTypesOfPrintedSheets(): array
    {
        return [CalculatorPrintedSheet::SHEETS];
    }

    /**
     * Creates and returns values from the given user blocks
     * in the form of ValueKeeper.
     *
     * @param  UserBlocks $userBlocks
     * @return ValuesOfMultipleProduct|null
     */
    public function createValueKeeper(UserBlocks $userBlocks): ?ValueKeeper
    {
        $values = new ValuesOfMultipleProduct($userBlocks);

        if (!$values->areRequiredValuesDefined()) {
            $this->errors->add(
                trans('calculator_errors.required_values_are_not_defined'),
                Error::VARIABLE_OF_CALCULATION_ERROR
            );
        }

        if (!$values->hasStitchingWay()) {
            $this->errors->add(
                trans('calculator_errors.stitching_way_not_defined'),
                Error::VARIABLE_OF_CALCULATION_ERROR
            );
            $values = null;
        } elseif (!$values->getStitchingWay()->validate()) {
            $this->errors->unite($values->getStitchingWay()->getErrors());
            $values = null;
        } elseif (!$values->getPriceOfStitchingWay()) {
            $this->errors->add(
                trans('calculator_errors.price_of_stitching_type_is_null', [
                    'name' => $values->getStitchingWay()->getName(),
                ]),
                Error::VARIABLE_OF_CALCULATION_ERROR
            );
            $values = null;
        } elseif ($values->isCoverUsed() && !$values->getHeightOfCover()) {
            $this->errors->add(
                trans('calculator_errors.cover_height_of_stitching_way_is_null', [
                    'name' => $values->getStitchingWay()->getName(),
                ]),
                Error::VARIABLE_OF_CALCULATION_ERROR
            );
            $values = null;
        } elseif ($values->isCoverUsed() && !$values->getWidthOfCover()) {
            $this->errors->add(
                trans('calculator_errors.cover_width_of_stitching_way_is_null', [
                    'name' => $values->getStitchingWay()->getName(),
                ]),
                Error::VARIABLE_OF_CALCULATION_ERROR
            );
            $values = null;
        } elseif ($values->isSubstrateUsed() && !$values->getHeightOfSubstrate()) {
            $this->errors->add(
                trans('calculator_errors.substrate_height_of_stitching_way_is_null', [
                    'name' => $values->getStitchingWay()->getName(),
                ]),
                Error::VARIABLE_OF_CALCULATION_ERROR
            );
            $values = null;
        } elseif ($values->isSubstrateUsed() && !$values->getWidthOfSubstrate()) {
            $this->errors->add(
                trans('calculator_errors.substrate_width_of_stitching_way_is_null', [
                    'name' => $values->getStitchingWay()->getName(),
                ]),
                Error::VARIABLE_OF_CALCULATION_ERROR
            );
            $values = null;
        } elseif ($values->isBlockUsed() && !$values->getHeightOfBlock()) {
            $this->errors->add(
                trans('calculator_errors.block_height_of_stitching_way_is_null', [
                    'name' => $values->getStitchingWay()->getName(),
                ]),
                Error::VARIABLE_OF_CALCULATION_ERROR
            );
            $values = null;
        } elseif ($values->isBlockUsed() && !$values->getWidthOfBlock()) {
            $this->errors->add(
                trans('calculator_errors.block_width_of_stitching_way_is_null', [
                    'name' => $values->getStitchingWay()->getName(),
                ]),
                Error::VARIABLE_OF_CALCULATION_ERROR
            );
            $values = null;
        } elseif ($values->isBlockUsed() && !$values->getNumberOfCopybooks()) {
            $this->errors->add(
                trans('calculator_errors.number_of_copybooks_of_stitching_way_is_null', [
                    'name' => $values->getStitchingWay()->getName(),
                ]),
                Error::VARIABLE_OF_CALCULATION_ERROR
            );
            $values = null;
        }

        return $values;
    }

    /**
     * Checks whether the given PricingRule to calculate a price of products.
     *
     * @param  PricingRule $pricingRule
     * @param  string      $blockName
     * @param  string      $assemblyTitle
     * @return bool
     */
    public function checkPricingRule(
        PricingRule $pricingRule,
        string $blockName,
        string $nameOfPricingRule
    ): bool {
        $replacements = [
            'block_name' => $blockName,
            'name_of_pricing_rule' => $nameOfPricingRule,
        ];

        if (!($pricingRule instanceof PrintedSheets)) {
            $this->throwInvalidPricingRule($pricingRule, [PrintedSheets::class]);
            $this->errors->add(
                trans('calculator_errors.pricing_rule_of_block_is_invalid', $replacements),
                Error::SYSTEM_ERROR
            );
            return false;
        }

        return $this->checkPricingRuleOfPrintedSheets($pricingRule, $blockName, $nameOfPricingRule);
    }

    /**
     * Prepares the given pricing rule of a block: loads data, filter data, etc.
     *
     * @param  AbstractPrintedSheets $pricingRule
     * @param  OptionCollection $options
     * @param  ValueKeeper $valueKeeper
     * @param  string $blockName
     * @return bool
     */
    public function preparePricingRule(
        PricingRule $pricingRule,
        OptionCollection $options,
        ValueKeeper $valueKeeper,
        string $blockName
    ): bool {
        return $this->preparePricingRuleOfPrintedSheets($pricingRule, $options, $valueKeeper, $blockName);
    }

    /**
     * Makes an unstandard estimate in the form of an array.
     * Adds an printed sheet from the given ValueKeeper.
     *
     * @param  float $price
     * @param  float $pricePerProduct
     * @param  ValuesOfPrintedSheet $valueKeeper
     * @return array
     */
    public function makeUnstandardEstimate(float $price, float $pricePerProduct, ValueKeeper $valueKeeper): array
    {
        $unstandardEstimate = parent::makeUnstandardEstimate($price, $pricePerProduct, $valueKeeper);

        return $this->addPrintedSheetToUnstandardEstimate($unstandardEstimate, $valueKeeper);
    }

    /**
     * Prepares values for formulae of options.
     *
     * @param  ValuesOfMultipleProduct $valueKeeper
     * @return VariableCollection
     */
    public function createFormulaVariablesByValueKeeper(ValueKeeper $valueKeeper): VariableCollection
    {
        $variables = new VariableCollection();
        $variables->add('number_of_products', '{Тираж}', $valueKeeper->getOriginalNumberOfProducts());
        $variables->add('product_height', '{Высота}', $valueKeeper->getProductHeight());
        $variables->add('product_width', '{Ширина}', $valueKeeper->getProductWidth());
        $variables->add(
            'number_of_printed_sheets', '{кол-во печатных листов в тираже}',
            $valueKeeper->getNumberOfPrintedSheets()
        );
        $variables->add(
            'number_of_products_on_sheet', '{Количество изделий на листе}',
            $valueKeeper->getNumberOfProductsOnSheet()
        );
        $variables->add(
            'number_of_printed_sheets_for_fitting', '{кол-во печатных листов в приладке}',
            $valueKeeper->getNumberOfSheetsForFitting()
        );

        return $variables;
    }

    /**
     * Calculates a price by estimate groups of the estimate.
     *
     * @param  UserBlocks $userBlocks
     * @return float|null
     */
    public function calculatePriceOfEstimateByEstimateGroups(UserBlocks $userBlocks): ?float
    {
        if ($this->errors->count()) {
            return null;
        }

        $namesOfBlocksWithOptions = $userBlocks->getNamesOfVisibleBlocksWithAssembly();

        $price = $this->getPriceOfEstimateGroup('stitching_type');
        $price += $this->getPriceOfEstimateGroup('cover');
        $price += $this->getPriceOfEstimateGroup('substrate');
        $price += $this->getPriceOfEstimateGroup('block');
        $price += $this->calculatePriceOfOptions($namesOfBlocksWithOptions);

        return $price;
    }

    /**
     * Pushes the default estimate group by the given ValueKeeper.
     *
     * @param  ValueKeeper|ValuesOfMultipleProduct $valueKeeper
     * @return void
     */
    public function pushDefaultEstimateGroupByValueKeeper(ValueKeeper $valueKeeper): void
    {
        if ($valueKeeper instanceof ValuesOfMultipleProduct) {
            $this->pushDefaultEstimateGroupByValueKeeperOfMultipleProduct($valueKeeper);
        }
    }

    /**
     * Pushes other estimate groups by the given ValueKeeper.
     *
     * @param  ValueKeeper $valueKeeper
     * @return void
     */
    public function pushEstimateGroupsByValueKeeper(ValueKeeper $valueKeeper): void
    {
        $estimateOfStitchingType = $this->createEstimateGroupOfStitchingType($valueKeeper);
        $this->estimate->pushGroup($estimateOfStitchingType);
    }

    /**
     * Creates an estimate group of the stitching type by the given value.
     *
     * @param  ValuesOfMultipleProduct $values
     * @return EstimateGroup
     */
    public function createEstimateGroupOfStitchingType(ValuesOfMultipleProduct $values): EstimateGroup
    {
        $estimateGroup = new EstimateGroup('stitching_type');
        $estimateGroup->add('name', $values->getStitchingWay()->getName());
        $estimateGroup->add('price', $values->getPriceOfStitchingWay());
        $estimateGroup->add('price_per_product', $values->getPriceOfStitchingWayPerProduct(), true);
        $estimateGroup->add('number_of_products', $values->getOriginalNumberOfProducts(), true);
        $estimateGroup->add(
            'number_of_products_with_products_for_fitting',
            $values->getStitchingWay()->getCalculatedEditionSize(),
            true
        );
        $estimateGroup->add('product_height', $values->getHeightOfProduct());
        $estimateGroup->add('product_width', $values->getWidthOfProduct());

        if ($values->isCoverUsed()) {
            $estimateGroup->add('cover_height', $values->getHeightOfCover());
            $estimateGroup->add('cover_width', $values->getWidthOfCover());
        }

        if ($values->isSubstrateUsed()) {
            $estimateGroup->add('substrate_height', $values->getHeightOfSubstrate());
            $estimateGroup->add('substrate_width', $values->getWidthOfSubstrate());
        }

        if ($values->isBlockUsed()) {
            $estimateGroup->add('block_height', $values->getHeightOfBlock());
            $estimateGroup->add('block_width', $values->getWidthOfBlock());
            $estimateGroup->add('number_of_copybooks', $values->getNumberOfCopybooks());
        }

        return $estimateGroup;
    }

    /**
     * Pushes the default estimate group by the given ValuesOfMultipleProduct.
     *
     * @param  ValuesOfMultipleProduct $valueKeeper
     * @return void
     */
    public function pushDefaultEstimateGroupByValueKeeperOfMultipleProduct(
        ValuesOfMultipleProduct $valueKeeper
    ): void {
        $group = $this->estimate->getGroup('default');
        $group->add('number_of_products', $valueKeeper->getOriginalNumberOfProducts());
        $group->add('product_height', $valueKeeper->getHeightOfProduct());
        $group->add('product_width', $valueKeeper->getWidthOfProduct());
    }
}
