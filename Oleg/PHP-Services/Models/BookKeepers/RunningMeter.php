<?php

namespace App\Model\Calculator\BookKeepers;

use App\Model\Calculator\OptionCollection;
use App\Model\Calculator\PricingRulesOfAssemblies\WidePrintedSheets;
use App\Model\Calculator\PricingRulesOfAssemblies\SheetKeeper;
use App\Model\Calculator\PricingRulesOfAssemblies\Values\ValuesOfRunningMeter;
use App\Model\Calculator\CalculatorSetting;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRule;
use App\Model\Calculator\PricingRulesOfAssemblies\ValueKeeper;
use App\Model\Calculator\PricingRulesOfAssemblies\Values\ValuesOfWideSheets;
use App\Services\Calculators\Blocks\BlockCollection;
use App\Services\Calculators\Blocks\UserBlocks;

/**
 * Calculates a price of products of wide printed sheets.
 * It for calculators with the type of 'running meter'.
 *
 * Калькулятор "Расчет по погонному метру".
 */
class RunningMeter extends BookKeeperOfWideSheets
{
    /**
     * Returns types of individual settings of the calculator.
     *
     * @return array|string[]
     */
    public function getTypesOfIndividualSettings(): array
    {
        return [CalculatorSetting::ALGORITHM_OF_LENGTH];
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
                // 'visible' => true,
                // 'visualizeBy' => [
                //     [
                //         'blockName' => 'standard',
                //         'componentType' => 'calculationVariables',
                //         'elementName' => 'print_formats',
                //     ],
                // ],
                'contents' => [
                    'calculationVariables' => [
                        'print_formats',
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
     * @return ValuesOfRunningMeter|null
     */
    public function createValueKeeper(UserBlocks $userBlocks): ?ValueKeeper
    {
        $algorithmOfLength = $this->getIndividualSettings()->getByName(CalculatorSetting::ALGORITHM_OF_LENGTH);
        $valueKeeper = new ValuesOfRunningMeter($userBlocks, $algorithmOfLength);

        return $valueKeeper;
    }

    /**
     * Prepares the given pricing rule: loads sheets, filter printed sheets by
     * options with printed sheets, etc.
     *
     * @param  WidePrintedSheets $pricingRule
     * @param  OptionCollection $options
     * @param  ValuesOfWideSheets $valueKeeper
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

        $pricingRule->filterByProductSize($valueKeeper->getHeightOfProduct(), $valueKeeper->getWidthOfProduct());

        if ($pricingRule->areReady(false) === SheetKeeper::NO_SHEETS) {
            return $this->appropriatePrintedSheetsNotFound();
        }

        return true;
    }

    /**
     * Pushes other estimate groups by the given ValueKeeper.
     *
     * @param  ValuesOfRunningMeter $valueKeeper
     * @return void
     */
    public function pushEstimateGroupsByValueKeeper(ValueKeeper $valueKeeper): void
    {
        // No other estimates to add.
    }
}
