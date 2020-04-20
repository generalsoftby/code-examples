<?php

namespace App\Rules;

use DateTime;
use App\Services\ResourceCalculators\Space\Requirement;
use App\Services\ResourceCalculators\Space\SpaceCalculator;
use Illuminate\Contracts\Validation\Rule;

class AvailabilityOfSpaceRule implements Rule
{
    use SpaceColumnsValidator;

    protected string $idColumn;

    protected string $dateColumn;

    protected string $startTimeColumn;

    protected string $finishTimeColumn;

    protected string $peopleColumn;

    public function __construct(
        string $idColumn,
        string $dateColumn = 'date',
        string $startTimeColumn = 'start_time',
        string $finishTimeColumn = 'finish_time',
        string $peopleColumn = 'occupancy'
    ) {
        $this->idColumn = $idColumn;
        $this->dateColumn = $dateColumn;
        $this->startTimeColumn = $startTimeColumn;
        $this->finishTimeColumn = $finishTimeColumn;
        $this->peopleColumn = $peopleColumn;
    }

    /**
     * Checks a available balance and returns 'true' if the needed amount
     * of a requited resource is available.
     *
     * @param  string    $type
     * @param  array     $orders
     * @return bool
     */
    public function passes($attribute, $order)
    {
        $calculator = new SpaceCalculator();

        $requirement = Requirement::createFromStrings(
            $order[$this->idColumn],
            $order[$this->dateColumn],
            $order[$this->startTimeColumn],
            $order[$this->finishTimeColumn],
            $order[$this->peopleColumn]
        );

        return $calculator->isFreeTimeSlot($requirement, new DateTime);
    }

    /**
     * Returns the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute exceeds available number of people.';
    }
}
