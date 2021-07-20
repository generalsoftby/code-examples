<?php

namespace App\Services\Calculators\Blocks;

/**
 * Keeps data about block.
 */
class Block
{
    /**
     * A block name.
     *
     * @var string
     */
    protected $name;

    /**
     * A state of visibility of the block.
     *
     * @var bool
     */
    protected $visibility;

    /**
     * A name of attribute group.
     *
     * @var string|null
     */
    protected $nameOfAttributeGroup;

    /**
     * Permissions for visualization of the block by other blocks and components.
     *
     * @var PermissionForVisualizationCollection
     */
    protected $permissionsForVisualization;

    /**
     * A collection with calculation variables of contents.
     *
     * @var CalculationVariableOfContentsCollection
     */
    protected $calculationVariablesOfContents;

    /**
     * Initializes an instance with a name and a state of visibility of the block.
     *
     * @param string      $name
     * @param bool        $visibility
     * @param string|null $nameOfAttributeGroup
     * @param CalculationVariableOfContentsCollection|null $variables
     * @param PermissionForVisualizationCollection|null    $permissions
     */
    public function __construct(
        string $name,
        bool $visibility = true,
        string $nameOfAttributeGroup = null,
        CalculationVariableOfContentsCollection $variables = null,
        PermissionForVisualizationCollection $permissions = null
    ) {
        $this->name = $name;
        $this->visibility = $visibility;
        $this->nameOfAttributeGroup = $nameOfAttributeGroup ?? null;
        $this->calculationVariablesOfContents = $variables ?? new CalculationVariableOfContentsCollection;
        $this->permissionsForVisualization = $permissions ?? new PermissionForVisualizationCollection;
    }

    /**
     * Initializes an instance from the given array.
     *
     * @param  array $array
     * @return Block
     */
    public static function createFromArray(array $array): self
    {
        if (isset($array['contents']['calculationVariables'])) {
            $variables = CalculationVariableOfContentsCollection::createFromArray($array['contents']['calculationVariables']);
        }

        if (isset($array['visualizeBy'])) {
            $permissions = PermissionForVisualizationCollection::createFromArray($array['visualizeBy']);
        }

        return new self(
            $array['name'],
            $array['visible'] ?? true,
            $array['contents']['nameOfAttributeGroup'] ?? null,
            $variables ?? null,
            $permissions ?? null
        );
    }

    /**
     * Sets a name of the block.
     *
     * @param  string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Returns a name of the block.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets a state of visibility of the block.
     *
     * @param  bool $visibility
     * @return void
     */
    public function setVisibility(bool $visibility): void
    {
        $this->visibility = $visibility;
    }

    /**
     * Returns a state of visibility of the block.
     *
     * @return bool
     */
    public function getVisibility(): bool
    {
        return $this->visibility;
    }

    /**
     * Sets a name of an attribute group of the block.
     *
     * @param  string $name
     * @return void
     */
    public function setNameOfAttributeGroup(string $name): void
    {
        $this->nameOfAttributeGroup = $name;
    }

    /**
     * Returns a name of an attribute group of the block contents.
     *
     * @return string|null
     */
    public function getNameOfAttributeGroup(): ?string
    {
        return $this->nameOfAttributeGroup;
    }

    /**
     * Checks whether the block must has an assembly.
     *
     * @return bool
     */
    public function mustHasAssembly(): bool
    {
        return isset($this->nameOfAttributeGroup);
    }

    /**
     * Sets a collection with permissions for visualization of the block.
     *
     * @param PermissionForVisualizationCollection $permissions
     */
    public function setPermissionsForVisualization(PermissionForVisualizationCollection $permissions): void
    {
        $this->permissionsForVisualization = $permissions;
    }

    /**
     * Returns a collection with permissions for visualization of the block.
     *
     * @return PermissionForVisualizationCollection
     */
    public function getPermissionsForVisualization(): PermissionForVisualizationCollection
    {
        return $this->permissionsForVisualization;
    }

    /**
     * Checks whether the block is required.
     *
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->permissionsForVisualization->count() === 0;
    }

    /**
     * Sets a collection with allowed calculation variables of the block contents
     * and their configuration.
     *
     * @param CalculationVariableOfContentsCollection $variables
     */
    public function setCalculationVariablesOfContents(CalculationVariableOfContentsCollection $variables): void
    {
        $this->calculationVariablesOfContents = $variables;
    }

    /**
     * Returns a collection with allowed calculation variables of the block contents.
     *
     * @return CalculationVariableOfContentsCollection
     */
    public function getCalculationVariablesOfContents(): CalculationVariableOfContentsCollection
    {
        return $this->calculationVariablesOfContents;
    }

    /**
     * Checks whether the block must has calculation variables.
     *
     * @return bool
     */
    public function mustHasCalculationVariables(): bool
    {
        return $this->calculationVariablesOfContents->count();
    }

    /**
     * Returns data of the instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'visible' => $this->visibility,
            'visualizeBy' => $this->permissionsForVisualization->toArray(),
            'contents' => [
                'calculationVariables' => $this->calculationVariablesOfContents->toArray(),
                'nameOfAttributeGroup' => $this->nameOfAttributeGroup,
            ],
        ];
    }
}
