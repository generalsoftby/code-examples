<?php

namespace App\Services\ResourceCalculators\Base;

use DateTime;

class TimeSlot
{
    public const TIME_FORMAT = 'H:i:s';

    /**
     * The time slot is used. It can not be used other people.
     *
     * @var string
     */
    const BUSY_TYPE = 'busy';

    /**
     * The time slot is free. It can be used anyone man.
     *
     * @var string
     */
    const FREE_TYPE = 'free';

    /**
     * The time slot is lost. It can not be used.
     *
     * @var string
     */
    const LOST_TYPE = 'lost';

    /**
     * The time slot is used for cleaning or other operations.
     *
     * @var string
     */
    const PAUSE_TYPE = 'pause';

    /**
     * The time slot is not used, but it can be used people.
     *
     * @var string
     */
    const CANDIDATE_TYPE = 'candidate';

    /**
     * The time slot is not used, it can be used for the pause.
     *
     * @var string
     */
    const POSSIBLE_PAUSE_TYPE = 'possible_pause';

    /**
     * A start time of the time slot.
     *
     * @var DateTime
     */
    protected DateTime $startTime;

    /**
     * A finish time of the time slot.
     *
     * @var DateTime
     */
    protected DateTime $finishTime;

    /**
     * A type of the time slot.
     *
     * @var DateTime
     */
    protected string $type;

    /**
     * A number of resources of the time slot.
     *
     * @var DateTime
     */
    protected int $numberOfResources;

    /**
     * @param DateTime $startTime
     * @param DateTime $finishTime
     * @param string   $type
     * @param integer  $numberOfResources
     */
    public function __construct(
        DateTime $startTime,
        DateTime $finishTime,
        string $type = self::FREE_TYPE,
        int $numberOfResources = 0
    ) {
        $this->startTime = $startTime;
        $this->finishTime = $finishTime;
        $this->numberOfResources = $numberOfResources;
        $this->type = static::isTypeExists($type) ? $type : self::FREE_TYPE;
    }

    /**
     * Creates and returns a time slot.
     *
     * @param  string  $startTime
     * @param  string  $finishTime
     * @param  string  $type
     * @param  integer $number
     * @param  string  $format
     * @return TimeSlot
     */
    public static function createFromStrings(
        string $startTime,
        string $finishTime,
        string $type = self::FREE_TYPE,
        int $number = 0,
        string $format = 'H:i:s'
    ): self {
        return new static(
            DateTime::createFromFormat($format, $startTime),
            DateTime::createFromFormat($format, $finishTime),
            $type,
            $number
        );
    }

    /**
     * Returns a start time of the time slot.
     *
     * @return DateTime
     */
    public function getStartTime(): DateTime
    {
        return $this->startTime;
    }

    /**
     * Returns a finish time of the time slot.
     *
     * @return DateTime
     */
    public function getFinishTime(): DateTime
    {
        return $this->finishTime;
    }

    /**
     * Returns a type of the time slot.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Returns a number of resources used in the time slot.
     *
     * @return int
     */
    public function getNumberOfResources(): int
    {
        return $this->numberOfResources;
    }

    /**
     * Returns true if a given TimeSlot is similar to the current TimeSlot.
     *
     * @param  TimeSlot $slot
     * @return bool
     */
    public function isSimilar(TimeSlot $slot): bool
    {
        return $this->type === $slot->getType()
            && $slot->getStartTime() == $this->startTime
            && $slot->getFinishTime() == $this->finishTime
        ;
    }

    /**
     * Checks if type is exists.
     *
     * @param  string $type
     * @return bool
     */
    public static function isTypeExists(string $type): bool
    {
        return in_array($type, static::getTypes());
    }

    /**
     * Returns all possible types of the TimeSlot.
     *
     * @return array
     */
    public static function getTypes(): array
    {
        return [
            self::BUSY_TYPE, self::FREE_TYPE, self::LOST_TYPE,
            self::PAUSE_TYPE, self::POSSIBLE_PAUSE_TYPE, self::CANDIDATE_TYPE,
        ];
    }

    /**
     * Returns the instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'start_time' => $this->startTime->format(self::TIME_FORMAT),
            'finish_time' => $this->finishTime->format(self::TIME_FORMAT),
            'type' => $this->type,
            'number' => $this->numberOfResources,
        ];
    }
}
