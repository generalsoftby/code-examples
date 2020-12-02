<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\EstimateConverters;

use App\Services\Calculators\FrontConverter\AbstractEstimateConverter;
use App\Services\Calculators\FrontConverter\SpecificationDescriber;
use App\Services\Calculators\FrontConverter\Traits\ConvertationOfReactFE;

/**
 * Implements convertation of MultipleProduct's estimate.
 */
class MultipleProductConverter extends AbstractEstimateConverter
{
    use ConvertationOfReactFE;

    /**
     * Converts data to an array of the format of the specification:
     * an array with strings.
     *
     * @return array|string[]
     */
    public function toArrayOfSpecification(): array
    {
        $this->throwNoneEstimateException();
        $specificationDescriber = new SpecificationDescriber($this->getEstimate());

        $groupOfStitchingType = $this->estimate->getGroup('stitching_type');
        $nameOfStitcingWay = $groupOfStitchingType->get('name')->getValue();
        $descriptionOfStitchingWay = trans('estimate.stitching_way') . ': ' . $nameOfStitcingWay;
        $description = $specificationDescriber->describe();

        // Adds the description of the way to the second position
        // after a size of product.
        array_splice($description, 1, 0, $descriptionOfStitchingWay);

        return $description;
    }


}
