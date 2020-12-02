<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies;

class InvalidValueKeeperException extends \Exception
{
    /**
     * The message of the exception.
     *
     * @var string
     */
    protected $message = 'The given ValueKeeper is invalid.';

    /**
     * An instance of ValidKeeper.
     *
     * @var ValueKeeper
     */
    protected $valueKeeper;
    
    /**
     * An array with valid classes.
     *
     * @var array|string[]
     */
    protected $validClasses;

    /**
     * Initializes an instance of the exception.
     *
     * @param ValueKeeper    $valueKeeper
     * @param array|string[] $validClasses
     */
    public function __construct(ValueKeeper $valueKeeper, array $validClasses = [])
    {
        $this->valueKeeper = $valueKeeper;
        $this->validClasses = $validClasses;

        $this->message .= ' The class: '. get_class($valueKeeper) . '.';

        if (count($validClasses)) {
            $this->message .= ' Valid classes: ' . implode(', ', $validClasses);
        }
    }

    /**
     * Returns a ValueKeeper.
     *
     * @return ValueKeeper
     */
    public function getValueKeeper(): ValueKeeper
    {
        return $this->valueKeeper;
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
