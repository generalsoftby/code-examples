<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\PricingRulesByTypes\PrintedSheets;

use App\Support\Number;
use App\Services\Calculators\Error;
use App\Services\Calculators\Errors;
use App\Services\Calculators\EstimateGroup;
use App\Model\Calculator\PricingRulesOfAssemblies\Cost;
use App\Model\Calculator\PricingRulesOfAssemblies\Estimates\PrintedSheet\EstimateOfPrintedSheet;
use App\Model\Calculator\PricingRulesOfAssemblies\Estimates\PrintedSheet\EstimateOfPrintedSheetByWholesale;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRulesByTypes\GettingSettingStandardPricingRule;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRulesByTypes\PricingRuleWithInitializationOfAdditionalPrices;
use App\Model\Calculator\PricingRulesOfAssemblies\PricingRulesByTypes\InitializationOfAdditionalPrices;
use App\Model\Calculator\PricingRulesOfAssemblies\ThrowingExceptionOfInvalidValueKeeper;
use App\Model\Calculator\PricingRulesOfAssemblies\ValueKeeper;
use App\Model\Calculator\PricingRulesOfAssemblies\Values\ValuesOfSheet;

/**
 * The class for the 'Wholesale': downgrade the price when a number of products gets up.
 */
class Wholesale implements
    PricingRuleOfPrintedSheets,
    PricingRuleWithInitializationOfAdditionalPrices
{
    use GettingSettingStandardPricingRule, ThrowingExceptionOfInvalidValueKeeper, InitializationOfAdditionalPrices;

    const DEFAULT_VALUE_OF_START_COST_OF_PRINTED_SHEET = 0;

    const DEFAULT_CURRENCY_OF_START_COST_OF_PRINTED_SHEET = 'RUB';

    const DEFAULT_VALUE_OF_MIN_COST_OF_PRINTED_SHEET = 0;

    const DEFAULT_CURRENCY_OF_MIN_COST_OF_PRINTED_SHEET = 'RUB';

    /**
     * A name of the pricing.
     *
     * @var string
     */
    protected $name;

    /**
     * A coefficient for changing cost.
     *
     * @var float
     */
    protected $coefficient;

    /**
     * A start cost per a printed sheet.
     *
     * @var Cost
     */
    protected $startCostPerPrintedSheet;

    /**
     * A minimal cost per a printed sheet.
     *
     * @var Cost
     */
    protected $minCostPerPrintedSheet;

    /**
     * A number of printed sheets which may by printed in one working day.
     *
     * @var int
     */
    protected $numberOfPrintedSheetsPerDay;

    /**
     * Working on weekends.
     *
     * @var bool
     */
    protected $weekend;

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
     * An EstimateOfPrintedSheet.
     *
     * @var EstimateOfPrintedSheet|EstimateOfPrintedSheetByWholesale
     */
    protected $estimateOfPrintedSheet;

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
     * Initializes an instance of the class.
     *
     * @param array|null $rules
     */
    public function __construct(array $rules = null)
    {
        $this->errors = new Errors;
        $this->throwExceptions = config('app.debug');

        $this->fillFromArray($rules ?? []);
    }

    /**
     * Fills an instance with the given rules.
     *
     * @param  array $rules
     * @return void
     */
    public function fillFromArray(array $rules)
    {
        $this->name = $rules['name'] ?? trans('assemblies.default_name_of_pricing_rule');
        $this->coefficient = (float) (Number::normalizeFloat($rules['coefficient'] ?? 0));
        $this->startCostPerPrintedSheet = $this->newStartCostPerPrintedSheet($rules['start_cost_of_sheet'] ?? []);
        $this->minCostPerPrintedSheet = $this->newMinCostPrintedSheet($rules['min_cost_of_sheet'] ?? []);
        $this->numberOfPrintedSheetsPerDay = (int) ($rules['number_of_printed_sheets_in_day'] ?? 0);
        $this->weekend = isset($rules['weekend']) ? filter_var($rules['weekend'], FILTER_VALIDATE_BOOLEAN) : false;
        $this->extraCharge = $this->newExtraCharge($rules['extra_charge'] ?? []);
        $this->minPrice = $this->newMinPrice($rules['min_price'] ?? []);
    }

    /**
     * Returns a coefficient for changing cost.
     *
     * @return float
     */
    public function getCoefficient(): float
    {
        return $this->coefficient;
    }

    /**
     * Sets a coefficient for changing cost.
     *
     * @param  float $coefficient
     * @return void
     */
    public function setCoefficient(float $coefficient): void
    {
        $this->coefficient = $coefficient;
    }

    /**
     * Returns a start cost per a printed sheet.
     *
     * @return Cost
     */
    public function getStartCostPerPrintedSheet(): Cost
    {
        return $this->startCostPerPrintedSheet;
    }

    /**
     * Sets a Cost with a start cost per a printed sheet.
     *
     * @param  Cost $cost
     * @return void
     */
    public function setStartCostPerPrintedSheet(Cost $cost): void
    {
        $this->startCostPerPrintedSheet = $cost;
    }

    /**
     * Returns a minimal cost per unit (mm, cm, m).
     *
     * @return Cost
     */
    public function getMinimalCostPerPrintedSheet(): Cost
    {
        return $this->minCostPerPrintedSheet;
    }

    /**
     * Sets a Cost with a minimal cost per unit.
     *
     * @param  Cost $cost
     * @return void
     */
    public function setMinimalCostPerPrintedSheet(Cost $cost): void
    {
        $this->minCostPerPrintedSheet = $cost;
    }

    /**
     * Sets a number of printed sheets a day.
     *
     * @param  int $value
     * @return void
     */
    public function setNumberOfPrintedSheetsPerDay(int $value): void
    {
        $this->numberOfPrintedSheetsPerDay = $value;
    }

    /**
     * Returns a number of printed sheets which may by printed in one working day.
     *
     * @return int
     */
    public function getNumberOfPrintedSheetsPerDay(): int
    {
        return $this->numberOfPrintedSheetsPerDay;
    }

    /**
     * Checks whether the workers makes products on weekends.
     *
     * @return bool
     */
    public function worksOnWeekends(): bool
    {
        return $this->weekend;
    }

    /**
     * Sets a state of working on weekedns.
     *
     * @param  bool $state
     * @return void
     */
    public function setWeekend(bool $state): void
    {
        $this->weekend = $state;
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
        return true;
    }

    /**
     * Calculates a product price using the values for calculation.
     *
     * @param  ValueKeeper|ValuesOfSheet $keeper
     * @return bool
     *
     * @throws InvalidValueKeeperException  If $keeper is an unsupported class.
     */
    public function calculate(ValueKeeper $keeper): bool
    {
        if (!($keeper instanceof ValuesOfSheet)) {
            $this->throwInvalidValueKeeper($keeper, [ValuesOfSheet::class]);
            $this->errors->add(trans('calculator_errors.value_keeper_is_invalid'), Error::SYSTEM_ERROR);
            return false;
        }

        $ruleOfMinimalPriceWasUsed = false;
        $ruleOfMinimalCostPerUnitWasUsed = false;

        $costOfProducts = $this->calculateCostOfProducts($keeper);
        $originalCostOfProducts = $costOfProducts;

        $originalPriceOfProducts = $costOfProducts + $this->extraCharge->getValueByCurrency();

        /** @var float $priceOfProducts */
        $priceOfProducts = 0;
        $minPrice = $this->getMinPrice()->getValueByCurrency();

        // Uses the rule of a minimal price
        if ($originalPriceOfProducts < $minPrice) {
            $priceOfProducts = $minPrice;
            $ruleOfMinimalPriceWasUsed = true;
        } else {
            $priceOfProducts = $originalPriceOfProducts;
        }

        $numberOfPrintedSheets = $keeper->getNumberOfPrintedSheetsWithSheetsForFitting();

        $costPerPrintedSheet = $priceOfProducts / $numberOfPrintedSheets;
        $minCostPerPrintedSheet = $this->getMinimalCostPerPrintedSheet()->getValueByCurrency();

        // Uses the rule of minimal cost per a printed sheet
        if (round($costPerPrintedSheet, 2) < $minCostPerPrintedSheet) {
            $finalCostOfProducts = $minCostPerPrintedSheet * $numberOfPrintedSheets;
            $priceOfProducts = $costOfProducts + $this->extraCharge->getValueByCurrency();
            $ruleOfMinimalCostPerUnitWasUsed = true;
        } else {
            $finalCostOfProducts = $costOfProducts;
        }

        $this->estimateOfPrintedSheet = new EstimateOfPrintedSheetByWholesale(
            $this->name,
            $keeper,
            $priceOfProducts,
            $originalCostOfProducts,
            $originalPriceOfProducts,
            $finalCostOfProducts,
            $this->getExtraCharge(),
            $ruleOfMinimalPriceWasUsed,
            $ruleOfMinimalCostPerUnitWasUsed
        );

        return true;
    }

    /**
     * Calculates a cost of products.
     *
     * @param  ValuesOfSheet $keeper
     * @return float
     */
    protected function calculateCostOfProducts(ValuesOfSheet $keeper): float
    {
        /** @var int $numberOfMaterial **/
        $numberOfMaterial = $keeper->getNumberOfPrintedSheets();

        return $this->coefficient
            * pow($numberOfMaterial, 2)
            + $this->startCostPerPrintedSheet->getValueByCurrency()
            * $numberOfMaterial
        ;
    }

    /**
     * Returns an EstimateOfPrintedSheetByWholesale.
     *
     * @return EstimateOfPrintedSheet|EstimateOfPrintedSheetByWholesale
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
     * Initializes a Cost with a start cost of the printed sheet from an array with thier values.
     *
     * @param  array $values
     * @return Cost
     */
    public function newStartCostPerPrintedSheet(array $values): Cost
    {
        /** @var float $value */
        $value = isset($values['value'])
            ? Number::normalizeFloat($values['value'])
            : self::DEFAULT_VALUE_OF_START_COST_OF_PRINTED_SHEET
        ;

        return new Cost(
            $value,
            $values['currency'] ?? self::DEFAULT_CURRENCY_OF_START_COST_OF_PRINTED_SHEET
        );
    }

    /**
     * Initializes a Cost with a minimal cost of the printed sheet from an array with thier values.
     *
     * @param  array $values
     * @return Cost
     */
    public function newMinCostPrintedSheet(array $values): Cost
    {
        /** @var float $value */
        $value = isset($values['value'])
            ? Number::normalizeFloat($values['value']) : self::DEFAULT_VALUE_OF_MIN_COST_OF_PRINTED_SHEET
        ;

        return new Cost(
            $value,
            $values['currency'] ?? self::DEFAULT_CURRENCY_OF_MIN_COST_OF_PRINTED_SHEET
        );
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
            'coefficient' => $this->coefficient,
            'start_cost_of_sheet' => $this->startCostPerPrintedSheet->toArray(),
            'min_cost_of_sheet' => $this->minCostPerPrintedSheet->toArray(),
            'weekend' => $this->weekend,
            'number_of_printed_sheets_in_day' => $this->numberOfPrintedSheetsPerDay,
            'extra_charge' => $this->extraCharge->toArray(),
            'min_price' => $this->minPrice->toArray(),
        ];
    }
}
