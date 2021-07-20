<?php

namespace App\Model\Calculator\CalculationVariables;

/**
 * Contains a size for a sheet.
 */
class SheetFormat extends SheetSize
{
    /**
     * A name of the format.
     *
     * @var string
     */
    protected $name;

    /**
     * Initializes an instance of the class with a sheet size and its name.
     *
     * @param int    $height
     * @param int    $width
     * @param string $name
     */
    public function __construct(int $height, int $width, string $name)
    {
        parent::__construct($height, $width);

        $this->name = $name;
    }

    /**
     * Returns a name of the format.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns data of the instance in the forma of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'height' => $this->height,
            'width' => $this->width,
            'name' => $this->name,
        ];
    }
}
