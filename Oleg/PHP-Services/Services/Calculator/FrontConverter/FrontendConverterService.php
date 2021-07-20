<?php

namespace App\Services\Calculators\FrontConverter;

use App\Services\Calculators\Errors;
use App\Services\Calculators\Estimate;
use App\Services\Calculators\FrontConverter\Contracts\ErrorsConverter;
use App\Services\Calculators\FrontConverter\Contracts\EstimateConverter;
use App\Services\Calculators\FrontConverter\Contracts\FrontendConverter;

class FrontendConverterService
{
    /**
     * An instance of EstimateConverterService.
     *
     * @var EstimateConverterService
     */
    protected $estimateConverterService;

    /**
     * An instance of ErrorsConverter.
     *
     * @var ErrorsConverter
     */
    protected $errorsConverter;

    /**
     * An instance of FrontendConverter;
     *
     * @var FrontendConverter
     */
    protected $frontendConverter;

    /**
     * Initializes an instance of the class
     * with the given EstimateConverterService and the given ErrorsConverter.
     *
     * @param FrontendConverter        $frontendConverter
     * @param EstimateConverterService $estimateConverterService
     * @param ErrorsConverter          $errorsConverter
     */
    public function __construct(
        FrontendConverter $frontendConverter,
        EstimateConverterService $estimateConverterService,
        ErrorsConverter $errorsConverter
    ) {
        $this->frontendConverter = $frontendConverter;
        $this->estimateConverterService = $estimateConverterService;
        $this->errorsConverter = $errorsConverter;
    }

    /**
     * Converts the given instances to the form of Frontend
     * and returns an instance of FrontendConverter.
     *
     * @param  Estimate|null $estimate
     * @param  Errors $errors
     * @param  string|null $converterType
     * @return FrontendConverter
     */
    public function convert(
        ?Estimate $estimate,
        Errors $errors,
        string $converterType = null
    ): FrontendConverter {
        // Sets the given estimate and errors to the converters.
        if ($estimate) {
            /** @var EstimateConverter|null $estimateConverter */
            $estimateConverter = $this->estimateConverterService->convert($estimate, $converterType);
        }

        $this->errorsConverter->setInstanceOfErrors($errors);

        // Sets the converters to the forntend converter.
        $this->frontendConverter->setEstimateConverter($estimateConverter ?? null);
        $this->frontendConverter->setErrorsConverter($this->errorsConverter);

        return $this->frontendConverter;
    }
}
