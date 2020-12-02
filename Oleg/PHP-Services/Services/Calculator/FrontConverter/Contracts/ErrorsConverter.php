<?php

namespace App\Services\Calculators\FrontConverter\Contracts;

use App\Services\Calculators\Errors;

/**
 * The interface to implement a converter of errors.
 */
interface ErrorsConverter
{
    /**
     * Sets the given Errors.
     *
     * @param  Errors $errors
     * @return self
     */
    public function setInstanceOfErrors(Errors $errors): self;

    /**
     * Returns an instance of Errors.
     *
     * @return Errors|null
     */
    public function getInstanceOfErrors(): ?Errors;

    /**
     * Checks whether the instance has an instance of Errors.
     *
     * @return bool
     */
    public function hasInstanceOfErrors(): bool;

    /**
     * Converts errors to an array of the format of React's front-end
     * of the calculator interface.
     *
     * @return array
     */
    public function toArrayOfReactFE(): array;
}
