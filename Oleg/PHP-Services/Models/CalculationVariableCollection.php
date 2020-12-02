<?php

namespace App\Model\Calculator;

use App\Model\Calculator\CalculationVariables\CalculationVariableEntity;
use App\Model\Calculator\CalculationVariables\CalculationVariableEntityCollection;
use App\Services\Calculators\Errors;
use Illuminate\Database\Eloquent\Collection;

/**
 * A collection with CalculationVariable.
 */
class CalculationVariableCollection extends Collection
{
    /**
     * Returns an array with types of variables.
     *
     * @return array|string[]
     */
    public function types(): array
    {
        return $this->pluck('type')->unique()->toArray();
    }

    /**
     * Returns an array with names of variables.
     *
     * @return array|string[]
     */
    public function names(): array
    {
        return $this->pluck('name')->unique()->toArray();
    }

    /**
     * Validates given data by a name of the calculation variable.
     *
     * @param  string $name
     * @param  mixed  $data
     * @return bool
     */
    public function validateByName(string $name, $data): bool
    {
        /** @var CalculationVariableEntity|null $instance **/
        $instance = $this->getInstanceOfSettings($name);

        return $instance ? $instance->validate($data) : false;
    }

    /**
     * Returns errors of a validation by the given name
     * of the calculation variable.
     * Returns null when an instance was not found.
     *
     * @param  string $name
     * @return Errors|Error[]|null
     */
    public function getErrorsOfValidationByName(string $name): ?Errors
    {
        /** @var CalculationVariableEntity|null $instance **/
        $instance = $this->getInstanceOfSettings($name);

        return $instance ? $instance->getErrors() : null;
    }

    /**
     * Checks whether the collection has a calculation variable
     * with the given name.
     *
     * @param  string $name
     * @return bool
     */
    public function hasCalculationVariable(string $name): bool
    {
        return $this->whereStrict('name', $name)->count() > 0;
    }

    /**
     * Checks whether a calculation variable has an instance of the settings.
     *
     * @param  string $name
     * @return bool
     */
    public function hasInstanceOfSettings(string $name): bool
    {
        return ! is_null($this->getInstanceOfSettings($name));
    }

    /**
     * Filters the collection with CalculationVariables by the given type.
     * Returns a new collection.
     *
     * @param  string $type
     * @return CalculationVariableCollection
     */
    public function filterByType(string $type): self
    {
        return $this->whereStrict('type', $type);
    }

    /**
     * Filters the collection with CalculationVariables by the given name.
     * Returns a new collection.
     *
     * @param  string $name
     * @return CalculationVariableCollection
     */
    public function filterByName(string $name): self
    {
        return $this->whereStrict('name', $name);
    }

    /**
     * Returns the first CalculationVariable by the given type.
     *
     * @param  string $name
     * @return CalculationVariable|null
     */
    public function getFirstByName(string $name): ?CalculationVariable
    {
        return $this->whereStrict('name', $name)->first();
    }

    /**
     * Returns an instance of the settings of the calculation variable
     * by the given name.
     *
     * @param  string $name
     * @return CalculationVariableEntity|null
     */
    public function getInstanceOfSettings(string $name): ?CalculationVariableEntity
    {
        /** @var CalculationVariable|null $variable **/
        $variable = $this->getFirstByName($name);

        return $variable ? $variable->getSettings() : null;
    }

    /**
     * Returns a collection of instances of settings.
     *
     * @return CalculationVariableEntityCollection
     */
    public function getInstancesOfSettings(): CalculationVariableEntityCollection
    {
        return CalculationVariableEntityCollection::createFromCalculationVariableCollection($this);
    }

    /**
     * Fills an instance of settings with data.
     * Returns a state of filling.
     *
     * @param  string $type
     * @param  mixed  $data Data for the settings.
     * @return bool
     */
    public function fillInstanceOfSettings(string $type, $data): bool
    {
        /** @var CalculationVariableEntity|null $instance **/
        $instance = $this->getInstanceOfSettings($type);

        return $instance && $instance->fillWithUserValues($data);
    }
}
