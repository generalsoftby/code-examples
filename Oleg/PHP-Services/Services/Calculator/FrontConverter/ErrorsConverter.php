<?php

namespace App\Services\Calculators\FrontConverter;

use App\Services\Calculators\Errors;
use App\Services\Calculators\FrontConverter\Contracts\ErrorsConverter as ErrorsConverterInterface;
use App\Services\Calculators\FrontConverter\Traits\ThrowsNoneErrorsException;

/**
 * Converts the given errors to some formats.
 */
class ErrorsConverter implements ErrorsConverterInterface
{
    use ThrowsNoneErrorsException;

    /**
     * An instance of Errors.
     *
     * @var Errors
     */
    protected $errors;

    /**
     * Sets the given Errors.
     *
     * @param  Errors $errors
     * @return self
     */
    public function setInstanceOfErrors(Errors $errors): ErrorsConverterInterface
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * Returns an instance of Errors.
     *
     * @return Errors|null
     */
    public function getInstanceOfErrors(): ?Errors
    {
        return $this->errors;
    }

    /**
     * Checks whether the instance has an instance of Errors.
     *
     * @return bool
     */
    public function hasInstanceOfErrors(): bool
    {
        return isset($this->errors);
    }

    /**
     * Converts errors to an array of the format of React's front-end
     * of the calculator interface.
     *
     * @return array
     */
    public function toArrayOfReactFE(): array
    {
        $this->throwNoneErrorsException();

        return $this->errors->toArray();
    }
}
