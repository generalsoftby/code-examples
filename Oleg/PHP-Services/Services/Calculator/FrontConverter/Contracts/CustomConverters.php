<?php

namespace App\Services\Calculators\FrontConverter\Contracts;

/**
 * The interface for a class that returns converters.
 */
interface CustomConverters
{
    /**
     * Returns a list with classes of converters.
     * Keys are types, values are class names.
     *
     * @return array|string[]
     */
    public function getList();

    /**
     * Checks whether the service has a custom converter with the given type.
     *
     * @param  string $converterType
     * @return bool
     */
    public function hasConverter(string $type): bool;

    /**
     * Returns a class name of a custom converter by the given type
     * of the converter.
     *
     * @param  string $type
     * @return string|null
     */
    public function getClassName(string $type): ?string;

    /**
     * Returns an instance of a converter by the given type.
     *
     * @param  string $type
     * @return EstimateConverter|null
     */
    public function getConverter(string $type): ?EstimateConverter;
}
