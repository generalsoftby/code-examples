<?php

namespace App\Services\Calculators\Blocks;

use App\Model\Calculator\CalculationVariables\VariableSettings;

/**
 * Keeps and handles data about a calculation variable of contents.
 */
class CalculationVariableOfContents
{
    /**
     * A unique type of the calculation variable. It is used to get a componet
     * of the variable for the frontend or the backend.
     *
     * @var string
     */
    protected $type;

    /**
     * A name of the calculation variable. It is used to identify the variable.
     *
     * @var string
     */
    protected $name;

    /**
     * A position of the component on the frontend.
     *
     * @var string
     */
    protected $position;

    /**
     * A component configuration. Settings for the frontend.
     *
     * @var ComponentConfigration|null
     */
    protected $configuration;

    /**
     * An array with privileged components that allowed to change values
     * of the calculation variable.
     *
     * @var array
     */
    protected $modifyValuesBy;

    /**
     * An array with privileged components that allowed to change config
     * of the calculation variable.
     *
     * @var array
     */
    protected $modifyConfigBy;

    /**
     * A state of visibility of settings at the calculator settings.
     *
     * @var bool
     */
    protected $visibleSettings;

    /**
     * A name of a calculation variable to get settings from it, if it exists.
     *
     * @var string|null
     */
    protected $importSettingsFrom;

    /**
     * An instance of VariableSettings to change settings of the calculation
     * variable.
     *
     * @var VariableSettings|null
     */
    protected $variableSettings;

    /**
     * Initializes an instance with the given name and configuration.
     *
     * @param string $type
     * @param string $name
     * @param string $position
     * @param ComponentConfiguration|null $configuration
     * @param array  $modifyValuesBy
     * @param array  $modifyConfigBy
     * @param bool   $visibleSettings
     * @param string|null $importSettingsFrom
     */
    public function __construct(
        string $type,
        string $name,
        string $position = 'top',
        ComponentConfiguration $configuration = null,
        array $modifyValuesBy = [],
        array $modifyConfigBy = [],
        bool $visibleSettings = true,
        string $importSettingsFrom = null,
        VariableSettings $variableSettings = null
    ) {
        $this->type = $type;
        $this->name = $name;
        $this->position = $position;
        $this->configuration = $configuration;
        $this->modifyValuesBy = $modifyValuesBy;
        $this->modifyConfigBy = $modifyConfigBy;
        $this->visibleSettings = $visibleSettings;
        $this->importSettingsFrom = $importSettingsFrom;
        $this->variableSettings = $variableSettings;
    }

    /**
     * Initializes an instance using the given array with a name and an instance
     * of configuration.
     *
     * @param  array $array
     * @return CalculationVariableOfContents
     */
    public static function createFromArray(array $array): self
    {
        if (isset($array['configuration']) && $array['configuration'] instanceof ComponentConfiguration) {
            $configuration = $array['configuration'];
        }

        return new self(
            $array['type'],
            $array['name'],
            $array['position'] ?? 'top',
            $configuration ?? null,
            $array['modifyValuesBy'] ?? [],
            $array['modifyConfigBy'] ?? [],
            $array['visibleSettings'] ?? true,
            $array['importSettingsFrom'] ?? null,
            $array['variableSettings'] ?? null
        );
    }

    /**
     * Returns a unique type (a code name) of the calculation variable.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Returns a name of the calculation variable.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns a position of the calculation variable.
     *
     * @return string
     */
    public function getPosition(): string
    {
        return $this->position;
    }

    /**
     * Sets a component configuration.
     *
     * @param  ComponentConfiguration $configuration
     * @return void
     */
    public function setComponentConfiguration(ComponentConfiguration $configuration): void
    {
        $this->configuration = $configuration;
    }

    /**
     * Returns a component configuration.
     *
     * @return ComponentConfiguration|null
     */
    public function getComponentConfiguration(): ?ComponentConfiguration
    {
        return $this->configuration;
    }

    /**
     * Returns privileged components of values.
     *
     * @return array
     */
    public function getPrivilegedComponentsOfValues(): array
    {
        return $this->modifyValuesBy;
    }

    /**
     * Returns privileged components of config.
     *
     * @return array
     */
    public function getPrivilegedComponentsOfConfig(): array
    {
        return $this->modifyConfigBy;
    }

    /**
     * Checks whether the settings of the calculation variable are visible
     * at the interface of the calculator settings.
     *
     * @return bool
     */
    public function areSettingsVisible(): bool
    {
        return $this->visibleSettings;
    }

    /**
     * Checks whether the calculation variable imports settings from another
     * calculation variable.
     *
     * @return bool
     */
    public function doesImportSettings(): bool
    {
        return isset($this->importSettingsFrom);
    }

    /**
     * Returns a source of import. It is a name of another calculation variable.
     *
     * @return string|null
     */
    public function getSourceOfImport(): ?string
    {
        return $this->importSettingsFrom;
    }

    /**
     * Checks whether the variable has variable settings.
     *
     * @return bool
     */
    public function hasVariableSettings(): bool
    {
        return isset($this->variableSettings);
    }

    /**
     * Returns an instance of VariableSettings for the calculation variable.
     *
     * @return VariableSettings|null
     */
    public function getVariableSettings(): ?VariableSettings
    {
        return $this->variableSettings;
    }

    /**
     * Returns data of the instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'name' => $this->name,
            'position' => $this->position,
            'configuration' => isset($this->configuration) ? $this->configuration->toArray() : null,
            'modifyValuesBy' => $this->modifyValuesBy,
            'modifyConfigBy' => $this->modifyConfigBy,
        ];
    }
}
