<?php

namespace App\Services\Calculators\FrontConverter\Contracts;

use App\Services\Calculators\Estimate;

/**
 * The interface to implement a converter of an estimate.
 */
interface EstimateConverter
{
    /**
     * Sets a debug mode.
     *
     * @param  bool $debugMode
     * @return void
     */
    public function setDebugMode(bool $debugMode = true): void;

    /**
     * Returns a debug mode.
     *
     * @return bool
     */
    public function getDebugMode(): bool;

    /**
     * Sets the given Estimate.
     *
     * @param  Estimate $estimate
     * @return self
     */
    public function setEstimate(Estimate $estimate): self;

    /**
     * Returns an instance of Estimate.
     *
     * @return Estimate|null
     */
    public function getEstimate(): ?Estimate;

    /**
     * Checks whether the instance has an estimate.
     *
     * @return bool
     */
    public function hasEstimate(): bool;

    /**
     * Converts data to an array of the format of the specification:
     * an array with strings.
     *
     * @return array|string[]
     */
    public function toArrayOfSpecification(): array;

    /**
     * Converts data to an array of the format of React's front-end
     * of the calculator interface.
     *
     * @return array
     */
    public function toArrayOfReactFE(): array;
}
