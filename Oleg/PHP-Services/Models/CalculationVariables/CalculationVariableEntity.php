<?php

namespace App\Model\Calculator\CalculationVariables;

use App\Services\Calculators\Errors;
use Dios\System\Multicasting\Interfaces\SimpleArrayEntity;

/**
 * The base interface for entities of calculation variables.
 */
interface CalculationVariableEntity extends SimpleArrayEntity
{
    /**
     * Returns true whether user variables were filled with user values.
     *
     * @param  array $values
     * @return bool
     */
    public function fillWithUserValues(array $values): bool;

    /**
     * Validates given user data and returns result of the validation.
     *
     * @param  mixed $data
     * @return bool
     */
    public function validate($data): bool;

    /**
     * Returns errors of the validation.
     *
     * @return Errors
     */
    public function getErrors(): Errors;
}
