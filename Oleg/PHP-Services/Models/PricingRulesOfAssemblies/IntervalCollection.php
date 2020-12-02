<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies;

/**
 * Keeps and handlers intervals.
 */
class IntervalCollection implements \Countable, \Iterator
{
    /**
     * An array with intervals.
     *
     * @var array|IntervalOfProduct[]
     */
    protected $intervals;

    /**
     * A position of the pointer.
     *
     * @var int
     */
    protected $position;

    /**
     * Initializes an instance of the class using intervals.
     *
     * @param array|IntervalOfProduct[] $intervals
     */
    function __construct(array $intervals = [])
    {
        $this->position = 0;
        $this->setIntervals($intervals);
    }

    /**
     * Returns a number of intervals.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->intervals);
    }

    /**
     * Resets the pointer.
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Returns a current IntervalOfProduct or null.
     *
     * @return IntervalOfProduct|null
     */
    public function current(): ?IntervalOfProduct
    {
        return $this->intervals[$this->position] ?? null;
    }

    /**
     * Returns a key of the pointer.
     *
     * @return int
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Moves the pointer further.
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * Checks whether the current interval exists.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->intervals[$this->position]);
    }

    /**
     * Sets intervals of IntervalOfProduct to the collection.
     *
     * @param array|IntervalOfProduct[] $intervals
     */
    public function setIntervals(array $intervals)
    {
        $this->intervals = $this->pullIntervalOfProducts($intervals);
    }

    /**
     * Returns an array with IntervalOfProduct.
     *
     * @return array|IntervalOfProduct[]
     */
    protected function pullIntervalOfProducts(array $intervals): array
    {
        $intervals = array_filter($intervals, function ($interval) {
            return $interval instanceof IntervalOfProduct;
        });

        return array_values($intervals);
    }

    /**
     * Returns appropriate intervals by the value.
     *
     * @param  float $value
     * @return IntervalCollection
     */
    public function getIntervalsByValue(float $value): self
    {
        /** @var array|IntervalOfProduct[] $appropriateIntervals **/
        $appropriateIntervals = array_filter($this->intervals, function (IntervalOfProduct $interval) use ($value) {
            return $interval->isIncluded($value);
        });

        return new self(array_values($appropriateIntervals));
    }

    /**
     * Returns an appropriate interval by the value.
     *
     * @param  float  $value
     * @return IntervalOfProduct|null
     */
    public function getIntervalByValue(float $value): ?IntervalOfProduct
    {
        /** @var IntervalCollection $intervals **/
        $intervals = $this->getIntervalsByValue($value);

        return count($intervals)
            ? $intervals->current()
            : null
        ;
    }

    /**
     * Returns an instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_map(function (IntervalOfProduct $interval) {
            return $interval->toArray();
        }, $this->intervals);
    }
}
