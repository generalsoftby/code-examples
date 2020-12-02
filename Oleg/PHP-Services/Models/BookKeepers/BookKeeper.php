<?php

namespace App\Model\Calculator\BookKeepers;

use App\Services\Calculators\Estimate;
use App\Services\Calculators\Errors;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRule;
use App\Model\Calculator\OptionCollection;
use App\Model\Calculator\Settings\SettingEntityCollection;
use App\Services\Calculators\Blocks\BlockCollection;
use App\Services\Calculators\Blocks\CalculationVariableOfContentsCollection;
use App\Services\Calculators\Blocks\UserBlocks;
use App\Services\Calculators\CalculationVariableKeeper;

/**
 * The interface for bookkeepers of calculators.
 */
interface BookKeeper
{
    /**
     * Returns types of individual settings of the calculator.
     *
     * @return array|string[]
     */
    public function getTypesOfIndividualSettings(): array;

    /**
     * Returns entities of individual settings of the calculator.
     *
     * @return SettingEntityCollection|null
     */
    public function getIndividualSettings(): ?SettingEntityCollection;

    /**
     * Returns an instance of CalculationVariableOfContentsCollection of the blocks.
     *
     * @return CalculationVariableOfContentsCollection
     */
    public function getCalculationVariablesOfContents(): CalculationVariableOfContentsCollection;

    /**
     * Returns an array with types of pricing rules.
     *
     * @return array|string[]
     */
    public function getTypesOfPricingRules(): array;

    /**
     * Returns groups of attributes.
     *
     * @return array|string[]
     */
    public function getGroupsOfAttributes(): array;

    /**
     * Returns an array with types of options.
     *
     * TODO: Разработать возврат типов опций после обновления опций. Используется
     * для совмещения выбираемых калькуляторов и опций.
     *
     * @return array|string[]
     */
    // public function getTypesOfOptions(): array;

    /**
     * Returns an array with names of variables of a formula.
     *
     * TODO: Реализовать возврат переменных формул для страниц опций на основе
     * выбранных калькуляторов и типов опций.
     *
     * @return array|string[]
     */
    // public function getVariableNamesOfFormula(): array;

    /**
     * Returns an array with types of printed sheets.
     *
     * @return array|string[]
     */
    public function getTypesOfPrintedSheets(): array;

    /**
     * Returns blocks to configure the frontend.
     *
     * @return BlockCollection
     */
    public function getBlocks(): BlockCollection;

    /**
     * Calculates an estimate for the product.
     * Returns true after successful calculation.
     *
     * @param  PricingRule               $pricingRule               A pricing rule of the assembly.
     * @param  CalculationVariableKeeper $calculationVariableKeeper User values of variables for calculation.
     * @param  OptionCollection          $options                   Options with values for the calculation.
     *
     * @deprecated v1.7
     */
    public function calculate(
        PricingRule $pricingRule,
        CalculationVariableKeeper $calculationVariableKeeper,
        OptionCollection $options
    ): bool;

    /**
     * Calculates prices by the given user blocks with user data.
     * Returns true after a successful calculation.
     *
     * @param  UserBlocks $userBlocks
     * @return bool
     */
    public function calculateByBlocks(UserBlocks $userBlocks): bool;

    /**
     * Returns an Estimate of the calculation.
     *
     * @return Estimate
     */
    public function getEstimate(): Estimate;

    /**
     * Returns an Errors of the calculation.
     *
     * @return Errors
     */
    public function getErrors(): Errors;
}
