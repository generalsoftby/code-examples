<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\PricingRulesByTypes\PrintedSheets;

use App\Services\Calculators\Error;
use App\Services\Calculators\Errors;
use App\Services\Calculators\EstimateGroup;
use App\Model\Calculator\PricingRulesOfAssemblies\Cost;
use App\Model\Calculator\PricingRulesOfAssemblies\IntervalCollection;
use App\Model\Calculator\PricingRulesOfAssemblies\Estimates\PrintedSheet\EstimateOfPrintedSheet;
use App\Model\Calculator\PricingRulesOfAssemblies\Estimates\PrintedSheet\EstimateOfPrintedSheetByCustomEditionSize;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRulesByTypes\PricingRuleWithInitializationOfAdditionalPrices;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRulesByTypes\GettingSettingStandardPricingRule;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRulesByTypes\InitializationOfAdditionalPrices;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRulesByTypes\InitializationOfIntervals;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRulesByTypes\MethodsOfIntervals;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRuleWithIntervals;
use App\Model\Calculator\PricingRulesOfAssemblies\ThrowingExceptionOfInvalidValueKeeper;
use App\Model\Calculator\PricingRulesOfAssemblies\ValueKeeper;
use App\Model\Calculator\PricingRulesOfAssemblies\Values\ValuesOfSheet;

/**
 * The class of pricing rules for the 'custom edition size' type.
 */
class CustomEditionSize implements
    PricingRuleOfPrintedSheets,
    PricingRuleWithIntervals,
    PricingRuleWithInitializationOfAdditionalPrices
{
    use GettingSettingStandardPricingRule;
    use MethodsOfIntervals;
    use ThrowingExceptionOfInvalidValueKeeper;
    use InitializationOfAdditionalPrices;
    use InitializationOfIntervals;

    /**
     * A name of the pricing.
     *
     * @var string
     */
    protected $name;

    /**
     * A collection with intervals of products.
     *
     * @var IntervalCollection
     */
    protected $intervals;

    /**
     * An extra charge.
     *
     * @var Cost
     */
    protected $extraCharge;

    /**
     * A min price for the edition size.
     *
     * @var Cost
     */
    protected $minPrice;

    /**
     * An instance of the Errors.
     *
     * @var Errors
     */
    protected $errors;

    /**
     * A state of throwing oexceptions.
     * If it is true, then system exceptions will be thrown.
     *
     * @var bool
     */
    protected $throwExceptions;

    /**
     * An EstimateOfPrintedSheet.
     *
     * @var EstimateOfPrintedSheet|EstimateOfPrintedSheetByCustomEditionSize
     */
    protected $estimateOfPrintedSheet;

    /**
     * Initializes an instance of the class.
     *
     * @param array|null $rules
     */
    public function __construct(array $rules = null)
    {
        $this->errors = new Errors;
        $this->intervals = new IntervalCollection();
        $this->fillFromArray($rules ?? []);
    }

    /**
     * Fills the instance from an array with rules.
     *
     * @param  array  $rules
     * @return void
     */
    public function fillFromArray(array $rules): void
    {
        $this->name = $rules['name'] ?? trans('assemblies.default_name_of_pricing_rule');
        $this->setIntervals($this->defineInvervalOfProductsFromArray($rules['intervals'] ?? []));
        $this->extraCharge = $this->newExtraCharge($rules['extra_charge'] ?? []);
        $this->minPrice = $this->newMinPrice($rules['min_price'] ?? []);
    }

    /**
     * Checks whether the settings of the pricing rule are configured.
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        // The data of the pricing rule are filled by the default values when
        // given values are empty.
        return $this->hasIntervals();
    }

    /**
     * Calculates a cost of the printed sheet by input values.
     *
     * @param  ValueKeeper|ValuesOfSheet $keeper
     * @return bool
     *
     * @throws InvalidValueKeeperException If $keeper is an unsupported class.
     */
    public function calculate(ValueKeeper $keeper): bool
    {
        if (!($keeper instanceof ValuesOfSheet)) {
            $this->throwInvalidValueKeeper($keeper, [ValuesOfSheet::class]);
            $this->errors->add(trans('calculator_errors.value_keeper_is_invalid'), Error::SYSTEM_ERROR);
            return false;
        }

        /** @var int|null $numberOfPrintedSheets */
        $numberOfPrintedSheets = $keeper->getNumberOfPrintedSheetsWithSheetsForFitting();
        $appropriateInterval = $numberOfPrintedSheets
            ? $this->intervals->getIntervalByValue($numberOfPrintedSheets)
            : null
        ;

        if (!$appropriateInterval) {
            $this->errors->add(
                trans('calculator_errors.appropriate_interval_of_custom_intervals_for_printed_sheet_not_found', [
                    'name' => $keeper->getSheet()->getName()
                ]),
                Error::CALCULATION_ERROR
            );
            return false;
        }

        $costInRubByCurrency = $appropriateInterval->getValueByCurrency();
        $originalCostOfProducts = $keeper->getNumberOfPrintedSheetsWithSheetsForFitting() * $costInRubByCurrency;
        $originalPriceOfProducts = $originalCostOfProducts + $this->getExtraCharge()->getValueByCurrency();

        /** @var float $priceOfProducts **/
        $priceOfProducts = 0;

        // It is true when a cost of products with extra charge
        // less then a minimal price of products.
        // This means the price of products was changed.
        $ruleOfMinimumWasUsed = false;

        if ($originalPriceOfProducts < $this->getMinPrice()->getValueByCurrency()) {
            $priceOfProducts = $this->getMinPrice()->getValueByCurrency();
            $ruleOfMinimumWasUsed = true;
        } else {
            $priceOfProducts = $originalPriceOfProducts;
        }

        $this->estimateOfPrintedSheet = new EstimateOfPrintedSheetByCustomEditionSize(
            $this->name,
            $keeper,
            $priceOfProducts,
            $originalCostOfProducts,
            $originalPriceOfProducts,
            $this->getExtraCharge(),
            $appropriateInterval,
            $ruleOfMinimumWasUsed
        );

        return true;
    }

    /**
     * Returns an EstimateOfPrintedSheetByCustomEditionSize.
     *
     * @return EstimateOfPrintedSheet|EstimateOfPrintedSheetByCustomEditionSize
     */
    public function getEstimateOfPrintedSheet(): ?EstimateOfPrintedSheet
    {
        return $this->estimateOfPrintedSheet;
    }

    /**
     * Returns an instance of EstimateGroup.
     *
     * @return EstimateGroup|null
     */
    public function getEstimateGroup(): ?EstimateGroup
    {
        return isset($this->estimateOfPrintedSheet)
            ? $this->estimateOfPrintedSheet->getEstimateGroup()
            : null
        ;
    }

    /**
     * Returns an instance of the Errors of the calculation.
     *
     * @return Errors
     */
    public function getErrors(): Errors
    {
        return $this->errors;
    }

    /**
     * Returns an instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'intervals' => $this->intervals->toArray(),
            'extra_charge' => $this->extraCharge->toArray(),
            'min_price' => $this->minPrice->toArray(),
        ];
    }
}
