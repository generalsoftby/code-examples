<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies;

use App\Facade\Util;

/**
 * Contains a value of the cost and its currency.
 */
class Cost
{
    /**
     * A value of the cost.
     *
     * @var float
     */
    protected $value;

    /**
     * A currency of the cost.
     *
     * @var string
     */
    protected $currency;

    /**
     * Initializes an instance of the class using a cost and its currency.
     *
     * @param float  $value
     * @param string $currency
     */
    function __construct(float $value, string $currency)
    {
        $this->value = $value;
        $this->currency = $currency;
    }

    /**
     * Returns a value of cost.
     *
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * Returns a currency of the cost.
     *
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Returns a value in the form of Russian rubles.
     * Converts the current value from the current currency to Russian rubles.
     *
     * @return float
     */
    public function getValueByCurrency(): float
    {
        return Util::convertToRub($this->value, $this->currency);
    }

    /**
     * Returns an instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'currency' => $this->currency,
        ];
    }
}
