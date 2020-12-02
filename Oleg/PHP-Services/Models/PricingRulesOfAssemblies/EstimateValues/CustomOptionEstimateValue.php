<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies\EstimateValues;

use App\Services\Calculators\EstimateValue;

/**
 * @deprecated v1.11 It was replaced by EstimateSubgroup.
 */
class CustomOptionEstimateValue implements EstimateValue
{
    /**
     * Values of the option.
     *
     * @var array|string[]
     */
    protected $values;

    /**
     * Initializes an instance of the class.
     *
     * @param array $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * Returns a type of the value.
     *
     * @return string
     */
    public function getType(): string
    {
        return 'custom_option';
    }

    /**
     * Returns a price of the option.
     *
     * @return float
     */
    public function getPrice(): float
    {
        return $this->values['total_price'] ?? 0;
    }

    /**
     * Returns a price per unit.
     *
     * @return float
     */
    public function getPricePerUnit(): float
    {
        return $this->values['one_price'] ?? 0;
    }

    /**
     * Returns a user name of the option.
     *
     * @return string
     */
    public function getUserName(): string
    {
        return $this->values['optons_user_name'];
    }

    /**
     * Returns a user name of the value.
     *
     * @return string
     */
    public function getUserNameOfValue(): string
    {
        return $this->values['value_specification_name'];
    }

    /**
     * Returns a number of applied option for the value.
     *
     * @return float
     */
    public function getNumberOfValues(): float
    {
        return $this->values['amount'] ?? 0;
    }

    /**
     * Returns an instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->values;
    }
}
