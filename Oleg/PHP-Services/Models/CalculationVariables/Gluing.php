<?php

namespace App\Model\Calculator\CalculationVariables;

use App\Services\Calculators\Error;

/**
 * Gluing calculation variable implementation.
 */
class Gluing extends AbstractCustomNumber
{
    /**
     * A length of the gluing.
     *
     * @var int|null
     */
    protected $length;

    /**
     * A total length of the gluing.
     *
     * @var int|null
     */
    protected $totalLengthOfGluing;

    /**
     * A state of using of the gluing.
     *
     * @var bool
     */
    protected $used = false;

    /**
     * Returns true whether user variables were filled with user values.
     *
     * @param  array $values
     * @return bool
     */
    public function fillWithUserValues(array $values): bool
    {
        if (! $this->validate($values)) {
            return false;
        }

        $this->used = !empty($values['active']);
        $this->length =  $this->used ? (int) $values['length'] : null;
        $this->defineInterval();

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

        if (empty($data['active'])) {
            return $state;
        }

        if (!isset($data['length'])) {
            $this->errors->add(trans('calculator_errors.gluing_no_length'), Error::VARIABLE_OF_CALCULATION_ERROR);
            $state = false;
        } elseif (!is_int($data['length'])) {
            $this->errors->add(trans('calculator_errors.gluing_length_not_int'), Error::VARIABLE_OF_CALCULATION_ERROR);
            $state = false;
        } elseif ($data['length'] < 1) {
            $this->errors->add(trans('calculator_errors.gluing_less_than_one'), Error::VARIABLE_OF_CALCULATION_ERROR);
            $state = false;
        }

        return $state;
    }

    /**
     * Returns a length of the gluing.
     *
     * @return int|null
     */
    public function getLengthOfGluing(): ?int
    {
        return $this->length;
    }

    /**
     * Returns a total length of the gluing.
     *
     * @return int|null
     */
    public function getTotalLengthOfGluing(): ?int
    {
        return $this->totalLengthOfGluing;
    }

    /**
     * Returns a state of using of the gluing.
     *
     * @return bool
     */
    public function isUsed(): bool
    {
        return $this->used;
    }

    /**
     * Calculates a total length of the gluing.
     *
     * @return int|null
     */
    public function calculateTotalLengthOfGluing(int $numberOfProducts): ?int
    {
        return isset($this->length) ? $numberOfProducts * $this->length : null;
    }

    /**
     * Defines a total length and an appropriate interval.
     *
     * @return void
     */
    protected function defineInterval(): void
    {
        if ($this->used && isset($this->numberOfProducts, $this->length)) {
            $this->totalLengthOfGluing = $this->calculateTotalLengthOfGluing($this->numberOfProducts);
            $this->appropriateInterval = $this->findInterval($this->totalLengthOfGluing);
        }
    }
}
