<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\EstimateConverters;

use App\Services\Calculators\FrontConverter\Contracts\CustomConverters as BaseConverters;
use App\Services\Calculators\FrontConverter\Traits\ManagesConverters;

/**
 * Implements CustomConverters and contains custom converter classes.
 */
class CustomConverters implements BaseConverters
{
    use ManagesConverters;

    /**
     * The array with converters.
     * Keys are types, values are class names.
     *
     * @var array|string[]
     */
    protected $converters = [
        // Example: 'type_id' => 'class_name',
        // 13 => RunningMeterConverter::class,
        14 => MultipleProductConverter::class,
    ];
}
