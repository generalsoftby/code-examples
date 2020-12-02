<?php

namespace App\Model\Calculator\BookKeepers;

use App\Classes\Calculator\Option\SimpleOption;
use App\Model\Calculator\CalculatorAssembly as Assembly;
use App\Model\Calculator\CalculatorAttributeValue;
use App\Model\Calculator\OptionCollection;
use App\Model\Calculator\PricingRulesOfAssemblies\Copy;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRule;
use App\Model\Calculator\PricingRulesOfAssemblies\ValueKeeper;
use App\Services\Calculators\Error;
use App\Services\Calculators\Errors;
use App\Services\Calculators\Estimate;
use App\Services\Calculators\EstimateGroup;
use App\Services\Calculators\EstimateItem;
use App\Services\Calculators\EstimateSubgroup;
use App\Services\Formula\VariableCollection;
use Throwable;

/**
 * Implementes common methods of calculation for each BookKeeper.
 *
 * @property Estimate $estimate
 * @property Errors $errors
 */
trait CalculationOfPricingRule
{
    /**
     * Resets data of the last calculation.
     *
     * @return void
     */
    public function resetCalculation(): void
    {
        $this->estimate->clean();
        $this->estimate->createGroup('default');
        $this->errors->clean();
    }

    /**
     * Returns a need pricing rule by the given assembly.
     * If the original pricing rule is the copy then returns its child pricing rule.
     *
     * @param  Assembly $assembly
     * @return PricingRule
     */
    public function getNeedPricingRuleByAssembly(Assembly $assembly): PricingRule
    {
        /** @var PricingRule|Copy $pricingRule */
        $pricingRule = $assembly->getPricingRule();

        return $this->getNeedPricingRule($pricingRule);
    }

    /**
     * Returns an original pricing rule or a pricing rule by the copy
     * of pricing rule.
     *
     * @param  PricingRule $pricingRule
     * @return PricingRule
     */
    public function getNeedPricingRule(PricingRule $pricingRule): PricingRule
    {
        return $pricingRule instanceof Copy
            ? $pricingRule->getCopyPricingRule()
            : $pricingRule
        ;
    }

    /**
     * Calculates a price of the given options by the given variables and
     * ValueKeeper.
     *
     * @param  OptionCollection $options
     * @param  float $price
     * @param  float $pricePerProduct
     * @param  int   $numberOfProducts
     * @param  VariableCollection   $variables
     * @param  ValuesOfPrintedSheet $valueKeeper
     * @return EstimateGroup|null
     */
    public function calculateOptions(
        OptionCollection $options,
        float $price,
        float $pricePerProduct,
        int $numberOfProducts,
        VariableCollection $variables,
        ValueKeeper $valueKeeper
    ): ?EstimateGroup {
        $unstandardEstimate = $this->makeUnstandardEstimate($price, $pricePerProduct, $valueKeeper);
        $valuesOfFormula = $this->makeValuesOfFormula($variables, $numberOfProducts, $price);

        return $this->calculateOptionsByUnstandardEstimate($options, $unstandardEstimate, $valuesOfFormula);
    }

    /**
     * Makes an unstandard estimate in the form of an array.
     *
     * @param  float $price
     * @param  float $pricePerProduct
     * @param  ValueKeeper $valueKeeper
     * @return array
     */
    public function makeUnstandardEstimate(float $price, float $pricePerProduct, ValueKeeper $valueKeeper): array
    {
        return [
            'label' => 'Name of pricing rule',
            'for_one' => $pricePerProduct,
            'total' => $price,
            'date' => (new \DateTime())->format('d.m.Y'), // NOTE: Заглушка
        ];
    }

    /**
     * Makes values of a formula in the form of an array.
     *
     * @param  VariableCollection $variables
     * @param  int   $numberOfProducts
     * @param  float $price
     * @return array
     */
    public function makeValuesOfFormula(VariableCollection $variables, int $numberOfProducts, float $price): array
    {
        return $variables->getValues() + [
            'amount' => $numberOfProducts,
            // A price of the assembly
            'total_original' => $price,
        ];
    }

    /**
     * Calculates prices of the given options by the given array of the
     * unstandard data of an estimate and values of a formula.
     *
     * @param  OptionCollection $options
     * @param  array $unstandardEstimate
     * @param  array $valuesOfFormula
     * @return EstimateGroup|null
     */
    public function calculateOptionsByUnstandardEstimate(
        OptionCollection $options,
        array $unstandardEstimate,
        array $valuesOfFormula
    ): ?EstimateGroup {
        $group = new EstimateGroup('options');

        // NOTE: Если значений опций нет, то не обрабатывать. Данное условие должно быть в расчете опций!
        if (!$options->getValues()->count()) {
            return $group;
        }

        // NOTE: Костыль для расчета опций.
        $simpleOption = new SimpleOption;

        try {
            $prices = $simpleOption->computeOptions($options->getValues(), [$unstandardEstimate], $valuesOfFormula);
        } catch (\Throwable $th) {
            $this->prepareThrowOfCalculationOfOption($th);
            return null;
        }

        // NOTE: Содержит только одну цену
        $prices = current($prices);

        // Prepares options for the estimate.
        if (isset($prices['options'])) {
            /** @var array|string[] $option Contains a description and values of the option. */
            foreach ($prices['options'] as $option) {
                $item = new EstimateItem($this->createEstimateSubgroupOfOption($option));
                $group->push($option['option_name'], $item);
            }
        }

        return $group;
    }

    /**
     * Prepares the given throw of calculation of a option. Adds an error to the error list.
     *
     * @param  Throwable $throwable
     * @return void
     */
    public function prepareThrowOfCalculationOfOption(Throwable $throwable): void
    {
        if ($throwable->getMessage() === 'Division by zero.') {
            $message = trans('calculator_errors.division_by_zero_during_calculation_of_option');
        } else {
            $message = trans('calculator_errors.undefined_error_of_calculation_of_option', [
                'message' => $throwable->getMessage(),
            ]);
        }

        $this->errors->add($message, Error::OPTION_ERROR);
    }

    /**
     * Creates and returns a new EstimateSubgroup with data of the given option.
     *
     * @param  array $values
     * @return EstimateSubgroup
     */
    public function createEstimateSubgroupOfOption(array $values): EstimateSubgroup
    {
        $subgroup = new EstimateSubgroup;
        $subgroup->setType('option');
        $subgroup->add('price', $values['total_price'] ?? 0);
        $subgroup->add('price_per_unit', $values['one_price'] ?? 0);
        // NOTE: optons_user_name - the mistake.
        $subgroup->add('name_of_option', $values['optons_user_name']);
        $subgroup->add('system_name_of_option', $values['option_name'], true);
        $subgroup->add('name_of_option_value', $values['value_specification_name']);
        $subgroup->add('system_name_of_option_value', $values['value_name'], true);
        $subgroup->add('number_of_products', $values['amount'] ?? 0);

        return $subgroup;
    }

    /**
     * Pushes an estimate group of attributes by the given assembly.
     *
     * @param  Assembly $assembly
     * @param  string   $estimateGroupName
     * @return void
     */
    public function pushEstimateGroupOfAttributesByAssembly(
        Assembly $assembly,
        string $estimateName = 'attributes'
    ): void {
        $assembly->loadMissing('attribute_values.attribute');
        $estimateGroup = new EstimateGroup($estimateName);

        foreach ($assembly->attribute_values as $attributeValue) {
            $estimateGroup->add(
                $attributeValue->attribute->title_specification,
                $this->createEstimateSubgroupFromAttributeValue($attributeValue)
            );
        }

        $estimateGroup->setName($estimateName);
        $this->estimate->pushGroup($estimateGroup);
    }

    /**
     * Pushes an estimate group of the given pricing rule.
     *
     * @param  PricingRule $pricingRule
     * @param  ValueKeeper $valueKeeper
     * @param  string      $estimateName
     * @return void
     */
    public function pushEstimateGroupOfPricingRule(
        PricingRule $pricingRule,
        ValueKeeper $valueKeeper,
        string $estimateName = 'assembly'
    ): void {
        $group = $pricingRule->getEstimateGroup();
        $group->setName($estimateName);
        $this->estimate->pushGroup($group);
    }

    /**
     * Creates and returns an estimate subgroup by the given attribute value.
     *
     * @param  CalculatorAttributeValue $attributeValue
     * @return EstimateSubgroup
     */
    public function createEstimateSubgroupFromAttributeValue(
        CalculatorAttributeValue $attributeValue
    ): EstimateSubgroup {
        $subgroup = new EstimateSubgroup;
        $subgroup->setType('attribute');
        $subgroup->add('name_of_attribute', $attributeValue->attribute->title_specification);
        $subgroup->add('value_of_attribute', $attributeValue->value);
        $subgroup->add('system_name_of_attribute', $attributeValue->attribute->title, true);

        return $subgroup;
    }

    /**
     * Pushes an estimate of formula variables by the given collection.
     *
     * @param  VariableCollection $variables
     * @param  string $estimateName
     * @return void
     */
    public function pushEstimateOfFormulaVariables(
        VariableCollection $variables,
        string $estimateName = 'formula_variables'
    ): void {
        $group = new EstimateGroup($estimateName);

        foreach ($variables as $variable) {
            $group->add($variable->getVariableName(), $variable->getValue(), true);
        }

        $this->estimate->pushGroup($group);
    }

    /**
     * Pushes the given estimate group of options.
     *
     * @param  EstimateGroup $group
     * @param  string        $estimateName
     * @return void
     */
    public function pushEstimateGroupOfOptions(EstimateGroup $group, string $estimateName = 'options'): void
    {
        if (!$group->count()) {
            return;
        }

        $priceOfOptions = $this->sumPricesOfOptionsByEstimateGroup($group);
        $group->add('price_of_options', $priceOfOptions);

        $group->setName($estimateName);
        $this->estimate->pushGroup($group);
    }

    /**
     * Sums prices of options of the given estimate group.
     *
     * @param  EstimateGroup $group
     * @return float
     */
    public function sumPricesOfOptionsByEstimateGroup(EstimateGroup $group): float
    {
        $priceOfOptions = 0;

        // Sums prices of each option
        foreach ($group as $optionName => $item) {
            $subgroup = $item->getValue();

            if ($subgroup instanceof EstimateSubgroup) {
                $priceOfOptions += (float) $subgroup->get('price')->getValue();
            }
        }

        return $priceOfOptions;
    }

    /**
     * Returns a price of an estimate group by the given name of the group
     * and the given value name. Returns '0' if the value does not exist.
     *
     * @param  string $groupName
     * @return float
     */
    public function getPriceOfEstimateGroup(string $groupName, string $valueName = 'price'): float
    {
        $group = $this->estimate->getGroup($groupName);
        $value = $group ? $group->get($valueName) : null;

        return $value ? $value->getValue() : 0;
    }
}
