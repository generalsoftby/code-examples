<?php

namespace App\Services\Calculators\Blocks;

/**
 * Keeps a configuration of the permission for visualization of a block.
 */
class PermissionForVisualization
{
    /**
     * A block name.
     *
     * @var string
     */
    protected $blockName;

    /**
     * A component type: calculationVariable, attribute, option.
     *
     * @var string
     */
    protected $componentType;

    /**
     * An element name.
     *
     * @var string
     */
    protected $elementName;

    /**
     * Initializes an instance with a configuration of the permission.
     *
     * @param string $blockName
     * @param string $componentType
     * @param string $elementName
     */
    public function __construct(
        string $blockName,
        string $componentType,
        string $elementName
    ) {
        $this->blockName = $blockName;
        $this->componentType = $componentType;
        $this->elementName = $elementName;
    }

    /**
     * Initializes an instance using the given array.
     *
     * @param  array $array
     * @return PermissionForVisualization
     */
    public static function createFromArray(array $array): self
    {
        return new self(
            $array['blockName'],
            $array['componentType'],
            $array['elementName']
        );
    }

    /**
     * Returns a block name.
     *
     * @return string
     */
    public function getBlockName(): string
    {
        return $this->blockName;
    }

    /**
     * Returns a component name.
     *
     * @return string
     */
    public function getComponentName(): string
    {
        return $this->componentType;
    }

    /**
     * Returns an element name.
     *
     * @return string
     */
    public function getElementName(): string
    {
        return $this->elementName;
    }

    /**
     * Returns data of the instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'blockName' => $this->blockName,
            'componentType' => $this->componentType,
            'elementName' => $this->elementName,
        ];
    }
}
