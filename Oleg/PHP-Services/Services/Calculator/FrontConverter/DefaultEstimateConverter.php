<?php

namespace App\Services\Calculators\FrontConverter;

use App\Services\Calculators\FrontConverter\Traits\ConvertationOfReactFE;
use App\Services\Calculators\FrontConverter\Traits\ConvertationOfSpecification;

/**
 * The default EstimateConverter to convert any data of an estimate.
 * The result of convertation does not have an unique data structure.
 * All data are serial.
 */
class DefaultEstimateConverter extends AbstractEstimateConverter
{
    use ConvertationOfSpecification, ConvertationOfReactFE;
}
