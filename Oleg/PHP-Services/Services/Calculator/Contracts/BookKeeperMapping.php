<?php

namespace App\Services\Calculators\Contracts;

use App\Model\Calculator\BookKeepers\BookKeeper;
use App\Model\Calculator\Calculator;

/**
 * The interface to implement mapping for BookKeepers of the calculator.
 */
interface BookKeeperMapping
{
    /**
     * Returns a class name of a BookKeeper for a calculator by its ID.
     *
     * @param  int    $type
     * @return string|null
     */
    public function getClassNameByType(int $type): ?string;

    /**
     * Checks whether the mapping has a class name with the given calculator type.
     *
     * @param  string $type
     * @return bool
     */
    public function hasClassName(string $type): bool;

    /**
     * Returns an array with class names of BookKeepers.
     *
     * @return array|string[]
     */
    public function getClassNames(): array;

    /**
     * Creates a new instance of BookKeeper by the given type and calculator.
     *
     * @param  int $type
     * @param  Calculator $calculator
     * @return BookKeeper|null
     */
    public function newBookKeeper(int $type, Calculator $calculator): ?BookKeeper;
}
