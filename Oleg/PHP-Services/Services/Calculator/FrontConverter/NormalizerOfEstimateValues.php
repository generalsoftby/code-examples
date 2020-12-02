<?php

namespace App\Services\Calculators\FrontConverter;

/**
 * Normalizes values of an estimate.
 */
class NormalizerOfEstimateValues
{
    /**
     * Normalizes the given value.
     *
     * @param  mixed $value
     * @return mixed
     */
    public static function normalizeValue($value)
    {
        switch (gettype($value)) {
            case 'double':
                return self::normalizeFloat($value);

            case 'boolean':
            case 'bool':
                return self::normalizeBool($value);

            default:
                return $value;
        }
    }

    /**
     * Rounds the given float value.
     *
     * @param  float $value
     * @return float
     */
    public static function normalizeFloat(float $value): float
    {
        return round($value, 2);
    }

    /**
     * Converts to a string the given bool value.
     *
     * @param  bool $value
     * @return string
     */
    public static function normalizeBool(bool $value): string
    {
        return $value ? trans('estimate.yes') : trans('estimate.no');
    }
}
