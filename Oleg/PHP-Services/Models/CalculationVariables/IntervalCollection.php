<?php

namespace App\Model\Calculator\CalculationVariables;

/**
 * Keeps intervals with ranges and prices.
 * Handles intervals and finds an appropriate interval by a value of the range.
 */
class IntervalCollection implements \Countable, \Iterator
{
    /**
     * An array with intervals.
     *
     * @var array|Interval[]
     */
    protected $intervals;

    /**
     * A position of the pointer.
     *
     * @var int
     */
    protected $position = 0;

    /**
     * Initializes an instance from the given array with intervals.
     *
     * @param array|Interval[] $intervals
     */
    public function __construct(array $intervals = [])
    {
        $this->intervals = array_values(array_filter($intervals, function ($interval) {
            return $interval instanceof Interval;
        }));
    }

    /**
     * Counts a number of intervals.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->intervals);
    }

    /**
     * Rewinds the pointer to start.
     *
     * @return void
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Returns the current key.
     *
     * @return int
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Moves the pointer to the next interval.
     *
     * @return void
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * Returns the current interval.
     *
     * @return Interval|null
     */
    public function current(): ?Interval
    {
        return $this->intervals[$this->position] ?? null;
    }

    /**
     * Checks the current interval exists.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->intervals[$this->position]);
    }

    /**
     * Fills the instance from the given array.
     *
     * @param  array $values
     * @return void
     */
    public function fillFromArray(array $values = [])
    {
        $this->intervals = [];

        foreach ($values as $valuesOfInterval) {
            if (isset($valuesOfInterval['price']['value'])) {
                $this->intervals[] = new Interval($valuesOfInterval);
            }
        }
    }

    /**
     * Returns appropriate intervals by the value.
     *
     * @param  float|int $value
     * @return IntervalCollection
     */
    public function getIntervalsByValue($value): self
    {
        /** @var array|Interval[] $appropriateIntervals **/
        $appropriateIntervals = array_filter($this->intervals, function (Interval $interval) use ($value) {
            return $interval->isIncluded($value);
        });

        return new self(array_values($appropriateIntervals));
    }

    /**
     * Returns an appropriate interval by the value.
     *
     * @param  float|int  $value
     * @return IntervalOfProduct|null
     */
    public function getIntervalByValue($value): ?Interval
    {
        /** @var IntervalCollection $intervals **/
        $intervals = $this->getIntervalsByValue($value);

        return count($intervals) ? $intervals->current() : null;
    }

    /**
     * Checks the value is included in the intervals.
     *
     * @param  float|int $value
     * @return bool
     */
    public function isIncluded($value): bool
    {
        /** @var Interval $interval */
        foreach ($this->intervals as $interval) {
            if ($interval->isIncluded($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns data of the current instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_map(function (Interval $interval) {
            return $interval->toArray();
        }, $this->intervals);
    }
}
