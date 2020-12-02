<?php

namespace App\Services\Calculators\Blocks;

/**
 * Keeps and handles data about a calculation variable of contents.
 * It is used for keeping a configuration of components.
 */
interface ComponentConfiguration
{
    /**
     * Returns data of the instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array;
}
