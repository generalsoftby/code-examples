<?php

namespace App\Services\Calculators\FrontConverter\ReactFE;

/**
 * The interface to items of a collection with a calculation result.
 */
interface ResultItem
{
    /**
     * Returns a type of the component.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Sets a debug state.
     *
     * @param  bool $state
     * @return void
     */
    public function setDebug(bool $state = true): void;

    /**
     * Checks whether the item is for debugging.
     *
     * @return bool
     */
    public function isDebugging(): bool;

    /**
     * Converts data of the item to an array.
     * Also must contain the 'type' property.
     *
     * @return array
     */
    public function toArray(): array;
}
