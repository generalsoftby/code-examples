<?php

namespace App\Services\Calculators\FrontConverter\Contracts;

use App\Services\Calculators\Errors;
use App\Services\Calculators\Estimate;

/**
 * The interfaces is used to implemented a converter of data of the frontend.
 */
interface FrontendConverter
{
    /**
     * Sets an EstimateConverter.
     *
     * @param  EstimateConverter|null $estimateConverter
     * @return void
     */
    public function setEstimateConverter(?EstimateConverter $estimateConverter): void;

    /**
     * Returns an EstimateConverter.
     *
     * @return EstimateConverter|null
     */
    public function getEstimateConverter(): ?EstimateConverter;

    /**
     * Sets an Estimate to the current EstimateConverter.
     *
     * @param  Estimate $estimate
     * @return void
     */
    public function setEstimate(Estimate $estimate): void;

    /**
     * Returns an Estimate from the current EstimateConverter.
     *
     * @return Estimate|null
     */
    public function getEstimate(): ?Estimate;

    /**
     * Sets an ErrorsConverter.
     *
     * @param  ErrorsConverter $errorsConverter
     * @return void
     */
    public function setErrorsConverter(ErrorsConverter $errorsConverter): void;

    /**
     * Returns an ErrorsConverter.
     *
     * @return ErrorsConverter
     */
    public function getErrorsConverter(): ErrorsConverter;

    /**
     * Sets errors to the current ErrorsConverter.
     *
     * @param  Errors $errors
     * @return void
     */
    public function setErrors(Errors $errors): void;

    /**
     * Returns errors from the current ErrorsConverter.
     *
     * @return Errors|null
     */
    public function getErrors(): ?Errors;

    /**
     * Converts data to an array of the format of the specification:
     * an array with strings.
     *
     * @return array|string[]
     */
    public function toArrayOfSpecification(): array;

    /**
     * Converts data to the form of the calculator interface of React frontend.
     *
     * @return array
     */
    public function toArrayOfReactFE(): array;
}
