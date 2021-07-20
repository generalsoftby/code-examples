<?php

namespace App\Services\Calculators\Contracts;

/**
 * The interface of an error source.
 */
interface ErrorSource
{
    /**
     * Returns a type of the source.
     *
     * @return string 
     */
    public function getType(): string;

    /**
     * Returns an array that contains description of the error source.
     *
     * @return array
     */
    public function toArray(): array;
}
