<?php

namespace App\Model\Calculator\CalculationVariables\VariableSettings;

use App\Model\Calculator\CalculationVariables\VariableSettings;

/**
 * It is used to set settings of the 'Sides' calculation variable.
 */
class SidesVariableSettings implements VariableSettings
{
    /**
     * A label of the component.
     *
     * @var string|null
     */
    protected $label;

    /**
     * Creates an instance from the given array.
     *
     * @param  array $array
     * @return SidesVariableSettings
     */
    public static function createFromArray(array $array): self
    {
        $instance = new self();

        if (isset($array['label'])) {
            $instance->setLabel($array['label']);
        }

        return $instance;
    }

    /**
     * Sets a label.
     *
     * @param  string $label
     * @return void
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * Checks whether the instance has a label.
     *
     * @return bool
     */
    public function hasLabel(): bool
    {
        return isset($this->label);
    }

    /**
     * Returns a label.
     *
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }
}
