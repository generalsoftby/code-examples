<?php

namespace App\Model\Calculator\BookKeepers;

use App\Model\Calculator\PricingRulesOfAssemblies\PricingRule;

class InvalidPricingRuleException extends \Exception
{
    /**
     * The message of the exception.
     *
     * @var string
     */
    protected $message = 'The given PricingRule is invalid.';

    /**
     * An instance of PricingRule.
     *
     * @var PricingRule
     */
    protected $rule;

    /**
     * An array with valid classes.
     *
     * @var array|string[]
     */
    protected $validClasses;

    /**
     * Initializes an instance of the exception.
     *
     * @param PricingRule    $rule
     * @param array|string[] $validClasses
     */
    public function __construct(PricingRule $rule, array $validClasses = [])
    {
        $this->rule = $rule;
        $this->validClasses = $validClasses;

        $this->message .= ' The class: ' . get_class($rule);

        if (count($validClasses)) {
            $this->message .= '. Valid classes: ' . implode(', ', $validClasses);
        }
    }

    /**
     * Returns a PricingRule.
     *
     * @return PricingRule
     */
    public function getPricingRule(): PricingRule
    {
        return $this->rule;
    }

    /**
     * Returns an array with valid classes.
     *
     * @return array|string[]
     */
    public function getValidClasses(): array
    {
        return $this->validClasses;
    }
}
