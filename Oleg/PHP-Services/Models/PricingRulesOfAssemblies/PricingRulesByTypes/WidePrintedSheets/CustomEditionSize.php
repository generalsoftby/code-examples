<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\PricingRulesByTypes\WidePrintedSheets;

use App\Services\Calculators\Error;
use App\Services\Calculators\Errors;
use App\Services\Calculators\EstimateGroup;
use App\Model\Calculator\PricingRulesOfAssemblies\Cost;
use App\Model\Calculator\PricingRulesOfAssemblies\Estimates\EstimateOfPrintedSheet;
use App\Model\Calculator\PricingRulesOfAssemblies\Estimates\WidePrintedSheet\EstimateOfPrintedSheetByCustomEditionSize;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRulesByTypes\PricingRuleWithInitializationOfAdditionalPrices;
use App\Model\Calculator\PricingRulesOfAssemblies\IntervalCollection;
use App\Model\Calculator\PricingRulesOfAssemblies\InvalidValueKeeperException;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRulesByTypes\GettingSettingStandardPricingRule;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRulesByTypes\InitializationOfAdditionalPrices;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRulesByTypes\InitializationOfIntervals;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRulesByTypes\MethodsOfIntervals;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRuleWithIntervals;
use App\Model\Calculator\PricingRulesOfAssemblies\ThrowingExceptionOfInvalidValueKeeper;
use App\Model\Calculator\PricingRulesOfAssemblies\ValueKeeper;
use App\Model\Calculator\PricingRulesOfAssemblies\Values\ValuesOfWideSheets;

/**
 * The class of pricing rules for the 'custom edition size' type.
 */
class CustomEditionSize implements
    PricingRuleOfWidePrintedSheets,
    PricingRuleWithIntervals,
    PricingRuleWithInitializationOfAdditionalPrices
{
    use GettingSettingStandardPricingRule;
    use ThrowingExceptionOfInvalidValueKeeper;
    use InitializationOfAdditionalPrices;
    use InitializationOfIntervals;
    use MethodsOfIntervals;

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
     * @param  ValueKeeper|ValuesOfWideSheets $keeper
     * @return bool
     *
     * @throws InvalidValueKeeperException If $keeper is an unsupported class.
     */
    public function calculate(ValueKeeper $keeper): bool
    {
        if (! ($keeper instanceof ValuesOfWideSheets)) {
            return $this->invalidValueKeeper($keeper);
        }

        /** @var float $numberOfMaterial A number of the material for search an appropriate interval. **/
        $numberOfMaterial = $keeper->getNumberOfMaterialByUnit();
        $appropriateInterval = $this->intervals->getIntervalByValue($numberOfMaterial);

        if (! $appropriateInterval) {
            return $this->appropriateIntervalNotFound($keeper->getSheet()->getName());
        }

        $algorithmWithUnit = $keeper->getAlgorithmWithUnit();
        $costOfProducts = $algorithmWithUnit->calculatePrice(
            $keeper->getNumberOfProducts(),
            $numberOfMaterial,
            $appropriateInterval->getValueByCurrency(),
            $appropriateInterval->getCostUnit()
        );

        if ($costOfProducts === null) {
            return $this->costOfProductIsNotDefined($appropriateInterval->getCostUnit(), $keeper->getAlgorithmType());
        }

        $costOfProductsWithExtraCharge = $costOfProducts + $this->getExtraCharge()->getValueByCurrency();
        $priceOfProducts = 0.0;

        // It is true when a cost of products with extra charge
        // less then a minimal price of products.
        // This means the price of products was changed.
        $ruleOfMinimumWasUsed = false;

        if ($costOfProductsWithExtraCharge < $this->getMinPrice()->getValueByCurrency()) {
            $priceOfProducts = $this->getMinPrice()->getValueByCurrency();
            $ruleOfMinimumWasUsed = true;
        } else {
            $priceOfProducts = $costOfProductsWithExtraCharge;
        }

        $this->estimateOfPrintedSheet = new EstimateOfPrintedSheetByCustomEditionSize(
            $this->name,
            $keeper,
            $priceOfProducts,
            $costOfProducts,
            $this->getExtraCharge(),
            $costOfProductsWithExtraCharge,
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
     * Throws the exception or adds the error to the errors.
     * Returns 'false'.
     *
     * @param  ValueKeeper $keeper
     * @return bool
     *
     * @throws InvalidValueKeeperException
     */
    protected function invalidValueKeeper(ValueKeeper $keeper): bool
    {
        $this->throwInvalidValueKeeper($keeper, [ValuesOfWideSheets::class]);
        $this->errors->add(trans('calculator_errors.value_keeper_is_invalid'), Error::SYSTEM_ERROR);

        return false;
    }

    /**
     * Adds the error to the errors.
     * Returns 'false'.
     *
     * @param  string $nameOfPrintedSheet
     * @return bool
     */
    protected function appropriateIntervalNotFound(string $nameOfPrintedSheet): bool
    {
        $this->errors->add(
            trans('calculator_errors.appropriate_interval_of_custom_intervals_for_printed_sheet_not_found', [
                'name' => $nameOfPrintedSheet
            ]),
            Error::CALCULATION_ERROR
        );

        return false;
    }

    /**
     * Adds the error to the errors.
     * Returns 'false'.
     *
     * @param  string $unit A unit of the interval.
     * @param  string $algorithmType
     * @return bool
     */
    protected function costOfProductIsNotDefined(string $unit, string $algorithmType): bool
    {
        $this->errors->add(
            trans('calculator_errors.condition_to_calculate_price_of_running_meter_is_not_defined', [
                'unit' => trans('units.' . $unit),
                'algorithm' => trans('individual_settings.' . $algorithmType),
            ]),
             Error::SYSTEM_ERROR
        );

        return false;
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
