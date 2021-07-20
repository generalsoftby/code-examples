<?php

namespace App\Services\Calculators\Blocks;

use App\Model\Calculator\CalculationVariables\CalculationVariableEntity;

/**
 * Keeps an instance of CalculationVariableOfContents and CalculationVariableEntity.
 * It is used to pass to the frontend from the controller.
 * Transform data of instances to data to the frontend to config the calculator
 * interface.
 */
class CalculationVariableContainer
{
    /**
     * An instance of CalculationVariableOfContents.
     *
     * @var CalculationVariableOfContents
     */
    protected $variableOfContents;

    /**
     * An instance of CalculationVariableEntity.
     *
     * @var CalculationVariableEntity|null
     */
    protected $instanceOfCalculationVariableEntity;

    public function __construct(
        CalculationVariableOfContents $calculationVariableOfContents
    ) {
        $this->variableOfContents = $calculationVariableOfContents;
    }

    /**
     * Returns a name of the calculation variable.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->variableOfContents->getName();
    }

    /**
     * Returns a type of the calculation variable.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->variableOfContents->getType();
    }

    /**
     * Returns an instance of the calculation variable of contents.
     *
     * @return CalculationVariableOfContents
     */
    public function getInstanceOfContents(): CalculationVariableOfContents
    {
        return $this->variableOfContents;
    }

    /**
     * Sets an instance of CalculationVariableEntity.
     *
     * @param  CalculationVariableEntity $instance
     * @return void
     */
    public function setInstanceOfEntity(CalculationVariableEntity $instance): void
    {
        $this->instanceOfCalculationVariableEntity = $instance;
    }

    /**
     * Returns an instance of CalculationVariableEntity.
     *
     * @return CalculationVariableEntity|null
     */
    public function getInstanceOfEntity(): ?CalculationVariableEntity
    {
        return $this->instanceOfCalculationVariableEntity;
    }

    /**
     * Checks whether the container has an instance of the entity.
     *
     * @return bool
     */
    public function hasInstanceOfEntity(): bool
    {
        return isset($this->instanceOfCalculationVariableEntity);
    }

    /**
     * Returns settings of the entity if it exists.
     *
     * @return array
     */
    public function getSettingsOfEntity(): array
    {
        return isset($this->instanceOfCalculationVariableEntity)
            ? $this->instanceOfCalculationVariableEntity->toArray()
            : []
        ;
    }

    /**
     * Checks whether the calculation variable imports settings from another
     * calculation variable.
     *
     * @return bool
     */
    public function doesImportSettings(): bool
    {
        return $this->variableOfContents->doesImportSettings();
    }

    /**
     * Checks whether the variable has variable settings.
     *
     * @return bool
     */
    public function hasVariableSettings(): bool
    {
        return $this->variableOfContents->hasVariableSettings();
    }

    /**
     * Checks whether the settings of the calculation variable are visible
     * at the interface of the calculator settings.
     *
     * @return bool
     */
    public function areSettingsVisible(): bool
    {
        return $this->variableOfContents->areSettingsVisible();
    }

    /**
     * Checks whether the calculation variable keeps itself settings.
     *
     * @return bool
     */
    public function doesKeepItselfSettings(): bool
    {
        return $this->areSettingsVisible() && !$this->doesImportSettings();
    }

    /**
     * Returns an array of the container.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'type' => $this->getType(),
            'settings' => $this->getSettingsOfEntity(),
        ];
    }
}
