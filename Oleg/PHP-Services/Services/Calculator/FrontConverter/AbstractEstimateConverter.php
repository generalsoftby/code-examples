<?php

namespace App\Services\Calculators\FrontConverter;

use App\Services\Calculators\Estimate;
use App\Services\Calculators\FrontConverter\Contracts\EstimateConverter;
use App\Services\Calculators\FrontConverter\Traits\ThrowsNoneEstimateException as TraitsThrowsNoneEstimateException;

/**
 * The default EstimateConverter to convert any data of an estimate.
 * The result of convertation does not have an unique data structure.
 * All data are serial.
 */
abstract class AbstractEstimateConverter implements EstimateConverter
{
    use TraitsThrowsNoneEstimateException;

    /**
     * An instance of Estimate.
     *
     * @var Estimate|null
     */
    protected $estimate;

    /**
     * The state of the debug mode.
     *
     * @var bool
     */
    protected $debug = false;

    /**
     * Sets a debug mode.
     *
     * @param  bool $debugMode
     * @return void
     */
    public function setDebugMode(bool $debugMode = true): void
    {
        $this->debug = $debugMode;
    }

    /**
     * Returns a debug mode.
     *
     * @return bool
     */
    public function getDebugMode(): bool
    {
        return $this->debug;
    }

    /**
     * Sets the given Estimate.
     *
     * @param  Estimate $estimate
     * @return self
     */
    public function setEstimate(Estimate $estimate): EstimateConverter
    {
        $this->estimate = $estimate;

        return $this;
    }

    /**
     * Returns an instance of Estimate.
     *
     * @return Estimate|null
     */
    public function getEstimate(): ?Estimate
    {
        return $this->estimate;
    }

    /**
     * Checks whether the instance has an estimate.
     *
     * @return bool
     */
    public function hasEstimate(): bool
    {
        return isset($this->estimate);
    }
}
