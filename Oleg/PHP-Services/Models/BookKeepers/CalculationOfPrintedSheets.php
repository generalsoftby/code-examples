<?php

namespace App\Model\Calculator\BookKeepers;

use App\Model\Calculator\OptionCollection;
use App\Model\Calculator\PricingRulesOfAssemblies\AbstractPrintedSheets;
use App\Model\Calculator\PricingRulesOfAssemblies\SheetKeeper;
use App\Model\Calculator\PricingRulesOfAssemblies\Values\ValuesOfPrintedSheet;
use App\Services\Calculators\Error;
use App\Services\Calculators\Errors;
use App\Services\Calculators\Estimate;
use App\Services\Formula\VariableCollection;

/**
 * Implementes methods of calculation of printed sheets.
 *
 * @property Estimate $estimate
 * @property Errors $errors
 */
trait CalculationOfPrintedSheets
{
    /**
     * Adds values for formulae of options by the given ValuesOfPrintedSheet.
     *
     * @param  VariableCollection   $variables
     * @param  ValuesOfPrintedSheet $valueKeeper
     * @return VariableCollection
     */
    public function addDataOfPrintedSheetToVariables(
        VariableCollection $variables,
        ValuesOfPrintedSheet $valueKeeper
    ): VariableCollection {
        $variables->add('number_of_products', '{Тираж}', $valueKeeper->getNumberOfProducts());
        $variables->add('product_height', '{Высота}', $valueKeeper->getHeightOfProduct());
        $variables->add('product_width', '{Ширина}', $valueKeeper->getWidthOfProduct());

        return $variables;
    }

    /**
     * Checks the PricingRule of a printed sheet to calculate a price of products.
     *
     * @param  AbstractPrintedSheets $pricingRule
     * @param  string|null $blockName
     * @param  string|null $nameOfPricingRule
     * @return bool
     */
    public function checkPricingRuleOfPrintedSheets(
        AbstractPrintedSheets $pricingRule,
        string $blockName = null,
        string $nameOfPricingRule = null
    ): bool {
        if ($pricingRule->areReady(false) === SheetKeeper::NO_READY_SHEETS) {
            $translation = isset($blockName, $nameOfPricingRule)
                ? 'sheets_in_assembly_of_block_are_not_configured'
                : 'sheets_are_not_configured'
            ;

            $this->errors->add(
                trans('calculator_errors.' . $translation, [
                    'block_name' => trans('calculation_variables.' . $blockName),
                    'name_of_pricing_rule' => $nameOfPricingRule,
                ]), Error::CONFIGURATION_ERROR
            );

            return false;
        }

        return true;
    }

    /**
     * Prepares the given pricing rule: loads sheets, filter printed sheets by
     * options with printed sheets, etc.
     *
     * @param  AbstractPrintedSheets $pricingRule
     * @param  OptionCollection $options
     * @param  ValuesOfPrintedSheet $valueKeeper
     * @param  string $blockName
     * @return bool
     */
    public function preparePricingRuleOfPrintedSheets(
        AbstractPrintedSheets $pricingRule,
        OptionCollection $options,
        ValuesOfPrintedSheet $valuesOfPrintedSheet,
        string $blockName = null
    ): bool {
        $pricingRule->loadPrintedSheets();
        $pricingRule->removeSheetsWithoutPrintedSheet();

        if ($pricingRule->areReady(false) === SheetKeeper::NO_SHEETS) {
            $this->errors->add(trans('calculator_errors.sheets_not_found_in_the_assembly'), Error::CONFIGURATION_ERROR);
            return false;
        }

        $this->defineIntersectionOfPrintedSheets($pricingRule, $options);

        if ($pricingRule->areReady(false) === SheetKeeper::NO_SHEETS) {
            $translation = isset($blockName)
                ? 'appropriate_printed_sheet_of_block_not_found'
                : 'appropriate_printed_sheet_not_found'
            ;

            $this->errors->add(
                trans('calculator_errors.' . $translation, [
                    'name' => trans('calculation_variables.' . $blockName),
                ]),
                Error::CALCULATION_ERROR
            );

            return false;
        }

        return true;
    }

    /**
     * Adds a printed sheet to the unstandard estimate.
     *
     * @param  array $unstandardEstimate
     * @param  ValuesOfPrintedSheet $valuesOfPrintedSheet
     * @return array
     */
    public function addPrintedSheetToUnstandardEstimate(
        array $unstandardEstimate,
        ValuesOfPrintedSheet $valuesOfPrintedSheet
    ): array {
        /** @var CalculatorPrintedSheet $printedSheet An optimal printed sheet */
        $printedSheet = $valuesOfPrintedSheet->getSheet()->getModel();

        // The printed sheet is used to get 'Количество печатных листов на покупном'.
        return $unstandardEstimate + compact('printedSheet');
    }

    /**
     * Defines an intersection of printed sheets between printed sheets
     * of the given pricing rule and printed sheets of options.
     *
     * @param  AbstractPrintedSheets $pricingRule
     * @param  OptionCollection  $name
     * @return void
     */
    public function defineIntersectionOfPrintedSheets(AbstractPrintedSheets $pricingRule, OptionCollection $options): void
    {
        /** @var array An array with arrays which contains IDs of printed sheets. **/
        $arraysWithIdsOfOptions = $this->getArraysWithIDsOfPrintedSheetsFromValuesOfOptions($options);
        $pricingRule->filterByIntersectionWithArraysOfIds($arraysWithIdsOfOptions);
    }

    /**
     * Returns arrays with IDs of printed sheets from values of options.
     *
     * @param  OptionCollection $options A collection with options and their values.
     * @return array
     */
    public function getArraysWithIDsOfPrintedSheetsFromValuesOfOptions(OptionCollection $options): array
    {
        return $options
            ->getValues()
            ->load('printedSheets')
            ->pluck('printedSheets.*.id')
            ->filter('count')
            ->values()
            ->toArray()
        ;
    }
}
