<?php

namespace App\Model\Calculator\CalculationVariables;

/**
 * The insterface is used by classes of calculation variables that change
 * states of block visibility.
 */
interface StatesOfVisibility
{
    /**
     * Returns states of block visibility. Keys are names of blocks.
     *
     * @return array|bool[]
     */
    public function getStatesOfVisibility(): array;
}
