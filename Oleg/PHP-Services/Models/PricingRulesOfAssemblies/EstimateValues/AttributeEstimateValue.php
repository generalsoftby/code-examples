<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\EstimateValues;

use App\Model\Calculator\CalculatorAttribute as CalculatorCalculatorAttribute;
use App\Model\Calculator\CalculatorAttributeValue;
use App\Services\Calculators\EstimateValue;

/**
 * Keeps an attribute value and its value.
 */
class AttributeEstimateValue implements EstimateValue
{
    /**
     * An attribute value of the assembly.
     *
     * @var CalculatorAttributeValue
     */
    protected $attributeValue;

    /**
     * Values of attributes in the form of an array;
     *
     * @var array
     */
    protected $values;

    /**
     * Initializes an instance of the class.
     *
     * @param CalculatorAttributeValue $attributeValue
     */
    public function __construct(CalculatorAttributeValue $attributeValue)
    {
        $this->attributeValue = $attributeValue;
    }

    /**
     * Returns a type of the value.
     *
     * @return string
     */
    public function getType(): string
    {
        return 'attribute';
    }

    /**
     * Returns an attribute value.
     *
     * @return CalculatorAttributeValue
     */
    public function getAttributeValue(): CalculatorAttributeValue
    {
        return $this->attributeValue;
    }

    /**
     * Returns an attribute.
     *
     * @return CalculatorCalculatorAttribute
     */
    public function getAttribute(): CalculatorCalculatorAttribute
    {
        return $this->attributeValue->attribute;
    }

    /**
     * Returns a name of the attribute.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->getAttribute()->title_specification;
    }

    /**
     * Returns a value of the attribute.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->attributeValue->value;
    }

    /**
     * Returns an instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'value' => $this->getValue(),
        ];
    }
}
