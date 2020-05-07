<?php

namespace App\Rules;

use DateTime;
use App\Services\DT;
use Illuminate\Contracts\Validation\Rule;

class FutureDateRule implements Rule
{
    protected DateTime $currentDate;

    public function __construct(DateTime $currentDate = null)
    {
        $this->currentDate = $currentDate ?? new DateTime;
    }

    /**
     * Checks that a given date is greater or equal that the current date.
     *
     * @param string $attribute
     * @param string $date
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function passes($attribute, $date)
    {
        // YYYY-mm-dd = 10
        // If an empty string given then false is returned.
        // The empty string is converted as the current date.
        if (strlen($date) < 10) {
            return false;
        }

        try {
            $date = new DateTime($date);
        } catch (\Exception $e) {
            return false;
        }

        // It must return 1 or 0
        return DT::compareDate($this->currentDate, $date) >= 0;
    }

    /**
     * Returns the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute is less than the current date.';
    }
}
