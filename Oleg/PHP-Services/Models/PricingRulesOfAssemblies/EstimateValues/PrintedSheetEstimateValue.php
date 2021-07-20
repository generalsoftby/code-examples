<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\EstimateValues;

use App\Model\Calculator\TypesOfPrintedSheets\Type;
use App\Services\Calculators\EstimateValue;

/**
 * @deprecated v1.11 Now uses EstimateSubgroup
 */
class PrintedSheetEstimateValue implements EstimateValue
{
    /**
     * A Type.
     *
     * @var Type
     */
    protected $instanceOfType;

    /**
     * Initializes an instance of the class.
     *
     * @param Type $instanceOfType
     */
    public function __construct(Type $instanceOfType)
    {
        $this->instanceOfType = $instanceOfType;
    }

    /**
     * Returns a type of the value.
     *
     * @return string
     */
    public function getType(): string
    {
        return 'printed_sheet';
    }

    /**
     * Returns a name of printed sheet.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->instanceOfType->getName();
    }

    /**
     * Returns a size of printed sheet.
     *
     * @return string
     */
    public function getSize(): string
    {
        return $this->instanceOfType->getSize();
    }

    /**
     * Returns a top margin.
     *
     * @return int
     */
    public function getTopMargin(): int
    {
        return $this->instanceOfType->getTopMargin();
    }

    /**
     * Returns a bottom margin.
     *
     * @return int
     */
    public function getBottomMargin(): int
    {
        return $this->instanceOfType->getBottomMargin();
    }

    /**
     * Returns a left margin.
     *
     * @return int
     */
    public function getLeftMargin(): int
    {
        return $this->instanceOfType->getLeftMargin();
    }

    /**
     * Returns a right margin.
     *
     * @return int
     */
    public function getRightMargin(): int
    {
        return $this->instanceOfType->getRightMargin();
    }

    /**
     * Returns an instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name_of_printed_sheet' => $this->instanceOfType->getName(),
            'size' => $this->instanceOfType->getSize(),
            'height' => $this->instanceOfType->getHeight(),
            'width' => $this->instanceOfType->getWidth(),
            'top_margin' => $this->instanceOfType->getTopMargin(),
            'bottom_margin' => $this->instanceOfType->getBottomMargin(),
            'left_margin' => $this->instanceOfType->getLeftMargin(),
            'right_margin' => $this->instanceOfType->getRightMargin(),
        ];
    }
}
