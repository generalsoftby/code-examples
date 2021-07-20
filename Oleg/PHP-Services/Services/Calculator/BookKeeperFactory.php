<?php

namespace App\Services\Calculators;

use App\Model\Calculator\BookKeepers\BookKeeper;
use App\Model\Calculator\Calculator;
use App\Services\Calculators\Contracts\BookKeeperMapping;

/**
 * Initializes instances of BookKeeper by instances of Calculator.
 */
class BookKeeperFactory
{
    /**
     * An instance of BookKeeperMapping.
     *
     * @var BookKeeperMapping
     */
    protected $bookKeeperMapping;

    /**
     * Initializes instances of BookKeeper of calculators.
     *
     * @var array|BookKeeper[]
     */
    protected $instances = [];

    /**
     * Initializes an instance of the class.
     *
     * @param BookKeeperMapping $bookKeeperMapping
     */
    public function __construct(BookKeeperMapping $bookKeeperMapping)
    {
        $this->bookKeeperMapping = $bookKeeperMapping;
    }

    /**
     * Returns a class name of a BookKeeper for a calculator by its ID.
     *
     * @param  int    $type
     * @return string|null
     */
    public function getClassNameOfBookKeeperByCalculatorType(int $type): ?string
    {
        return $this->bookKeeperMapping->getClassNameByType($type);
    }

    /**
     * Returns an array with class names of implemented BookKeepers.
     *
     * @return array|string[]
     */
    public function getClassNamesOfBookKeepers(): array
    {
        return $this->bookKeeperMapping->getClassNames();
    }

    /**
     * Creates a new instance of BookKeeper by the given calculator.
     *
     * @param  Calculator $calculator
     * @return BookKeeper|null
     */
    public function newBookKeeper(Calculator $calculator): ?BookKeeper
    {
        return $this->bookKeeperMapping->newBookKeeper($calculator->type_id, $calculator);
    }

    /**
     * Checks whether a BookKeeper of the given calculator exists.
     *
     * @return bool
     */
    public function doesBookKeeperExist(Calculator $calculator): bool
    {
        return isset($this->instances[$calculator->id]);
    }

    /**
     * Returns an instance of BookKeeper by the given calculator.
     *
     * @return BookKeeper|null
     */
    public function getBookKeeper(Calculator $calculator): ?BookKeeper
    {
        if (!$this->doesBookKeeperExist($calculator)) {
            $this->instances[$calculator->id] = $this->newBookKeeper(($calculator));
        }

        return $this->instances[$calculator->id] ?? null;
    }
}
