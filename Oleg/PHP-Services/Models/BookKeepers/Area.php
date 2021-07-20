<?php

namespace App\Model\Calculator\BookKeepers;

use App\Services\Calculators\Blocks\BlockCollection;
use App\Model\Calculator\CalculatorSetting;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRule;
use App\Model\Calculator\OptionCollection;
use App\Model\Calculator\PricingRulesOfAssemblies\SheetKeeper;
use App\Model\Calculator\PricingRulesOfAssemblies\Values\ValuesOfArea;
use App\Model\Calculator\PricingRulesOfAssemblies\WidePrintedSheets;
use App\Services\Calculators\Blocks\UserBlocks;
use App\Model\Calculator\CalculationVariables\VariableSettings\SidesVariableSettings;
use App\Model\Calculator\PricingRulesOfAssemblies\AbstractPrintedSheets;
use App\Model\Calculator\PricingRulesOfAssemblies\ValueKeeper;
use App\Services\Calculators\Error;
use App\Services\Calculators\EstimateGroup;
use App\Support\Translation;

/**
 * Calculates a price by area of the material.
 *
 * Калькулятор с расчетом по площади.
 */
class Area extends BookKeeperOfWideSheets
{
    /**
     * Returns types of individual settings of the calculator.
     *
     * @return array|string[]
     */
    public function getTypesOfIndividualSettings(): array
    {
        return [
            CalculatorSetting::ALGORITHM_OF_AREA,
            CalculatorSetting::MARKUP_FOR_NARROW_PRODUCT,
            CalculatorSetting::PRINT_BY_PARTS,
        ];
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
                'name' => 'standard',
                'contents' => [
                    'calculationVariables' => [
                        'print_formats',
                        'gluing',
                        'eyelets',
                        'pole_pocket' => [
                            'name' => 'pole_pocket',
                            'type' => 'sides',
                            'variableSettings' => SidesVariableSettings::createFromArray([
                                 'label' => 'Карман',
                             ]),
                        ],
                        'edge_reinforcement' => [
                            'name' => 'edge_reinforcement',
                            'type' => 'sides',
                            'variableSettings' => SidesVariableSettings::createFromArray([
                                 'label' => 'Усиление края',
                             ]),
                        ],
                        'number_of_products' => [
                            'name' => 'number_of_products',
                            'type' => 'number_of_products',
                            'position' => 'bottom',
                        ],
                    ],
                    'nameOfAttributeGroup' => 'standard',
                ]
            ],
        ]);
    }

    /**
     * Initializes and returns a new ValueKeeper with values for the calculation.
     *
     * @param  UserBlocks $userBlocks
     * @return ValuesOfArea|null
     */
    public function createValueKeeper(UserBlocks $userBlocks): ?ValueKeeper
    {
        $algorithmOfArea = $this->getIndividualSettings()->getByName(CalculatorSetting::ALGORITHM_OF_AREA);
        $printByParts = $this->getIndividualSettings()->getByName(CalculatorSetting::PRINT_BY_PARTS);
        $markupForNarrowProduct = $this->getIndividualSettings()->getByName(CalculatorSetting::MARKUP_FOR_NARROW_PRODUCT);

        $valueKeeper = new ValuesOfArea($userBlocks, $algorithmOfArea, $printByParts, $markupForNarrowProduct);

        if ($valueKeeper->isGluingUsed() && $valueKeeper->getPriceOfGluing() === null) {
            $this->errors->add(trans('calculator_errors.undefined_price_of_gluing'), Error::CALCULATION_ERROR);
            $valueKeeper = null;
        } elseif ($valueKeeper->isPolePocketUsed() && $valueKeeper->getPriceOfPolePocket() === null) {
            $this->errors->add(trans('calculator_errors.undefined_price_of_pole_pocket'), Error::CALCULATION_ERROR);
            $valueKeeper = null;
        } elseif ($valueKeeper->isEdgeReinforcementUsed() && $valueKeeper->getPriceOfEdgeReinforcement() === null) {
            $this->errors->add(trans('calculator_errors.undefined_price_of_edge_reinforcement'), Error::CALCULATION_ERROR);
            $valueKeeper = null;
        } elseif ($valueKeeper->areEyeletsUsed() && $valueKeeper->getPriceOfEyelets() === null) {
            $this->errors->add(trans('calculator_errors.undefined_price_of_eyelets'), Error::CALCULATION_ERROR);
            $valueKeeper = null;
        }

        return $valueKeeper;
    }

    /**
     * Prepares the given pricing rule: loads sheets, filter printed sheets by
     * options with printed sheets, etc.
     *
     * @param  WidePrintedSheets $pricingRule
     * @param  OptionCollection $options
     * @param  ValuesOfArea $valueKeeper
     * @return bool
     */
    public function preparePricingRule(
        PricingRule $pricingRule,
        OptionCollection $options,
        ValueKeeper $valueKeeper
    ): bool {
        if (!parent::preparePricingRule($pricingRule, $options, $valueKeeper)) {
            return false;
        }

        // Filters sheets by the product size if the calculator does not use
        // the print by parts.
        if (!$valueKeeper->doesUsePrintByParts()) {
            $pricingRule->filterByProductSize($valueKeeper->getHeightOfProduct(), $valueKeeper->getWidthOfProduct());
        }

        if ($pricingRule->areReady(false) === SheetKeeper::NO_SHEETS) {
            return $this->appropriatePrintedSheetsNotFound();
        }

        return true;
    }

    /**
     * Pushes other estimate groups by the given ValueKeeper.
     *
     * @param  ValuesOfArea $valueKeeper
     * @return void
     */
    public function pushEstimateGroupsByValueKeeper(ValueKeeper $valueKeeper): void
    {
        $this->pushEstimateGroupOfIndividualSettings($valueKeeper);

        if ($valueKeeper->isGluingUsed()) {
            $estimateOfGluing = $this->createEstimateGroupOfGluing($valueKeeper);
            $this->estimate->pushGroup($estimateOfGluing);
        }

        if ($valueKeeper->isPolePocketUsed()) {
            $estimateOfPolePocket = $this->createEstimateGroupOfPolePocket($valueKeeper);
            $this->estimate->pushGroup($estimateOfPolePocket);
        }

        if ($valueKeeper->isEdgeReinforcementUsed()) {
            $estimateOfEdgeReinforcement = $this->createEstimateGroupOfEdgeReinforcement($valueKeeper);
            $this->estimate->pushGroup($estimateOfEdgeReinforcement);
        }

        if ($valueKeeper->areEyeletsUsed()) {
            $estimateOfEyelets = $this->createEstimateGroupOfEyelets($valueKeeper);
            $this->estimate->pushGroup($estimateOfEyelets);
        }
    }

    /**
     * Creates an estimate group of the gluing.
     *
     * @param  ValuesOfArea $values
     * @return EstimateGroup
     */
    public function createEstimateGroupOfGluing(ValuesOfArea $values): EstimateGroup
    {
        $estimateGroup = new EstimateGroup('gluing');
        $estimateGroup->add('length_of_gluing', $values->getLengthOfGluing());
        $estimateGroup->add('total_length_of_gluing', $values->getTotalLengthOfGluing());
        $estimateGroup->add('price', $values->getPriceOfGluing() ?? 0);

        return $estimateGroup;
    }

    /**
     * Creates an estimate group of the pole pocket.
     *
     * @param  ValuesOfArea $values
     * @return EstimateGroup
     */
    public function createEstimateGroupOfPolePocket(ValuesOfArea $values): EstimateGroup
    {
        $estimateGroup = new EstimateGroup('pole_pocket');
        $estimateGroup->add('length_of_sides', $values->getLengthOfPolePocket());
        $estimateGroup->add('total_length', $values->getTotalLengthOfPolePocket());
        $translations = Translation::getTranslations('estimate', $values->getSidesOfPolePocket());
        $sides = implode(', ', $translations);
        $estimateGroup->add('sides', mb_strtolower($sides));
        $estimateGroup->add('price', $values->getPriceOfPolePocket() ?? 0);

        return $estimateGroup;
    }

    /**
     * Creates an estimate group of the edge reinforcement.
     *
     * @param  ValuesOfArea $values
     * @return EstimateGroup
     */
    public function createEstimateGroupOfEdgeReinforcement(ValuesOfArea $values): EstimateGroup
    {
        $estimateGroup = new EstimateGroup('edge_reinforcement');
        $estimateGroup->add('length_of_sides', $values->getLengthOfEdgeReinforcement());
        $estimateGroup->add('total_length', $values->getTotalLengthOfEdgeReinforcement());
        $translations = Translation::getTranslations('estimate', $values->getSidesOfEdgeReinforcement());
        $sides = implode(', ', $translations);
        $estimateGroup->add('sides', mb_strtolower($sides));
        $estimateGroup->add('price', $values->getPriceOfEdgeReinforcement() ?? 0);

        return $estimateGroup;
    }

    /**
     * Creates an estimate group of the eyeltes.
     *
     * @param  ValuesOfArea $values
     * @return EstimateGroup
     */
    public function createEstimateGroupOfEyelets(ValuesOfArea $values): EstimateGroup
    {
        $estimateGroup = new EstimateGroup('eyelets');
        $estimateGroup->add('number_of_eyelets', $values->getNumberOfEyelets());
        $estimateGroup->add('total_number_of_eyelets', $values->getTotalNumberOfEyelets());
        $translations = Translation::getTranslations('estimate', $values->getPositionsOfPointsOfEyelets());
        $positions = implode(', ', $translations);
        $estimateGroup->add('positions', mb_strtolower($positions));
        $estimateGroup->add('price', $values->getPriceOfPolePocket() ?? 0);

        return $estimateGroup;
    }

    /**
     * Pushes the estimate of individual settings.
     *
     * @param  ValuesOfArea $values
     * @return void
     */
    public function pushEstimateGroupOfIndividualSettings(ValuesOfArea $values): void
    {
        $estimateGroup = new EstimateGroup('individual_settings');
        $estimateGroup->add('print_by_parts', $values->doesUsePrintByParts(), true);
        $estimateGroup->add('coefficient_for_narrow_product', $values->getCoefficientForNarrowProduct(), true);
        $this->estimate->pushGroup($estimateGroup);
    }

    /**
     * Pushes an estimate group of the given pricing rule.
     *
     * @param  AbstractPrintedSheets $pricingRule
     * @param  ValuesOfArea $valueKeeper
     * @param  string $estimateName
     * @return void
     */
    public function pushEstimateGroupOfPricingRule(
        PricingRule $pricingRule,
        ValueKeeper $valueKeeper,
        string $estimateName = 'assembly'
    ): void {
        $group = $pricingRule->getEstimateGroup();
        $group->setName($estimateName);
        $group->add('area_of_product', $valueKeeper->getAreaOfProduct(), true);
        $group->add('total_area_of_products', $valueKeeper->getTotalAreaOfProducts(), true);

        // Uses the coefficient when the product is small.
        if ($valueKeeper->doesUseCoefficientForNarrowProduct() && $valueKeeper->isProductSmall()) {
            /** @var float $price */
            $price = $group->get('price')->getValue();
            $group->add('coefficient_of_narrow_product_was_used', true, true);
            $group->add('price_without_coefficient_for_narrow_product', $price, true);
            $priceWithCoefficient = $valueKeeper->calculatePriceWithCoefficientForNarrowProduct($price);
            $group->add('price', $priceWithCoefficient);
        } else {
            $group->add('coefficient_of_narrow_product_was_used', false, true);
        }

        $this->estimate->pushGroup($group);
    }

    /**
     * Calculates a price by estimate groups of the estimate.
     *
     * @return float|null
     */
    public function calculatePriceOfEstimateByEstimateGroups(): ?float
    {
        $price = parent::calculatePriceOfEstimateByEstimateGroups();

        if (!isset($price)) {
            return null;
        }

        $price += $this->getPriceOfEstimateGroup('gluing');
        $price += $this->getPriceOfEstimateGroup('eyelets');
        $price += $this->getPriceOfEstimateGroup('pole_pocket');
        $price += $this->getPriceOfEstimateGroup('edge_reinforcement');

        return $price;
    }
}
