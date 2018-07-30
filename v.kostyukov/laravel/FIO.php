<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class FIO implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {

        $passes = true;
        if ($value) {
            $passes = is_string($value) && preg_match('/^[\pL\pM\-]+$/u', $value);
        }
        return $passes;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "Поле может содержать только буквы и '-'.";
    }
}
