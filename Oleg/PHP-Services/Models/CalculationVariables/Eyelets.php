<?php

namespace App\Model\Calculator\CalculationVariables;

use App\Services\Calculators\Error;
use App\Support\Number;

/**
 * Calculates eyelets for products.
 */
class Eyelets extends AbstractCustomNumber
{
    use ToolProductSize;

    /**
     * Types of position of eyelets.
     */
    public const ANGLE_TYPE = 'angle';
    public const SIDE_TYPE = 'side';

    /**
     * A state of using of the eyelets.
     *
     * @var bool
     */
    protected $used = false;

    /**
     * A type of position of the eyelets.
     *
     * @var string|null
     */
    protected $type;

    /**
     * An instance of FourAngles.
     *
     * @var FourAngles
     */
    protected $fourAngles;

    /**
     * An instance of FourSides.
     *
     * @var FourSides
     */
    protected $fourSides;

    /**
     * A set of position. The distance between eyelets (cm).
     *
     * @var int|null
     */
    protected $step;

    /**
     * A number of eyelets of a product.
     *
     * @var int|null
     */
    protected $numberOfEyelets;

    /**
     * A total number of eyelets of all products.
     *
     * @var int|null
     */
    protected $totalNumberOfEyelets;

    /**
     * Initializes an instance of the class.
     *
     * @param array $values
     */
    public function __construct(array $values = null)
    {
        parent::__construct($values);

        $this->fourAngles = new FourAngles;
        $this->fourSides = new FourSides;
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
        $this->setType($values['type'] ?? null);

        if (isset($values['type']) && $values['type'] === self::ANGLE_TYPE) {
            $this->setAngles($values['angles'] ?? []);
            $this->setSides([]);
        } elseif (isset($values['type']) && $values['type'] === self::SIDE_TYPE) {
            $this->setAngles([]);
            $this->setSides($values['sides'] ?? []);
        } else {
            $this->setAngles([]);
            $this->setSides([]);
        }

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

        // If angles are used.
        if (empty($data['active'])) {
            return $state;
        }

        if (empty($data['type'])) {
            $this->errors->add(trans('calculator_errors.undefined_type_of_eyelets'), Error::VARIABLE_OF_CALCULATION_ERROR);
            $state = false;
        } elseif (!(is_string($data['type']) && $this->isTypeAllowed($data['type']))) {
            $this->errors->add(trans('calculator_errors.incorrect_type_of_eyelets'), Error::VARIABLE_OF_CALCULATION_ERROR);
            $state = false;
        }

        if ($data['type'] === self::ANGLE_TYPE) {
            $state = $this->validateAngles($data['angles'] ?? []);
        } elseif ($data['type'] === self::SIDE_TYPE) {

            if (!isset($data['step'])) {
                $this->errors->add(trans('calculator_errors.undefined_step_of_eyelets'), Error::VARIABLE_OF_CALCULATION_ERROR);
                $state = false;
            } elseif (!is_int($data['step'])) {
                $this->errors->add(trans('calculator_errors.step_of_eyelets_is_non_numeric'), Error::VARIABLE_OF_CALCULATION_ERROR);
                $state = false;
            } elseif ($data['step'] < 1) {
                $this->errors->add(trans('calculator_errors.step_of_eyelets_less_that_one'), Error::VARIABLE_OF_CALCULATION_ERROR);
                $state = false;
            }

            $state = $this->validateSides($data['sides'] ?? []);
        }

        return $state;
    }

    /**
     * Checks whether the given type is allowed.
     *
     * @param  string $type
     * @return bool
     */
    public function isTypeAllowed(string $type): bool
    {
        return $type === self::ANGLE_TYPE || $type === self::SIDE_TYPE;
    }

    /**
     * Validates the given angles.
     *
     * @param  array $angles
     * @return bool
     */
    public function validateAngles(array $angles): bool
    {
        $state = true;

        if (empty($angles)) {
            $this->errors->add(trans('calculator_errors.angles_of_eyelets_not_given'), Error::VARIABLE_OF_CALCULATION_ERROR);
            $state = false;
        } elseif (!$this->fourAngles->hasAngles($angles)) {
            $this->errors->add(trans('calculator_errors.incorrect_angles_of_eyelets'), Error::VARIABLE_OF_CALCULATION_ERROR);
            $state = false;
        } elseif (!$this->fourAngles->hasSelectedAngles($angles)) {
            $this->errors->add(trans('calculator_errors.angles_of_eyelets_are_unselected'), Error::VARIABLE_OF_CALCULATION_ERROR);
            $state = false;
        }

        return $state;
    }

    /**
     * Validates the given sides.
     *
     * @param  array $sides
     * @return bool
     */
    public function validateSides(array $sides): bool
    {
        $state = true;

        if (empty($sides)) {
            $this->errors->add(trans('calculator_errors.sides_of_eyelets_not_given'), Error::VARIABLE_OF_CALCULATION_ERROR);
            $state = false;
        } elseif (!$this->fourSides->hasSides($sides)) {
            $this->errors->add(trans('calculator_errors.incorrect_sides_of_eyelets'), Error::VARIABLE_OF_CALCULATION_ERROR);
            $state = false;
        } elseif (!$this->fourSides->hasSelectedSides($sides)) {
            $this->errors->add(trans('calculator_errors.sides_of_eyelets_are_unselected'), Error::VARIABLE_OF_CALCULATION_ERROR);
            $state = false;
        }

        return $state;
    }

    /**
     * Sets a type of the position.
     *
     * @param  string|null $type
     * @return void
     */
    public function setType(?string $type): void
    {
        $this->type = $type;
        $this->defineInterval();
    }

    /**
     * Sets the given states of angles.
     *
     * @param  array $angles
     * @return void
     */
    public function setAngles(array $angles): void
    {
        $this->fourAngles->setAnglesAndFalseForUndefined($angles);
        $this->defineInterval();
    }

    /**
     * Sets the given state of sides.
     *
     * @param  array $sides
     * @return void
     */
    public function setSides(array $sides): void
    {
        $this->fourSides->setSidesAndFalseForUndefined($sides);
        $this->defineInterval();
    }

    /**
     * Sets a step between eyelets.
     *
     * @param  int $step
     * @return void
     */
    public function setStep(int $step): void
    {
        $this->step = $step;
        $this->defineInterval();
    }

    /**
     * Checks whether the eyelets are used.
     *
     * @return bool
     */
    public function areEyeletsUsed(): bool
    {
        return $this->used;
    }

    /**
     * Returns a type of the position.
     *
     * @return string|null
     */
    public function getTypeOfPosition(): ?string
    {
        return $this->type;
    }

    /**
     * Returns a distance between two points of eyelets.
     *
     * @return int|null
     */
    public function getStepBetweenPoints(): ?int
    {
        return $this->step;
    }

    /**
     * Returns positions of points.
     *
     * @return array|string[]
     */
    public function getPositionsOfPoints(): array
    {
        switch ($this->type) {
            case self::ANGLE_TYPE:
                return $this->fourAngles->getNamesOfSelectedAngles();

            case self::SIDE_TYPE:
                return $this->fourSides->getNamesOfSelectedSides();

            default:
                return [];
        }
    }

    /**
     * Returns a calculated number of eyelets of a product by the current type
     * of the position.
     *
     * @return int|null
     */
    public function getNumberOfEyelets(): ?int
    {
        return $this->numberOfEyelets;
    }

    /**
     * Returns a total number of eyelets of all products.
     *
     * @return int|null
     */
    public function getTotalNumberOfEyelets(): ?int
    {
        return $this->totalNumberOfEyelets;
    }

    /**
     * Returns a number of eyelets of a product by used angles.
     *
     * @return int
     */
    public function getNumberOfEyeletsByAngles(): int
    {
        return $this->fourAngles->countSelectedAngles();
    }

    /**
     * Returns a number of eyelets of a product by used sides.
     *
     * @return int|null
     */
    public function getNumberOfEyeletsBySides(): ?int
    {
        return isset($this->heigthOfProduct, $this->widthOfProduct, $this->step)
            ? $this->calculateEyeletsBySides(
                $this->heigthOfProduct,
                $this->widthOfProduct,
                $this->step,
                $this->fourSides->isLeftSideSelected(),
                $this->fourSides->isRightSideSelected(),
                $this->fourSides->isTopSideSelected(),
                $this->fourSides->isBottomSideSelected()
            )
            : null
        ;
    }

    /**
     * Calculates number of eyelets by the given size of a product,
     * a step between eyelets and states of sides.
     *
     * @param  int  $height The height is the left legnth and the right length of the product.
     * @param  int  $width The width is the top legnth and the bottom length of the product.
     * @param  int  $step
     * @param  bool $left
     * @param  bool $right
     * @param  bool $top
     * @param  bool $bottom
     * @return int
     */
    public function calculateEyeletsBySides(
        int $height,
        int $width,
        int $step,
        bool $left,
        bool $right,
        bool $top,
        bool $bottom
    ): int {
        $length = Number::roundByMultiple($height, $step) * $left;
        $length += Number::roundByMultiple($height, $step) * $right;
        $length += Number::roundByMultiple($width, $step) * $top;
        $length += Number::roundByMultiple($width, $step) * $bottom;

        return $length / $step;
    }

    /**
     * Calculates a number of eyelets by the given type.
     *
     * @param  string $type
     * @return int|null
     */
    public function calculateNumberOfEyeletsByType(string $type): ?int
    {
        switch ($type) {
            case self::ANGLE_TYPE:
                return $this->getNumberOfEyeletsByAngles();

            case self::SIDE_TYPE:
                return $this->getNumberOfEyeletsBySides();

            default:
                return null;
        }
    }

    /**
     * Calculates a number of eyelets and finds an appropriate interval.
     *
     * @return void
     */
    protected function defineInterval(): void
    {
        if (!$this->type) {
            return;
        }

        $this->defineNumbersOfEyelets();

        $this->appropriateInterval = isset($this->numberOfEyelets)
            ? $this->customNumber->findInterval($this->numberOfEyelets)
            : null
        ;
    }

    /**
     * Defines numbers of eyelets for a product and all products.
     *
     * @return void
     */
    protected function defineNumbersOfEyelets(): void
    {
        $this->numberOfEyelets = $this->calculateNumberOfEyeletsByType($this->type);
        $this->totalNumberOfEyelets = isset($this->numberOfEyelets, $this->numberOfProducts)
            ? $this->numberOfEyelets * $this->numberOfProducts
            : null
        ;
    }
}
