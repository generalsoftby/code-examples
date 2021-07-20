<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\Estimates;

use App\Model\Calculator\TypesOfPrintedSheets\Type;
use App\Services\Calculators\EstimateSubgroup;

/**
 * Initializes an EstimateSubgroup of the given sheet Type.
 */
trait InitializationOfEstimateSubgroupOfSheet
{
    /**
     * Initializes and returns a new EstimateSubgroup by the given Type.
     *
     * @param  Type $sheet
     * @return EstimateSubgroup
     */
    protected function newEstimateSubgroupOfSheet(Type $sheet): EstimateSubgroup
    {
        $subgroup = new EstimateSubgroup;
        $subgroup->setType('printed_sheet');
        $subgroup->add('name_of_printed_sheet', $sheet->getName());
        $subgroup->add('size', $sheet->getSize());
        $subgroup->add('height', $sheet->getHeight());
        $subgroup->add('width', $sheet->getWidth());
        $subgroup->add('top_margin', $sheet->getTopMargin());
        $subgroup->add('bottom_margin', $sheet->getBottomMargin());
        $subgroup->add('left_margin', $sheet->getLeftMargin());
        $subgroup->add('right_margin', $sheet->getRightMargin());
        $subgroup->add('priority', $sheet->getPriority());

        return $subgroup;
    }

}
