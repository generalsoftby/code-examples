<?php

namespace App\Model\Calculator\CalculationVariables;

use App\Services\Calculators\Error;
use App\Services\Calculators\Errors;

/**
 * Keeps a list of ways of stitching.
 */
class StitchingType implements CalculationVariableEntity, \Countable, \Iterator, StatesOfVisibility
{
    /**
     * An array with stitching ways (types).
     *
     * @var array|StitchingWay[]
     */
    protected $ways = [];

    /**
     * A position of the pointer.
     *
     * @var int
     */
    protected $position = 0;

    /**
     * An appropriate way.
     *
     * @var StitchingWay|null
     */
    protected $appropriateWay;

    /**
     * Initializes an instance of the class.
     *
     * @param array|null $values
     */
    public function __construct(array $values = null)
    {
        $this->errors = new Errors;

        $this->fillFromArray($values ?? []);
    }

    /**
     * Counts a number of the ways.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->ways);
    }

    /**
     * Rewinds the pointer to the start.
     *
     * @return void
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Returns a current key.
     *
     * @return int
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Moves the pointer to the next position.
     *
     * @return StitchingWay|null
     */
    public function next(): ?StitchingWay
    {
        return $this->ways[++$this->position] ?? null;
    }

    /**
     * Returns the current type.
     *
     * @return StitchingWay|null
     */
    public function current(): ?StitchingWay
    {
        return $this->ways[$this->position] ?? null;
    }

    /**
     * Checks the current type.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->ways[$this->position]);
    }

    /**
     * Fills the instance from an array.
     *
     * @param  array $values
     * @return void
     */
    public function fillFromArray(array $values)
    {
        $this->ways = [];

        foreach ($values as $index => $valuesOfWay) {
            if (!isset($valuesOfWay['name'])) {
                $valuesOfWay['name'] = trans('calculation_variables.default_name_of_stitching_way') . $index;
            }

            $this->ways[] = new StitchingWay($valuesOfWay);
        }
    }

    /**
     * Returns ways of stitching.
     *
     * @return array|StitchingWay[]
     */
    public function getWays(): array
    {
        return $this->ways;
    }

    /**
     * Finds a way by the given index and name.
     *
     * @param  int    $index
     * @param  string $name
     * @return StitchingWay|null
     */
    public function findWay(int $index, string $name): ?StitchingWay
    {
        /** @var array|StitchingWay $ways */
        $ways = array_filter($this->ways, function (StitchingWay $way) use ($name) {
            return $way->getName() === $name;
        });

        if (!current($ways)) {
            return $this->ways[$index] ?? null;
        }

        return key($ways) === $index ? current($ways) : null;
    }

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

        return $this->defineAppropriateWay($values['index'], $values['name']) !== null;
    }

    /**
     * Defines an appropriate way.
     *
     * @param  int    $index
     * @param  string $name
     * @return StitchingWay|null
     */
    public function defineAppropriateWay(int $index, string $name): ?StitchingWay
    {
        return $this->appropriateWay = $this->findWay($index, $name);
    }

    /**
     * Returns an appropriate way.
     *
     * @return StitchingWay|null
     */
    public function getAppropriateWay(): ?StitchingWay
    {
        return $this->appropriateWay;
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

        if (!isset($data['index'], $data['name'])) {
            $this->errors->add(trans('calculator_errors.stitching_way_was_not_selected'), Error::VARIABLE_OF_CALCULATION_ERROR);
            $state = false;
        } elseif (!is_int($data['index'])) {
            $this->errors->add(
                trans('calculator_errors.index_of_stitching_type_is_undefined'), Error::VARIABLE_OF_CALCULATION_ERROR
            );
            $state = false;
        } elseif (!$this->findWay($data['index'], $data['name'])) {
            $this->errors->add(
                trans('calculator_errors.selected_stitching_way_not_found'), Error::VARIABLE_OF_CALCULATION_ERROR
            );
            $state = false;
        }

        return $state;
    }

    /**
     * Returns errors of the validation.
     *
     * @return Errors
     */
    public function getErrors(): Errors
    {
        return $this->errors;
    }

    /**
     * Returns states of block visibility. Keys are names of blocks.
     *
     * @return array|bool[]
     */
    public function getStatesOfVisibility(): array
    {
        return $this->appropriateWay
            ? [
                'cover' => $this->appropriateWay->isCoverUsed(),
                'substrate' => $this->appropriateWay->isSubstrateUsed(),
                'block' => $this->appropriateWay->isBlockUsed(),
            ]
            : []
        ;
    }

    /**
     * Returns data of the current instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_map(function (StitchingWay $way) {
            return $way->toArray();
        }, $this->ways);
    }
}
