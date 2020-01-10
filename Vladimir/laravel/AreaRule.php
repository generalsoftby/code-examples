<?php

namespace App\Rules\Area;

use Illuminate\Contracts\Validation\Rule;

class AreaRule implements Rule
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
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $regexp = '/^([1-9]+(\d*(\.|\,)?\d)?|0(\.|\,)\d)$/';
        return !$value || preg_match($regexp, $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Поле должно быть положительным числом с точностью до десятых.';
    }
}
