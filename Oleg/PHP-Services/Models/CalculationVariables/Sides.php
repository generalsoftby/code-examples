<?php

namespace App\Model\Calculator\CalculationVariables;

use App\Services\Calculators\Error;
use App\Model\Calculator\CalculationVariables\VariableSettings\SidesVariableSettings;

/**
 * Sides calculation variable implementation.
 */
class Sides extends AbstractCustomNumber implements EntityWithVariableSettings
{
    use ToolProductSize;

    /**
     * Variable label.
     *
     * @var string
     */
    protected $label;

    /**
     * An instance of FourSides.
     *
     * @var FourSides
     */
    protected $fourSides;

    /**
     * A state of using of the sides.
     *
     * @var bool
     */
    protected $used = false;

    /**
     * Length of sides of a product.
     *
     * @var int|null
     */
    protected $lengthOfSides;

    /**
     * Full length of sides.
     *
     * @var int|null
     */
    protected $fullLengthOfSides;

    /**
     * Initializes an instance of the class.
     *
     * @param array $values
     */
    public function __construct(array $values = null)
    {
        parent::__construct($values);

        $this->fourSides = new FourSides;
    }

    /**
     * Fills the instance from an array.
     *
     * @param  array $array
     * @return void
     */
    public function fillFromArray(array $array)
    {
        parent::fillFromArray($array);
        $this->label = $array['label'] ?? trans('calculation_variables.sides');
    }

    /**
     * Sets an instance of VariableSettings to update settings of the variable.
     *
     * @param  SidesVariableSettings $variableSettings
     * @return void
     */
    public function setVariableSettings(VariableSettings $variableSettings): void
    {
        if (!($variableSettings instanceof SidesVariableSettings)) {
            throw new IncorrectVariableSettingsException(
                $variableSettings,
                SidesVariableSettings::class
            );
        }

        if ($variableSettings->hasLabel()) {
            $this->label = $variableSettings->getLabel();
        }
    }

    /**
     * Returns true whether user variables were filled with user values.
     *
     * @param  array $values
     * @return bool
     */
    public function fillWithUserValues(array $values): bool
    {
        if (!$this->validate($values)) {
            return false;
        }

        $this->used = !empty($values['active']);
        $this->setSides($values['sides'] ?? []);

        return true;
    }

    /**
     * Validates given user data and returns result of the validation.
     *
     * @param  mixed $data
     * @return bool
     */
    public function validate($data): bool
    {
        $state = true;
        $transReplacements = [
            'name' => $this->label
        ];

        // If sides are used.
        if (empty($data['active'])) {
            return $state;
        }

        $sides = $data['sides'] ?? [];

        if (empty($sides)) {
            $this->errors->add(trans('calculator_errors.sides_were_not_given', $transReplacements), Error::VARIABLE_OF_CALCULATION_ERROR);
            $state = false;
        } elseif (!$this->fourSides->hasSides($sides)) {
            $this->errors->add(trans('calculator_errors.sides_have_incorrect_values', $transReplacements), Error::VARIABLE_OF_CALCULATION_ERROR);
            $state = false;
        } elseif (!$this->fourSides->hasSelectedSides($sides)) {
            $this->errors->add(trans('calculator_errors.sides_are_not_selected', $transReplacements), Error::VARIABLE_OF_CALCULATION_ERROR);
            $state = false;
        }

        return $state;
    }

    /**
     * Sets states of the given sides with their states.
     * Keys are names of sides, values are states of these sides.
     *
     * @param array $sides
     */
    public function setSides(array $sides): void
    {
        $this->fourSides->setSidesAndFalseForUndefined($sides);
        $this->defineInterval();
    }

    /**
     * Return a length of sides of a product.
     *
     * @return int|null
     */
    public function getLengthOfSides(): ?int
    {
        return $this->lengthOfSides;
    }

    /**
     * Returns total length of sides of all products.
     *
     * @return int|null
     */
    public function getTotalLengthOfSides(): ?int
    {
        return $this->fullLengthOfSides;
    }

    /**
     * Returns used sides.
     *
     * @return array|string[]
     */
    public function getUsedSides(): array
    {
        return $this->fourSides->getNamesOfSelectedSides();
    }

    /**
     * Checkes whether sides are used.
     *
     * @return bool
     */
    public function areSidesUsed(): bool
    {
        return $this->used;
    }

    /**
     * Defines lengths and finds an appropriate interval.
     *
     * @return void
     */
    protected function defineInterval(): void
    {
        if (isset($this->numberOfProducts, $this->widthOfProduct, $this->heigthOfProduct)) {
            $this->defineLengthOfSides();
            $this->defineTotalLengthOfSides();
            $this->appropriateInterval = $this->findInterval($this->fullLengthOfSides);
        } else {
            $this->appropriateInterval = null;
        }
    }

    /**
     * Calculates a total length of sides of all products.
     *
     * @return void
     */
    protected function defineTotalLengthOfSides(): void
    {
        $this->fullLengthOfSides = $this->getLengthOfSides() * $this->numberOfProducts;
    }

    /**
     * Calculates a length of sides of a product.
     *
     * @return void
     */
    protected function defineLengthOfSides(): void
    {
        $lengthOfSides = 0;

        if ($this->fourSides->isTopSideSelected()) {
            $lengthOfSides += $this->widthOfProduct;
        } else if ($this->fourSides->isRightSideSelected()) {
            $lengthOfSides += $this->heigthOfProduct;
        } else if ($this->fourSides->isBottomSideSelected()) {
            $lengthOfSides += $this->widthOfProduct;
        } else if ($this->fourSides->isLeftSideSelected()) {
            $lengthOfSides += $this->heigthOfProduct;
        }

        $this->lengthOfSides = $lengthOfSides;
    }

    /**
     * Returns data of the current instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return parent::toArray() + [
            'label' => $this->label,
        ];
    }
}
