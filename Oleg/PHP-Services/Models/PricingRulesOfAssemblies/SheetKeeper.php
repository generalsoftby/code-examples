<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies;

use App\Model\Calculator\PrintedSheetCollection;
use App\Model\Calculator\CalculatorPrintedSheet as PrintedSheet;

/**
 * The class for keeping and handling sheets.
 */
class SheetKeeper implements \Countable, \Iterator
{
    /**
     * The state of the error when there are no sheets.
     *
     * @var int
     */
    const NO_SHEETS = 1;

    /**
     * The state of the error when there are not all sheets are ready.
     *
     * @var int
     */
    const NOT_ALL_SHEETS_ARE_READY = 2;

    /**
     * The state of the error when there are no ready sheets.
     *
     * @var int
     */
    const NO_READY_SHEETS = 3;

    /**
     * Sheets with rules.
     *
     * @var array|SheetWithRules[]
     */
    protected $sheets;

    /**
     * A PrintedSheetCollection.
     *
     * @var PrintedSheetCollection|PrintedSheet[]
     */
    protected $printedSheetCollection;

    /**
     * Initializes an instance of the class with sheets.
     *
     * @param array|SheetWithRules[] $sheets
     */
    function __construct(array $sheets = [])
    {
        $this->setSheetsWithRules($sheets);
    }

    /**
     * Returns a number of sheets.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->sheets);
    }

    /**
     * Resets the current position.
     */
    public function rewind()
    {
        reset($this->sheets);
    }

    /**
     * Returns the current SheetWithRules.
     *
     * @return SheetWithRules|null
     */
    public function current(): ?SheetWithRules
    {
        /** @var SheetWithRules|null $sheet **/
        $sheet = current($this->sheets);

        return is_bool($sheet)
            ? null
            : $sheet
        ;
    }

    /**
     * Returns the current position.
     *
     * @return int
     */
    public function key(): ?int
    {
        return key($this->sheets);
    }

    /**
     * The pointer moves to the next position.
     */
    public function next(): ?SheetWithRules
    {
        /** @var SheetWithRules|null $sheet **/
        $sheet = next($this->sheets);

        return is_bool($sheet)
            ? null
            : $sheet
        ;
    }

    /**
     * Checks if the current sheet exists.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return current($this->sheets) !== false;
    }

    /**
     * Sets sheets with rules.
     *
     * @param array|SheetWithRules[] $sheets
     */
    public function setSheetsWithRules(array $sheets)
    {
        $this->sheets = array_filter($sheets, function ($sheet) {
            return $sheet instanceof SheetWithRules;
        });
    }

    /**
     * Returns sheets with rules.
     *
     * @return array|SheetWithRules[]
     */
    public function getSheetsWithRules(): array
    {
        return $this->sheets;
    }

    /**
     * Creates and adds a new SheetWithRules to the collection.
     *
     * @param  int            $id   An ID of the sheet.
     * @param  string         $type A type of pricing rule.
     * @param  PricingRule    $rule A PricingRule.
     * @return SheetWithRules
     */
    public function addSheetWithRules(int $id, string $type, PricingRule $rule): SheetWithRules
    {
        $sheetWithRules = new SheetWithRules($id, $type, $rule);

        $this->sheets[] = $sheetWithRules;

        return $sheetWithRules;
    }

    /**
     * Removes a sheet by its ID.
     *
     * @param  int    $id
     * @return void
     */
    public function removeSheetWithRulesById(int $id): void
    {
        /** @var array $sheetToDelete Contains one sheet or nothing **/
        $sheetToDelete = array_filter($this->sheets, function (SheetWithRules $sheet) use ($id) {
            return $id === $sheet->getId();
        });

        if (count($sheetToDelete)) {
            unset($this->sheets[key($sheetToDelete)]);
        }
    }

    /**
     * Returns an array with IDs of sheets.
     *
     * @return array|int[]
     */
    public function getIDsOfSheets(): array
    {
        /** @var array|int[] $ids **/
        $ids = array_map(function (SheetWithRules $sheet) {
            return $sheet->getId();
        }, $this->sheets);

        return array_values($ids);
    }

    /**
     * Sets a PrintedSheetCollection.
     * If $fillSheetsWithRules is true, then fill sheets with rules
     * with printed sheets of the collection.
     * If $removeNonexistenSheets is true, then nonexisten sheets with rules
     * will be removed.
     *
     * @param PrintedSheetCollection|PrintedSheet[] $collection
     * @param bool                                  $fillSheetsWithRules
     * @param bool                                  $removeNonexistenSheets
     */
    public function setPrintedSheetCollection(
        PrintedSheetCollection $collection,
        bool $fillSheetsWithRules = true,
        bool $removeNonexistenSheets = true
    ) {
        $this->printedSheetCollection = $collection;

        if ($fillSheetsWithRules) {
            $this->fillSheetsWithRulesWithPrintedSheets();
        }

        if ($removeNonexistenSheets) {
            $this->removeNonexisten();
        }
    }

    /**
     * Gets a PrintedSheetCollection with Sheets.
     *
     * @param PrintedSheetCollection|PrintedSheet[] $collection
     */
    public function getPrintedSheetCollection(): PrintedSheetCollection
    {
        return $this->printedSheetCollection;
    }

    /**
     * Fills sheets with rules with printed sheets
     * from the collection of printed sheets.
     *
     * @return void
     */
    public function fillSheetsWithRulesWithPrintedSheets(): void
    {
        foreach ($this->sheets as $sheet) {
            /** @var PrintedSheet|null $printedSheet **/
            $printedSheet = $this->printedSheetCollection->getById($sheet->getId());

            if ($printedSheet) {
                $sheet->setPrintedSheet($printedSheet);
            }
        }
    }

    /**
     * Defines $printedSheetCollection from sheets with rules.
     *
     * @return void
     */
    public function definePrintedSheetCollectionFromSheetsWithRules(): void
    {
        $this->printedSheetCollection = $this->getPrintedSheetCollectionFromSheetsWithRules();
    }

    /**
     * Returns a PrintedSheetCollection with PrintedSheets from sheets with rules.
     *
     * @return PrintedSheetCollection|PrintedSheet[]
     */
    public function getPrintedSheetCollectionFromSheetsWithRules(): PrintedSheetCollection
    {
        return new PrintedSheetCollection($this->getPrintedSheetsFromSheetsWithRules());
    }

    /**
     * Returns an array with printed sheets of sheets with rules.
     *
     * @return array|PrintedSheet[]
     */
    public function getPrintedSheetsFromSheetsWithRules(): array
    {
        /** @var array|SheetWithRules[] $sheetsWithPrintedSheets **/
        $sheetsWithPrintedSheets = array_values($this->getSheetsWithPrintedSheets());

        return array_map(function (SheetWithRules $sheet) {
            return $sheet->getPrintedSheet();
        }, $sheetsWithPrintedSheets);
    }

    /**
     * Removes nonexisten sheets.
     *
     * @return int
     */
    public function removeNonexisten(): int
    {
        /** @var array|int[] $IDsOfNonexistenSheets **/
        $IDsOfNonexistenSheets = $this->getIDsOfNonexistenSheets();

        foreach ($IDsOfNonexistenSheets as $id) {
            $this->removeSheetWithRulesById($id);
        }

        return count($IDsOfNonexistenSheets);
    }

    /**
     * Counts sheets without printed sheet and returns the amount.
     *
     * @return int
     */
    public function countSheetsWithoutPrintedSheet(): int
    {
        return count($this->getSheetsWithoutPrintedSheet());
    }

    /**
     * Returns an array with sheets with rules by their IDs.
     *
     * @param  array|int[] $ids
     * @return array|SheetWithRules[]
     */
    public function getSheetsWithRulesByIds(array $ids): array
    {
        return array_filter($this->sheets, function (SheetWithRules $sheet) use ($ids) {
            return in_array($sheet->getId(), $ids);
        });
    }

    /**
     * Returns sheets without printed sheet.
     * Indexes of sheets are saved.
     *
     * @return array|SheetWithRules[]
     */
    public function getSheetsWithoutPrintedSheet(): array
    {
        return array_filter($this->sheets, function (SheetWithRules $sheet) {
            return ! $sheet->hasPrintedSheet();
        });
    }

    /**
     * Returns sheets that contains a printed sheet.
     * Indexes of sheets are saved.
     *
     * @return array|SheetWithRules[]
     */
    public function getSheetsWithPrintedSheets(): array
    {
        return array_filter($this->sheets, function (SheetWithRules $sheet) {
            return $sheet->hasPrintedSheet();
        });
    }

    /**
     * Removes sheets without printed sheet. Returns a number of removed sheets.
     *
     * @return int
     */
    public function removeSheetsWithoutPrintedSheet(): int
    {
        /** @var array|SheetWithRules[] $sheetsWithoutPrintedSheet **/
        $sheetsWithoutPrintedSheet = $this->getSheetsWithoutPrintedSheet();

        foreach ($sheetsWithoutPrintedSheet as $index => $sheet) {
            unset($this->sheets[$index]);
        };

        return count($sheetsWithoutPrintedSheet);
    }

    /**
     * Returns IDs of nonexisten sheets.
     *
     * @return array|int[]
     */
    public function getIDsOfNonexistenSheets(): array
    {
        /** @var array|int[] $IDsOfPrintedSheets **/
        $IDsOfPrintedSheets = $this->printedSheetCollection->pluck('id')->toArray();

        return array_diff($this->getIDsOfSheets(), $IDsOfPrintedSheets);
    }

    /**
     * Returns an intersection with arrays of IDs of sheets.
     *
     * @param  array $arraysWithIds
     * @return array|int[]
     */
    public function getIntersectionWithArraysOfIds(array $arraysWithIds): array
    {
        return count($arraysWithIds)
            ? array_intersect($this->getIDsOfSheets(), ...$arraysWithIds)
            : $this->getIDsOfSheets()
        ;
    }

    /**
     * Filters sheets by a intersection the sheets with arrays of IDs.
     *
     * @param  array $arraysWithIds Arrays with IDs of sheets.
     * @return self
     */
    public function filterByIntersectionWithArraysOfIds(array $arraysWithIds): self
    {
        /** @var array|int[] $IDsOfAllowedSheets **/
        $IDsOfAllowedSheets = $this->getIntersectionWithArraysOfIds($arraysWithIds);

        return $this->filterByIDsOfSheets($IDsOfAllowedSheets);
    }

    /**
     * Filters sheets by their IDs.
     *
     * @param  array|int[]  $ids
     * @return SheetKeeper
     */
    public function filterByIDsOfSheets(array $ids): self
    {
        /** @var array|SheetWithRules[] $sheets **/
        $sheets = $this->getSheetsWithRulesByIds($ids);

        $sheetKeeper = new self($sheets);
        $sheetKeeper->definePrintedSheetCollectionFromSheetsWithRules();

        return $sheetKeeper;
    }

    /**
     * Filters sheets by the given type.
     *
     * @param  string $type
     * @return SheetKeeper
     */
    public function filterByType(string $type): self
    {
        $sheetsByType = array_filter($this->sheets, function (SheetWithRules $sheet) use ($type) {
            return $sheet->getType() === $type;
        });

        return new self($sheetsByType);
    }

    /**
     * Filters sheets by the given size of a product.
     *
     * @param  int    $height
     * @param  int    $width
     * @return SheetKeeper
     */
    public function filterByProductSize(int $height, int $width): self
    {
        /** @var PrintedSheetCollection|PrintedSheet[] $printedSheetCollection **/
        $printedSheetCollection = $this->printedSheetCollection->canBeUsed($height, $width);

        return $this->filterByIDsOfSheets($printedSheetCollection->getIds());
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
        if ($this->count() === 0) {
            return self::NO_SHEETS;
        } else {
            /** @var array|SheetWithRules[] $readySheets **/
            $readySheets = array_filter($this->sheets, function (SheetWithRules $sheet) {
                return $sheet->isReady();
            });

            if ($allSheetsMustBeReady && count($readySheets) !== $this->count()) {
                return self::NOT_ALL_SHEETS_ARE_READY;
            } elseif (! count($readySheets)) {
                return self::NO_READY_SHEETS;
            }
        }

        return true;
    }

    /**
     * Returns an instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_map(function (SheetWithRules $sheet) {
            return $sheet->toArray();
        }, $this->sheets);
    }
}
