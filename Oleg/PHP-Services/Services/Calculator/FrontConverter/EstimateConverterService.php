<?php

namespace App\Services\Calculators\FrontConverter;

use App\Services\Calculators\Estimate;
use App\Services\Calculators\FrontConverter\Contracts\CustomConverters;
use App\Services\Calculators\FrontConverter\Contracts\EstimateConverter;

/**
 * The class is used to convert results of calculation to the front format of data.
 */
class EstimateConverterService
{
    /**
     * An instance of the default converter.
     *
     * @var EstimateConverter|null
     */
    protected $defaultConverter;

    /**
     * An instance of CustomConverters.
     *
     * @var CustomConverters
     */
    protected $customConverters;

    /**
     * The state of the debug mode.
     *
     * @var bool
     */
    protected $debug = false;

    /**
     * Initializes an instance of the class with the given default converter
     * and the given custom converters.
     *
     * @param EstimateConverter|null $defaultConverter
     * @param CustomConverters|null  $customConverters
     * @param bool $mode
     */
    public function __construct(
        EstimateConverter $defaultConverter = null,
        CustomConverters $customConverters = null,
        bool $debug = true
    ) {
        $this->defaultConverter = $defaultConverter;
        $this->customConverters = $customConverters;
        $this->debug = $debug;
    }

    /**
     * Sets a debug state.
     *
     * @param bool $state
     */
    public function setDebugMode(bool $state = true): void
    {
        $this->debug = $state;
    }

    /**
     * Returns a debug state.
     *
     * @return bool
     */
    public function getDebugMode(): bool
    {
        return $this->debug;
    }

    /**
     * Checks whether the service has a default converter.
     *
     * @return bool
     */
    public function hasDefaultConverter(): bool
    {
        return isset($this->defaultConverter);
    }

    /**
     * Returns a default converter.
     *
     * @return EstimateConverter|null
     */
    public function getDefaultConverter(): ?EstimateConverter
    {
        return $this->defaultConverter;
    }

    /**
     * Checks whether the service has a custom converter with the given type.
     *
     * @param  string $converterType
     * @return bool
     */
    public function hasCustomConverter(string $converterType): bool
    {
        return $this->customConverters
            && $this->customConverters->hasConverter($converterType)
        ;
    }

    /**
     * Returns a custom converter by the given converter type.
     *
     * @param  string $converterType
     * @return EstimateConverter|null
     */
    public function getCustomConverter(string $converterType): ?EstimateConverter
    {
        return $this->customConverters
            ? $this->customConverters->getConverter($converterType)
            : null
        ;
    }

    /**
     * Returns a custom converter by the given type or the default converter.
     *
     * @param  string|null $converterType
     * @return EstimateConverter|null
     */
    public function getCustomOfDefaultConverter(?string $converterType): ?EstimateConverter
    {
        /** @var EstimateConverter|null $converter */
        $converter = $converterType ? $this->getCustomConverter($converterType) : null;

        return $converter ?? $this->getDefaultConverter();
    }

    /**
     * Converts the given estimate to an EstimateConverter by the given type.
     *
     * @param  Estimate $estimate A result of calculation.
     * @param  string   $converterType A type of content of the given Estimate.
     * @return EstimateConverter
     */
    public function convert(Estimate $estimate, string $converterType = null): ?EstimateConverter
    {
        /** @var EstimateConverter|null $converter */
        $converter = $this->getCustomOfDefaultConverter($converterType);

        // Sets the debug mode to the converter to show debugging values.
        if ($converter) {
            $converter->setDebugMode($this->debug);
        }

        return $converter ? $converter->setEstimate($estimate) : null;
    }
}
