<?php

namespace App\Services\Calculators\Blocks;

/**
 * The exception of duplicated names of calculation variables.
 */
class DuplicatedNamesOfCalculationVariablesException extends \Exception
{
    protected $message = "Blocks have duplicated names of calculation variables.";

    public function __construct(string $blockName = null)
    {
        if (isset($blockName)) {
            $this->message .= " Duplicating in the block: $blockName";
        }
    }
}
