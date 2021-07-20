<?php

namespace App\Services\Calculators\FrontConverter\Traits;

use App\Services\Calculators\FrontConverter\Contracts\EstimateConverter;

/**
 * Implements functions of CustomConverters.
 *
 * @property array|string[] $converters The array with converters. Keys are types, values are class names.
 */
trait ManagesConverters
{
    /**
     * Returns an instance of Estimate.
     *
     * @return Estimate|null
     */
    public function getList(): array
    {
        return $this->converters;
    }

    /**
     * Checks whether the service has a custom converter with the given type.
     *
     * @param  string $converterType
     * @return bool
     */
    public function hasConverter(string $type): bool
    {
        return isset($this->converters[$type]);
    }

    /**
     * Returns a class name of a custom converter by the given type
     * of the converter.
     *
     * @param  string $type
     * @return string|null
     */
    public function getClassName(string $type): ?string
    {
        return $this->converters[$type] ?? null;
    }

    /**
     * Returns an instance of a converter by the given type.
     *
     * @param  string $type
     * @return EstimateConverter|null
     */
    public function getConverter(string $type): ?EstimateConverter
    {
        $className = $this->getClassName($type);

        return $className ? new $className : null;
    }
}
