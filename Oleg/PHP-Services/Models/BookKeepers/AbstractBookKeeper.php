<?php

namespace App\Model\Calculator\BookKeepers;

use App\Services\Calculators\Errors;
use App\Model\Calculator\OptionCollection;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRule;
use App\Model\Calculator\PricingRulesOfAssemblies\ValueKeeper;
use App\Services\Calculators\Blocks\UserBlocks;
use App\Services\Calculators\Error;
use App\Services\Formula\VariableCollection;
use Throwable;

/**
 * Calculates a price of an assembly when blocks have only one assembly.
 */
abstract class AbstractBookKeeper extends BaseBookKeeper
{
    use CalculationOfPricingRule;

    /**
     * Calculates prices by the given user blocks with user data.
     * Returns true after a successful calculation.
     *
     * @param  UserBlocks $userBlocks
     * @return bool
     */
    public function calculateByBlocks(UserBlocks $userBlocks): bool
    {
        $this->resetCalculation();
        $valueKeeper = $this->createValueKeeper($userBlocks);

        if (!$valueKeeper) {
            return false;
        }

        $nameOfBlockWithAssembly = $userBlocks->getFirstNameOfVisibleBlockWithAssembly();
        $assembly = $userBlocks->getUserBlock($nameOfBlockWithAssembly)->getAssembly();
        $pricingRule = $this->getNeedPricingRuleByAssembly($assembly);
        $options = $userBlocks->getUserBlock($nameOfBlockWithAssembly)->getUsedOptions();

        if (!$this->calculatePricingRule($pricingRule, $options, $valueKeeper)) {
            return $this->calculationOfPricingRuleHasErrors($pricingRule->getErrors());
        }

        // Calculates prices of the options
        $variables = $this->createFormulaVariablesByValueKeeper($valueKeeper);
        $price = $pricingRule->getEstimateGroup()->get('price')->getValue();
        $pricePerProduct = $pricingRule->getEstimateGroup()->get('price_per_product')->getValue();
        $numberOfProducts = $variables->getByCode('number_of_products')->getValue();

        $estimateGroupOfOptions = $this->calculateOptions(
            $options,
            $price,
            $pricePerProduct,
            $numberOfProducts,
            $variables,
            $valueKeeper
        );

        if (!$estimateGroupOfOptions) {
            return $this->calculationOfOptionsHasError();
        }

        // Pushes estimates
        $this->pushDefaultEstimateGroupByValueKeeper($valueKeeper);
        $this->pushEstimateGroupsByValueKeeper($valueKeeper);
        $this->pushEstimateGroupOfAttributesByAssembly($assembly);
        $this->pushEstimateGroupOfPricingRule($pricingRule, $valueKeeper);
        $this->pushEstimateOfFormulaVariables($variables);
        $this->pushEstimateGroupOfOptions($estimateGroupOfOptions);
        $price = $this->calculatePriceOfEstimateByEstimateGroups();

        $this->estimate->setNumberOfProducts($valueKeeper->getNumberOfProducts());
        $this->estimate->setPrice($price);

        return true;
    }

    /**
     * Initializes and returns a new ValueKeeper with values for the calculation.
     *
     * @param  UserBlocks $userBlocks
     * @return ValueKeeper|null
     */
    abstract public function createValueKeeper(UserBlocks $userBlocks): ?ValueKeeper;

    /**
     * Creates values for formulae of options by the given ValueKeeper.
     *
     * @param  ValueKeeper $valueKeeper
     * @return VariableCollection
     */
    abstract public function createFormulaVariablesByValueKeeper(
        ValueKeeper $valueKeeper
    ): VariableCollection;

    /**
     * Calculates a price of the given pricing rule.
     *
     * @param  PricingRule      $pricingRule
     * @param  OptionCollection $options
     * @param  ValueKeeper      $valueKeeper
     * @return bool
     */
    public function calculatePricingRule(
        PricingRule $pricingRule,
        OptionCollection $options,
        ValueKeeper $valueKeeper
    ): bool {
        if (!($this->checkAndPreparePricingRule($pricingRule, $options, $valueKeeper))) {
            return false;
        }

        // Tries to calculate. Logs possible errors.
        try {
            $successfulCalculated = $pricingRule->calculate($valueKeeper);
        } catch (\Throwable $th) {
            $this->prepareThrowOfCalculation($th);
            return false;
        }

        return $successfulCalculated;
    }

    /**
     * Checks and prepares the given pricing rule.
     *
     * @param  PricingRule      $pricingRule
     * @param  OptionCollection $options
     * @param  ValueKeeper      $valueKeeper
     * @return bool
     */
    public function checkAndPreparePricingRule(
        PricingRule $pricingRule,
        OptionCollection $options,
        ValueKeeper $valueKeeper
    ): bool {
        return $this->checkPricingRule($pricingRule) && $this->preparePricingRule($pricingRule, $options, $valueKeeper);
    }

    /**
     * Checks the PricingRule to calculate a price of products.
     *
     * @param  PricingRule $pricingRule
     * @return bool
     */
    abstract public function checkPricingRule(PricingRule $pricingRule): bool;

    /**
     * Prepares the given pricing rule: loads data, filter data, etc.
     *
     * @param  PricingRule      $pricingRule
     * @param  OptionCollection $options
     * @param  ValueKeeper      $valueKeeper
     * @return bool
     */
    abstract public function preparePricingRule(
        PricingRule $pricingRule,
        OptionCollection $options,
        ValueKeeper $valueKeeper
    ): bool;

    /**
     * Pushes the default estimate group by the given ValueKeeper.
     *
     * @param  ValueKeeper $valueKeeper
     * @return void
     */
    abstract public function pushDefaultEstimateGroupByValueKeeper(ValueKeeper $valueKeeper): void;

    /**
     * Pushes other estimate groups by the given ValueKeeper.
     *
     * @param  ValueKeeper $valueKeeper
     * @return void
     */
    abstract public function pushEstimateGroupsByValueKeeper(ValueKeeper $valueKeeper): void;

    /**
     * Calculates a price by estimate groups of the estimate.
     *
     * @return float|null
     */
    abstract public function calculatePriceOfEstimateByEstimateGroups(): ?float;

    /**
     * Prepares the given throw of a calculation. Adds an error to the error list.
     *
     * @param  Throwable $throwable
     * @return void
     */
    public function prepareThrowOfCalculation(Throwable $throwable): void
    {
        if ($throwable->getMessage() === 'Division by zero') {
            $message = trans('calculator_errors.division_by_zero_during_calculation');
        } else {
            $message = trans('calculator_errors.undefined_error_of_calculation', [
                'message' => $throwable->getMessage(),
            ]);
        }

        $this->errors->add($message, Error::CALCULATION_ERROR);
    }

    /**
     * Adds the error to the errors. Returns 'false'.
     *
     * @return bool
     */
    public function calculationOfOptionsHasError(): bool
    {
        $this->errors->add(trans('calculator_errors.calculation_of_options_has_error'), Error::OPTION_ERROR);

        return false;
    }

    /**
     * Adds the error to the errors. Returns 'false'.
     *
     * @param  Errors $errors
     * @return bool
     */
    public function calculationOfPricingRuleHasErrors(Errors $errors): bool
    {
        $this->errors->unite($errors);
        $this->errors->add(trans('calculator_errors.price_calculation_is_unsuccessful'), Error::CALCULATION_ERROR);

        return false;
    }

    /**
     * Adds the error to the errors. Returns 'false'.
     *
     * @return bool
     */
    public function appropriatePrintedSheetsNotFound(): bool
    {
        $this->errors->add(
            trans('calculator_errors.appropriate_printed_sheet_for_product_size_not_found'),
            Error::CALCULATION_ERROR
        );

        return false;
    }
}
