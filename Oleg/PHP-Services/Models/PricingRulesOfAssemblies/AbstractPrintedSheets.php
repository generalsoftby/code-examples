<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies;

use App\Services\Calculators\Errors;
use App\Services\Calculators\Estimate;
use App\Services\Calculators\EstimateGroup;
use App\Model\Calculator\PrintedSheetCollection;
use App\Model\Calculator\CalculatorPrintedSheet as PrintedSheet;
use App\Model\Calculator\PricingRulesOfAssemblies\Estimates\EstimateOfPrintedSheet;
use App\Model\Calculator\PricingRulesOfAssemblies\Estimates\EstimateOfPrintedSheetCollection;
use App\Model\Calculator\PricingRulesOfAssemblies\Values\ValuesOfPrintedSheet;
use App\Services\Calculators\Error;

/**
 * The abstract class of pricing rules for different printed sheets..
 */
abstract class AbstractPrintedSheets implements PricingRule, Preloading, \Countable, \Iterator
{
    use ThrowingExceptionOfInvalidValueKeeper;

    /**
     * Sheets with pricing rules.
     *
     * @var SheetKeeper|SheetWithRules[]
     */
    protected $sheetKeeper;

    /**
     * An instance of the EstimateGroup.
     *
     * @var EstimateGroup
     */
    protected $estimateGroup;

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
     * Initializes an instance of the class using rules for sheets.
     *
     * @param array|null $rules
     */
    public function __construct(array $rules = null)
    {
        $this->sheetKeeper = new SheetKeeper;
        $this->errors = new Errors;
        $this->throwExceptions = config('app.debug');

        $this->fillFromArray($rules ?? []);
    }

    /**
     * Returns a SheetKeeper.
     *
     * @return SheetKeeper
     */
    public function getSheetKeeper(): SheetKeeper
    {
        return $this->sheetKeeper;
    }

    /**
     * Returns a number of sheets.
     *
     * @return int
     */
    public function count(): int
    {
        return $this->sheetKeeper->count();
    }

    /**
     * Resets the current position.
     */
    public function rewind(): void
    {
        $this->sheetKeeper->rewind();
    }

    /**
     * Returns the current SheetWithRules.
     *
     * @return SheetWithRules|null
     */
    public function current(): SheetWithRules
    {
        return $this->sheetKeeper->current();
    }

    /**
     * Returns the current position.
     *
     * @return int
     */
    public function key(): int
    {
        return $this->sheetKeeper->key();
    }

    /**
     * The pointer moves to the next position.
     */
    public function next(): void
    {
        $this->sheetKeeper->next();
    }

    /**
     * Checks if the current sheet exists.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return $this->sheetKeeper->valid();
    }

    /**
     * Fills the instance from an array with rules.
     *
     * @param  array  $rules
     * @return void
     */
    public function fillFromArray(array $rules): void
    {
        /** @var array|SheetWithRules[] $sheets **/
        $sheets = $this->makeSheetsWithRulesFromArray($rules['sheets'] ?? []);
        $this->sheetKeeper->setSheetsWithRules($sheets);
        // NOTE: Printed sheets must be loaded by manual.
    }

    /**
     * Loads printed sheets to the instance.
     *
     * @return void
     */
    public function loadData(): void
    {
        /** @var array|int[] $ids */
        $ids = $this->getIDsOfSheets();

        /** @var PrintedSheetCollection $printedSheets */
        $printedSheets = PrintedSheet::find($ids);
        $this->setPrintedSheetCollection($printedSheets);
    }

    /**
     * Loads printed sheets to the instance. It is a synonym for loadData().
     *
     * @uses self::loadData
     *
     * @return void
     */
    public function loadPrintedSheets(): void
    {
        $this->loadData();
    }

    /**
     * Makes sheets with rules from an array with sheets and rules.
     * Creates rules only when sheets have a type.
     *
     * @param  array  $sheets
     * @return array|SheetWithRules[]
     */
    public function makeSheetsWithRulesFromArray(array $sheets): array
    {
        /** @var array $sheetsWithTypes **/
        $sheetsWithTypes = array_filter($sheets, function ($sheet) {
            return isset($sheet['type']);
        });

        return array_map(function ($sheet) {
            /** @var string $type **/
            $type = $sheet['type'];

            /** @var array $rules **/
            $rules = $sheet[$type] ?? [];

            /** @var PricingRule $rule **/
            $rule = $this->newPricingRuleFromArray($type, $rules);

            return new SheetWithRules($sheet['printed_sheet_id'], $type, $rule);
        }, $sheetsWithTypes);
    }

    /**
     * Initializes a new rules by the given type with the given rules.
     *
     * @param  string $type
     * @param  array  $rules
     * @return PricingRule
     *
     * @throws UnsupportedPricingRuleException
     */
    public function newPricingRuleFromArray(string $type, array $rules): PricingRule
    {
        throw new UnsupportedPricingRuleException($type);
    }

    /**
     * Checks whether the sheets are ready for the calculation.
     * Returns true if it true else returns a state of the error
     * in the form of a number.
     *
     * @param  bool $allSheetsMustBeReady
     * @return int|bool
     */
    public function areReady(bool $allSheetsMustBeReady = true)
    {
        return $this->sheetKeeper->areReady($allSheetsMustBeReady);
    }

    /**
     * Checks whether the settings of the pricing rule are configured.
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return $this->areReady();
    }

    /**
     * Returns IDs of sheets.
     *
     * @return array|int[]
     */
    public function getIDsOfSheets(): array
    {
        return $this->sheetKeeper->getIDsOfSheets();
    }

    /**
     * Removes sheets without printed sheet. Returns a number of removed sheets.
     *
     * @return int
     */
    public function removeSheetsWithoutPrintedSheet(): int
    {
        return $this->sheetKeeper->removeSheetsWithoutPrintedSheet();
    }

    /**
     * Sets a PrintedSheetCollection to the SheetKeeper.
     *
     * @param  PrintedSheetCollection|Sheet[] $collection
     * @return void
     */
    public function setPrintedSheetCollection(PrintedSheetCollection $collection): void
    {
        $this->sheetKeeper->setPrintedSheetCollection($collection);
    }

    /**
     * Gets a PrintedSheetCollection from the SheetKeeper.
     *
     * @param PrintedSheetCollection|Sheet[]
     */
    public function getPrintedSheetCollection(): PrintedSheetCollection
    {
        return $this->sheetKeeper->getPrintedSheetCollection();
    }

    /**
     * Filters sheets by a intersection the sheets with arrays of IDs.
     *
     * @param array $arraysWithIds  Arrays with IDs of sheets.
     */
    public function filterByIntersectionWithArraysOfIds(array $arraysWithIds): void
    {
        $this->sheetKeeper = $this->sheetKeeper->filterByIntersectionWithArraysOfIds($arraysWithIds);
    }

    /**
     * Filters sheets by the given size of a product.
     *
     * @param int $height
     * @param int $width
     */
    public function filterByProductSize(int $height, int $width): void
    {
        $this->sheetKeeper = $this->sheetKeeper->filterByProductSize($height, $width);
    }

    /**
     * Returns allowed types of ValueKeeper.
     *
     * @return array|string[]
     */
    abstract public function getAllowedTypesOfValueKeeper(): array;

    /**
     * Resets results of the last calculation.
     *
     * @return void
     */
    public function resetCalculation(): void
    {
        $this->estimateGroup = null;
        $this->errors->clean();
    }

    /**
     * Calculates a product price by input values.
     *
     * @param  ValuesOfPrintedSheet $values Values for calculation.
     * @return bool
     *
     * @throws InvalidValueKeeperException If $values is an unsupported class.
     */
    public function calculate(ValueKeeper $valueKeeper): bool
    {
        $this->resetCalculation();

       if (!$this->isValueKeeperAllowed($valueKeeper)) {
           return $this->invalidValueKeeper($valueKeeper);
       }

        /** @var EstimateOfPrintedSheet|null $estimateOfOptimalPrintedSheet **/
        $estimateOfOptimalPrintedSheet = $this->findOptimalPrintedSheet($valueKeeper);

        if (! $estimateOfOptimalPrintedSheet) {
            return $this->optimalPrintedSheetNotFound();
        }

        $this->updateEstimateGroup($estimateOfOptimalPrintedSheet->getEstimateGroup());

        return true;
    }

    /**
     * Checks whether the given ValueKeeper is allowed.
     *
     * @param  ValueKeeper $valueKeeper
     * @return bool
     */
    public function isValueKeeperAllowed(ValueKeeper $valueKeeper): bool
    {
        return $valueKeeper instanceof ValuesOfPrintedSheet;
    }

    /**
     * Returns an optimal printed sheet.
     *
     * @param  ValuesOfPrintedSheet $values
     * @return EstimateOfPrintedSheet|null
     */
    public function findOptimalPrintedSheet(ValuesOfPrintedSheet $values): ?EstimateOfPrintedSheet
    {
        /** @var EstimateOfPrintedSheetCollection $estimates **/
        $estimates = $this->calculateCostsOfPrintedSheets($values);
        // TODO Каждое значение должно попасть в "отладочную" часть расчета

        // TODO Нужно вернуть оптимальный печатный лист с его расчетом, от и до.
        // В зависимости от используемого правила расчета
        // там хранятся разные данные:
        // Правило, по котому был реализован расчет,
        // Конечное значение.
        // Все используемые переменные во время расчета.
        // Желательно, чтобы все это могло конвертироваться в массив,
        // чтобы потом передать на фронт.
        // Также эти данные будут использоваться для Estimate.

        return $estimates->getOptimalEstimate();
    }

    /**
     * Calculates costs of printed sheets.
     *
     * @param  ValuesOfPrintedSheet $values
     * @return EstimateOfPrintedSheetCollection
     */
    public function calculateCostsOfPrintedSheets(ValuesOfPrintedSheet $values): EstimateOfPrintedSheetCollection
    {
        $estimates = new EstimateOfPrintedSheetCollection;

        /** @var SheetWithRules $sheet **/
        foreach ($this as $sheet) {
            $values->setSheet($sheet->getPrintedSheet()->instance);

            /** @var PricingRuleOfPrintedSheets $rule **/
            $rule = $sheet->getPricingRule();

            if ($rule->calculate($values)) {
                $estimates->push($rule->getEstimateOfPrintedSheet());
            } else {
                // TODO Добавить отладочные ошибки расчета.
                // Только для главного Estimate.
                // Указать название листа, другие параметры и задать ошибки.
            }
        }

        return $estimates;
    }

    /**
     * Updates the current EstimateGroup.
     *
     * @param  EstimateGroup $group
     * @return void
     */
    protected function updateEstimateGroup(EstimateGroup $group): void
    {
        $this->estimateGroup = $group;
    }

    /**
     * Returns an instance of the Estimate of the calculation.
     *
     * @return Estimate
     */
    public function getEstimateGroup(): ?EstimateGroup
    {
        return $this->estimateGroup;
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
     * Adds the error to the errors. Returns 'false'.
     *
     * @return bool
     *
     * @throws InvalidValueKeeperException
     */
    protected function invalidValueKeeper(ValueKeeper $valueKeeper): bool
    {
        $this->throwInvalidValueKeeper($valueKeeper, $this->getAllowedTypesOfValueKeeper());
        $this->errors->add(trans('calculator_errors.value_keeper_is_invalid'), Error::SYSTEM_ERROR);

        return false;
    }

    /**
     * Adds the error to the errors. Returns 'false'.
     *
     * @return bool
     */
    protected function optimalPrintedSheetNotFound(): bool
    {
        $this->errors->add(trans('calculator_errors.optimal_printed_sheet_not_found'), Error::CALCULATION_ERROR);

        return false;
    }

    /**
     * Returns an array with rules for sheets.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'sheets' => $this->sheetKeeper->toArray(),
        ];
    }
}
