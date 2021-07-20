<?php

namespace App\Model\Calculator\BookKeepers;

use App\Model\Calculator\Calculator;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRule;
use App\Model\Calculator\OptionCollection;
use App\Model\Calculator\PricingRulesOfAssemblies\AbstractPrintedSheets;
use App\Model\Calculator\PricingRulesOfAssemblies\ValueKeeper;
use App\Model\Calculator\PricingRulesOfAssemblies\Values\ValuesOfPrintedSheet;
use App\Services\Calculators\Error;
use App\Services\Calculators\EstimateGroup;
use App\Services\Calculators\IndividualSettingsService;
use App\Services\Formula\VariableCollection;

/**
 * Calculates a price of products of any sheets (typical sheets or wide printed
 * sheets).
 */
abstract class BookKeeperOfPrintedSheets extends AbstractBookKeeper
{
    use ThrowingExceptionOfInvalidPricingRule;
    use CalculationOfPrintedSheets;

    /**
     * An instance of IndividualSettingsService.
     *
     * @var IndividualSettingsService
     */
    protected $individualSettingsService;

    /**
     * The current calculator.
     *
     * @var Calculator
     */
    protected $calculator;

    /**
     * Initializes an instance of the class.
     *
     * @param IndividualSettingsService $individualSettingsService
     * @param Calculator $calculator
     */
    public function __construct(
        IndividualSettingsService $individualSettingsService,
        Calculator $calculator
    ) {
        parent::__construct();

        $this->calculator = $calculator;
        $this->setIndividualSettingsService($individualSettingsService, $calculator);
    }

    /**
     * Checks the PricingRule to calculate a price of products.
     *
     * @param  PricingRule|AbstractPrintedSheets $pricingRule
     * @return bool
     */
    public function checkPricingRule(PricingRule $pricingRule): bool
    {
        if (!($pricingRule instanceof AbstractPrintedSheets)) {
            $this->throwInvalidPricingRule($pricingRule, [AbstractPrintedSheets::class]);
            $this->errors->add(trans('calculator_errors.pricing_rule_is_invalid'), Error::SYSTEM_ERROR);
            return false;
        }

        return $this->checkPricingRuleOfPrintedSheets($pricingRule);
    }

    /**
     * Prepares the given pricing rule: loads data, filter data, etc.
     *
     * @param  AbstractPrintedSheets $pricingRule
     * @param  OptionCollection $options
     * @param  ValuesOfPrintedSheet $valueKeeper
     * @return bool
     */
    public function preparePricingRule(
        PricingRule $pricingRule,
        OptionCollection $options,
        ValueKeeper $valueKeeper
    ): bool {
        return $this->preparePricingRuleOfPrintedSheets($pricingRule, $options, $valueKeeper);
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
     * Creates values for formulae of options by the given ValueKeeper.
     *
     * @param  ValuesOfPrintedSheet $valueKeeper
     * @return VariableCollection
     */
    public function createFormulaVariablesByValueKeeper(
        ValueKeeper $valueKeeper
    ): VariableCollection {
        $variables = new VariableCollection();

        return $this->addDataOfPrintedSheetToVariables($variables, $valueKeeper);;
    }

    /**
     * Pushes the default estimate group by the given ValueKeeper.
     *
     * @param  ValuesOfPrintedSheet $valueKeeper
     * @return void
     */
    public function pushDefaultEstimateGroupByValueKeeper(ValueKeeper $valueKeeper): void
    {
        $group = new EstimateGroup('default');
        $group->add('number_of_products', $valueKeeper->getNumberOfProducts());
        $group->add('product_height', $valueKeeper->getHeightOfProduct());
        $group->add('product_width', $valueKeeper->getWidthOfProduct());
        $this->estimate->pushGroup($group);
    }

    /**
     * Calculates a price by estimate groups of the estimate.
     * Calculates the common estimates.
     *
     * @return float|null
     */
    public function calculatePriceOfEstimateByEstimateGroups(): ?float
    {
        if (!$this->estimate->groupExists('assembly')) {
            return null;
        }

        $price = $this->getPriceOfEstimateGroup('assembly');
        $price += $this->getPriceOfEstimateGroup('options', 'price_of_options');

        return $price;
    }

    /**
     * Sets the given IndividualSettingsService and configures its by the given
     * calculator.
     *
     * @param  IndividualSettingsService $service
     * @param  Calculator $calculator
     * @return void
     */
    protected function setIndividualSettingsService(IndividualSettingsService $service, Calculator $calculator): void
    {
        $this->individualSettingsService = $service;
        $this->individualSettingsService->setCalculatorAndBookKeeper($calculator, $this);
    }
}
