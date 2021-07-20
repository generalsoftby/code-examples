<?php

namespace App\Model\Calculator\CalculationVariables;

/**
 * The interface for entities of calculation variables to change base settings.
 */
interface EntityWithVariableSettings extends CalculationVariableEntity
{
    /**
     * Sets an instance of VariableSettings to update settings of the variable.
     *
     * @param  VariableSettings $variableSettings
     * @return void
     */
    public function setVariableSettings(VariableSettings $variableSettings): void;
}
