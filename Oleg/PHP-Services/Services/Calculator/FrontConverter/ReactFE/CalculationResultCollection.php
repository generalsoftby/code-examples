<?php

namespace App\Services\Calculators\FrontConverter\ReactFE;

/**
 * The collection keeps data of a result of a calculation
 * or errors of the calculation.
 * It is used for the calculation interface of React front-end.
 */
class CalculationResultCollection
{
    /**
     * An array with items of a result.
     *
     * @var array|ResultItem[]
     */
    protected $items = [];

    /**
     * The state of the debug mode.
     *
     * @var bool
     */
    protected $debugMode = false;

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Sets a debug mode.
     *
     * @param  bool $mode
     * @return void
     */
    public function setDebugMode(bool $mode = true): void
    {
        $this->debugMode = $mode;
    }

    /**
     * Returns a debug mode.
     *
     * @return bool
     */
    public function getDebugMode(): bool
    {
        return $this->debugMode;
    }

    /**
     * Pushes the given ResultItem to the collection.
     *
     * @param  ResultItem $resultItem
     * @return void
     */
    public function push(ResultItem $resultItem): void
    {
        $this->items[] = $resultItem;
    }

    /**
     * Adds a group title.
     *
     * @param  string $title
     * @return GroupTitle
     */
    public function addGroupTitle(string $title, bool $debug = false): GroupTitle
    {
        $groupTitle = new GroupTitle($title, true, $debug);
        $this->items[] = $groupTitle;

        return $groupTitle;
    }

    /**
     * Adds the separation.
     *
     * @return Separator
     */
    public function addSeparation(bool $debug = false): Separator
    {
        $separator = new Separator($debug);
        $this->items[] = $separator;

        return $separator;
    }

    /**
     * Adds a value.
     *
     * @param  string $title
     * @param  string $value
     * @return ItemValue
     */
    public function addValue(string $title, string $value, bool $debug = false): ItemValue
    {
        $itemValue = new ItemValue($title, $value, $debug);
        $this->items[] = $itemValue;

        return $itemValue;
    }

    /**
     * Merges the given value with the current collection.
     * Returns a new collection.
     *
     * @param  mixed|ItemValue|array|self $value
     * @return self
     */
    public function merge($value): self
    {
        if ($value instanceof ItemValue) {
            return $this->mergeWithValue($value);
        } elseif ($value instanceof self) {
            return $this->mergeWithCollection($value);
        }

        return $this->mergeWithArray($value);
    }

    /**
     * Merges the given value with the current collection.
     * Returns a new collection.
     *
     * @param  ItemValue $item
     * @return self
     */
    public function mergeWithValue(ItemValue $item): self
    {
        /** @var self $collection */
        $collection = clone $this;
        $collection->push($item);

        return $collection;
    }

    /**
     * Merges the given array with items with the current collection.
     *
     * @param  array|ItemValue[] $items
     * @return self
     */
    public function mergeWithArray(array $items): self
    {
        return $this->mergeWithCollection(new self($items));
    }

    /**
     * Merges the given collection with the current collection.
     * Returns a new collection.
     *
     * @param  self $collection
     * @return self
     */
    public function mergeWithCollection(self $collection): self
    {
        return new self(array_merge($this->items, $collection->getItems()));
    }

    /**
     * Filters items by a debug mode.
     *
     * @param  bool $state
     * @return self
     */
    public function filterByDebugMode(bool $state = true): self
    {
        $items = array_filter($this->items, function (ResultItem $item) use ($state) {
            return $item->isDebugging() === $state;
        });

        return new self(array_values($items));
    }

    /**
     * Returns items of the collection.
     *
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Returns an array with items of the calculation result.
     *
     * @return array
     */
    public function toArray(): array
    {
        $items = !$this->debugMode ? $this->filterByDebugMode(false)->getItems() : $this->items;

        return array_map(function (ResultItem $item) {
            return $item->toArray();
        }, $items);
    }
}
