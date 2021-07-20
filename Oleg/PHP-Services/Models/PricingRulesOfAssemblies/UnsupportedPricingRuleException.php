<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies;

class UnsupportedPricingRuleException extends \Exception
{
    /**
     * The message of the exception.
     *
     * @var string
     */
    protected $message = 'An unsupported type of pricing rule.';

    /**
     * A type of pricing rule.
     *
     * @var string|null
     */
    protected $type;

    /**
     * Initializes an instance of the exception with a type of pricing rule.
     *
     * @param string|null $type
     */
    public function __construct(string $type = null)
    {
        $this->type = $type;

        if ($type) {
            $this->message .= ' The type: ' . $type . '.';
        }
    }

    /**
     * Returns a type of pricing rule.
     *
     * @return string|null
     */
    public function getTypeOfPricingRule(): ?string
    {
        return $this->type;
    }
}
