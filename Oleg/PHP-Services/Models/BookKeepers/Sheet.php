<?php

namespace App\Model\Calculator\BookKeepers;

use App\Services\Calculators\Estimate;
use App\Services\Calculators\Errors;
use App\Model\Calculator\Calculator;
use App\Model\Calculator\CalculatorAssembly as Assembly;
use App\Model\Calculator\CalculatorPrintedSheet;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRule;
use App\Model\Calculator\OptionCollection;
use App\Model\Calculator\Settings\SettingEntityCollection;
use App\Services\Calculators\Blocks\BlockCollection;
use App\Services\Calculators\Blocks\CalculationVariableOfContentsCollection;
use App\Services\Calculators\Blocks\UserBlocks;
use App\Services\Calculators\CalculationVariableKeeper;

/**
 * The class for calculator using a formula with sheet type.
 *
 * WARNING: Этот класс на момент версии 1.2 (1.3 в разработке)
 * является переходным. Сейчас он используется для получения списка разрешенных
 * переменных расчета, но в последующем должен заменить класс NewSheetCalculator.
 *
 * Листовой калькулятор.
 */
class Sheet implements BookKeeper
{
    /**
     * The current calculator.
     *
     * @var Calculator
     */
    protected $calculator;

    /**
     * An instance of Estimate.
     *
     * @var Estimate
     */
    protected $estimate;

    /**
     * An instance of Errors.
     *
     * @var Errors
     */
    protected $errors;

    /**
     * Initializes an instance of the class.
     *
     * @param Calculator $calculator
     */
    public function __construct(Calculator $calculator)
    {
        $this->estimate = new Estimate;
        $this->errors = new Errors;
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
     * Returns entities of individual settings of the calculator.
     *
     * @return SettingEntityCollection|null
     */
    public function getIndividualSettings(): ?SettingEntityCollection
    {
        return null;
    }

    /**
     * Returns an instance of CalculationVariableOfContentsCollection of the blocks.
     *
     * @return CalculationVariableOfContentsCollection
     */
    public function getCalculationVariablesOfContents(): CalculationVariableOfContentsCollection
    {
        return $this->getBlocks()->getCalculationVariablesOfContentsOfGroups();
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
     * Returns groups of attributes.
     *
     * @return array|string[]
     */
    public function getGroupsOfAttributes(): array
    {
        return ['standard'];
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
     * Returns blocks to configure the frontend.
     *
     * @return BlockCollection
     */
    public function getBlocks(): BlockCollection
    {
        // TODO Настроить блоки
        return new BlockCollection();
    }

    /**
     * Calculates an estimate for the product.
     * Returns true after successful calculation.
     *
     * @param  PricingRule               $pricingRule               A pricing rule of the assembly.
     * @param  CalculationVariableKeeper $calculationVariableKeeper User values of variables for calculation.
     * @param  OptionCollection          $options                   Options with values for the calculation.
     */
    public function calculate(
        PricingRule $pricingRule,
        CalculationVariableKeeper $calculationVariableKeeper,
        OptionCollection $options
    ): bool
    {
        // WARNING: Не производит расчет. Класс является переходным. См. описание класса.
        return false;
    }

    /**
     * Calculates prices by the given user blocks with user data.
     * Returns true after a successful calculation.
     *
     * @param  UserBlocks $userBlocks
     * @return bool
     */
    public function calculateByBlocks(UserBlocks $userBlocks): bool
    {
        // WARNING: Не производит расчет. Класс является переходным. См. описание класса.
        return false;
    }

    /**
     * Returns an Estimate of the calculation.
     *
     * @return Estimate
     */
    public function getEstimate(): Estimate
    {
        return $this->estimate;
    }

    /**
     * Returns an Errors of the calculation.
     *
     * @return Errors
     */
    public function getErrors(): Errors
    {
        return $this->errors;
    }
}
