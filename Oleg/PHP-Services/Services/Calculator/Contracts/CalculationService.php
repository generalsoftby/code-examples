<?php

namespace App\Services\Calculators\Contracts;

use App\Services\Calculators\Blocks\UserBlocks;
use App\Services\Calculators\Estimate;
use App\Services\Calculators\Errors;
use App\Services\Calculators\OptionInputs;

/**
 * The service calculates a price of products or a service.
 */
interface CalculationService
{
    /**
     * Returns options with their values for the calculation interface
     * by an calculator ID and an array with attribute value IDs.
     *
     * @param  int $calculatorId
     * @param  array|int[] $attributeValueIDs
     * @return OptionInputs|null
     */
    public function getOptionInputs(int $calculatorId, array $attributeValueIDs): ?OptionInputs;

    /**
     * Calculates prices by the given user blocks.
     *
     * @param  UserBlocks $blocks
     * @return bool
     */
    public function calculateByBlocks(UserBlocks $userBlocks): bool;

    /**
     * Returns an estimate of the last calculation or returns null.
     * The estimate contains all added data of the calculation.
     *
     * @return Estimate|null
     */
    public function getEstimate(): ?Estimate;

    /**
     * Checks whether errors are exist.
     *
     * @return bool
     */
    public function hasErrors(): bool;

    /**
     * Returns a collection of errors that given in the calculating.
     *
     * @return Errors
     */
    public function getErrors(): Errors;
}
