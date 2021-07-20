<?php

namespace App\Model\Calculator\CalculationVariables;

/**
 * The exception for incorrect variable settings.
 */
class IncorrectVariableSettingsException extends \Exception
{
    public function __construct(VariableSettings $given, string $target)
    {
        $this->message = "The given instance VariableSettings (" . get_class($given)
            . ") is non-compatible with $target"
        ;
    }
}
