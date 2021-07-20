<?php

namespace App\Model\Calculator\BookKeepers;

use App\Services\Calculators\Estimate;
use App\Services\Calculators\Errors;
use App\Model\Calculator\OptionCollection;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRule;
use App\Model\Calculator\Settings\SettingEntityCollection;
use App\Services\Calculators\Blocks\CalculationVariableOfContentsCollection;
use App\Services\Calculators\CalculationVariableKeeper;
use App\Services\Calculators\IndividualSettingsService;

/**
 * Implements the same methods of each BookKeeper.
 */
abstract class BaseBookKeeper implements BookKeeper
{
    /**
     * An instance of IndividualSettingsService.
     *
     * @var IndividualSettingsService|null
     */
    protected $individualSettingsService;

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
     */
    public function __construct()
    {
        $this->estimate = new Estimate();
        $this->errors = new Errors();
    }

    /**
     * Returns entities of individual settings of the calculator.
     *
     * @return SettingEntityCollection|null
     */
    public function getIndividualSettings(): ?SettingEntityCollection
    {
        return $this->individualSettingsService
            ? $this->individualSettingsService->getEntitiesOfSettings()
            : null
        ;
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
     * Returns groups of attributes.
     *
     * @return array|string[]
     */
    public function getGroupsOfAttributes(): array
    {
        return $this->getBlocks()->getNamesOfAttributeGroups();
    }

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
    ): bool {
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
