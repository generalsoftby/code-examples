<?php

namespace App\Services\Calculators\FrontConverter\ReactFE;

/**
 * The abstract ResultItem.
 */
abstract class AbstractResultItem implements ResultItem
{
    /**
     * A type of the item.
     *
     * @var string
     */
    protected $type = 'abstract';

    /**
     * The debug state.
     *
     * @var bool
     */
    protected $debug = false;

    /**
     * Returns a type of the component.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Sets a debug state.
     *
     * @param  bool $state
     * @return void
     */
    public function setDebug(bool $state = true): void
    {
        $this->debug = $state;
    }

    /**
     * Checks whether the item is for debugging.
     *
     * @return bool
     */
    public function isDebugging(): bool
    {
        return $this->debug;
    }

    /**
     * Converts data to the array.
     *
     * @return array
     */
    abstract public function toArray(): array;
}
