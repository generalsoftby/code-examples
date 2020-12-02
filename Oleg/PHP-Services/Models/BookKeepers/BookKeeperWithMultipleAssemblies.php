<?php

namespace App\Model\Calculator\BookKeepers;

use App\Model\Calculator\OptionCollection;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRule;
use App\Model\Calculator\PricingRulesOfAssemblies\ValueKeeper;
use App\Model\Calculator\PricingRulesOfAssemblies\Values\ValuesWithManyAssemblies;
use App\Services\Calculators\Blocks\UserBlocks;
use App\Services\Calculators\Error;
use App\Services\Calculators\Errors;
use App\Services\Calculators\EstimateGroup;
use App\Services\Formula\VariableCollection;
use Throwable;

/**
 * Calculates prices of assemblies when blocks have more than one assembly.
 */
abstract class BookKeeperWithMultipleAssemblies extends BaseBookKeeper
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

        $this->pushDefaultEstimateGroupByValueKeeper($valueKeeper);
        $this->pushEstimateGroupsByValueKeeper($valueKeeper);

        // It adds estimates of the calculation there.
        if (!$this->calculateAssembliesOfBlocks($valueKeeper, $userBlocks)) {
            return false;
        }

        $price = $this->calculatePriceOfEstimateByEstimateGroups($userBlocks);

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
    * Calculates assemblies of blocks.
    *
    * @param  ValuesWithManyAssemblies $valueKeeper
    * @param  UserBlocks $userBlocks
    * @return bool
    */
   public function calculateAssembliesOfBlocks(ValuesWithManyAssemblies $valueKeeper, UserBlocks $userBlocks): bool
   {
       $state = true;
       $blocksWithAssemblies = $userBlocks->getNamesOfBlocksWithAssemblies();

       foreach ($blocksWithAssemblies as $blockName) {
           if ($state && $valueKeeper->doesUserBlockUseAssembly($blockName)) {
               $state = $this->calculateAssemblyOfBlock($blockName, $valueKeeper, $userBlocks);
           }
       }

       return $state;
   }

   /**
    * Calculates an assembly of a block by the given name.
    *
    * @param  string     $blockName
    * @param  ValuesWithManyAssemblies $valueKeeper
    * @param  UserBlocks $userBlocks
    * @return bool
    */
   public function calculateAssemblyOfBlock(
       string $blockName,
       ValuesWithManyAssemblies $valueKeeper,
       UserBlocks $userBlocks
   ): bool {
        $valueKeeper->setTypeOfBlock($blockName);
        $assembly = $userBlocks->getUserBlock($blockName)->getAssembly();
        $pricingRule = $this->getNeedPricingRuleByAssembly($assembly);
        $options = $userBlocks->getUserBlock($blockName)->getOptionKeeper()->getOptionsUsingIds();

        if (!$this->calculatePricingRuleOfBlock($pricingRule, $options, $valueKeeper, $blockName, $assembly->title)) {
            return $this->calculationOfPricingRuleOfBlockHasErrors(
                $pricingRule->getErrors(),
                $blockName,
                $assembly->title
            );
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
            return $this->calculationOfOptionsOfBlockHasError($blockName);
        }

        $this->pushEstimateGroupOfAttributesByAssembly($assembly, 'attributes_of_' . $blockName);
        $this->pushEstimateGroupOfPricingRule($pricingRule, $valueKeeper, $blockName);
        $this->pushEstimateOfFormulaVariables($variables, 'formula_variables_of_' . $blockName);
        $this->pushEstimateGroupOfOptions($estimateGroupOfOptions, 'options_of_' . $blockName);

        return true;
   }

   /**
    * Calculates a price of the given pricing rule.
    *
    * @param  PricingRule $pricingRule
    * @param  OptionCollection $options
    * @param  ValueKeeper $valueKeeper
    * @param  string      $blockName
    * @param  string      $nameOfPricingRule
    * @return bool
    */
   public function calculatePricingRuleOfBlock(
       PricingRule $pricingRule,
       OptionCollection $options,
       ValueKeeper $valueKeeper,
       string $blockName,
       string $nameOfPricingRule
   ): bool {
        if (!$this->checkAndPreparePricingRule($pricingRule, $options, $valueKeeper, $blockName, $nameOfPricingRule)) {
            return false;
        }

        // Tries to calculate. Logs possible errors.
        try {
            $successfulCalculated = $pricingRule->calculate($valueKeeper);
        } catch (\Throwable $th) {
            $this->prepareThrowOfCalculationOfBlock($th, $blockName);
            return false;
        }

       return $successfulCalculated;
   }

   /**
    * Checks and prepares the given pricing rule of a block.
    *
    * @param  PricingRule      $pricingRule
    * @param  OptionCollection $options
    * @param  ValueKeeper      $valueKeeper
    * @param  string      $blockName
    * @param  string      $nameOfPricingRule
    * @return bool
    */
   public function checkAndPreparePricingRule(
       PricingRule $pricingRule,
       OptionCollection $options,
       ValueKeeper $valueKeeper,
       string $blockName,
       string $nameOfPricingRule
   ): bool {
        return $this->checkPricingRule($pricingRule, $blockName, $nameOfPricingRule)
            && $this->preparePricingRule($pricingRule, $options, $valueKeeper, $blockName)
        ;
   }

   /**
    * Checks whether the given PricingRule to calculate a price of products.
    *
    * @param  PricingRule $pricingRule
    * @param  string      $blockName
    * @param  string      $nameOfPricingRule
    * @return bool
    */
   abstract public function checkPricingRule(
       PricingRule $pricingRule,
       string $blockName,
       string $nameOfPricingRule
   ): bool;

    /**
     * Prepares the given pricing rule of a block: loads data, filter data, etc.
     *
     * @param  PricingRule $pricingRule
     * @param  OptionCollection $options
     * @param  ValueKeeper $valueKeeper
     * @param  string $blockName
     * @return bool
     */
    abstract public function preparePricingRule(
        PricingRule $pricingRule,
        OptionCollection $options,
        ValueKeeper $valueKeeper,
        string $blockName
    ): bool;

   /**
    * Prepares the given throw of calculation of the block.
    * Add an error to the error list.
    *
    * @param  Throwable $throwable
    * @param  string    $blockName
    * @return void
    */
   public function prepareThrowOfCalculationOfBlock(Throwable $throwable, string $blockName): void
   {
       if ($throwable->getMessage() === 'Division by zero') {
           $message = trans('calculator_errors.division_by_zero_during_calculation_of_block', [
               'name' => $blockName,
           ]);
       } else {
           $message = trans('calculator_errors.undefined_error_of_calculation_of_block', [
               'name' => $blockName,
               'message' => $throwable->getMessage(),
           ]);
       }

       $this->errors->add($message, Error::CALCULATION_ERROR);
   }

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
     * @param  UserBlocks $userBlocks
     * @return float|null
     */
    abstract public function calculatePriceOfEstimateByEstimateGroups(UserBlocks $userBlocks): ?float;

    /**
     * Calculates a sum of prices of options of the estimate by the given
     * names of visible blocks that have assembly and options.
     *
     * @param  array|string[] $namesOfBlocksWithOptions
     * @return int
     */
    public function calculatePriceOfOptions(array $namesOfBlocksWithOptions): int
    {
        $priceOfOptions = 0;

        // Gets each estimate group
        foreach ($namesOfBlocksWithOptions as $nameOfEstimateGroup) {
            /** @var EstimateGroup|null $group */
            $group = $this->estimate->getGroup('options_of_' . $nameOfEstimateGroup);

            if ($group) {
                $priceOfOptions += $this->sumPricesOfOptionsByEstimateGroup($group);
            }
        }

        return $priceOfOptions;
    }

    /**
     * Adds the error to the errors. Returns 'false'.
     *
     * @param  Errors $errors
     * @param  string $blockName
     * @param  string $nameOfPricingRule
     * @return bool
     */
    public function calculationOfPricingRuleOfBlockHasErrors(
        Errors $errors,
        string $blockName,
        string $nameOfPricingRule
    ): bool {
        $this->errors->unite($errors);
        $this->errors->add(
            trans('calculator_errors.calculation_of_block_is_unsuccessful', [
                'name' => trans('calculation_variables.' . $blockName),
                'name_of_pricing_rule' => $nameOfPricingRule
            ]),
            Error::CALCULATION_ERROR
        );

        return false;
    }

    /**
     * Adds the error to the errors. Returns 'false'.
     *
     * @param  string $blockName
     * @return bool
     */
    public function calculationOfOptionsOfBlockHasError(string $blockName): bool
    {
        $this->errors->add(
            trans('calculator_errors.calculation_of_options_of_block_has_error', [
                'name' => trans('calculation_variables.' . $blockName),
            ]),
            Error::OPTION_ERROR
        );

        return false;
    }
}
