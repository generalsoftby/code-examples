<?php

namespace App\Services\ResourceCalculators\Base;

use DateTime;
use App\Services\Arr;
use App\Services\DT;

/**
 * A collection of the time slots.
 */
class TimeSlotCollection implements \Iterator, \Countable
{
    /**
     * The current position of the pointer.
     *
     * @var int
     */
    protected int $position;

    /**
     * A collection of the time slots.
     *
     * @var array|TimeSlot[]
     */
    protected array $collection;

    /**
     * @param TimeSlot ...$slots
     */
    public function __construct(TimeSlot ...$slots)
    {
        $this->position = 0;

        $this->collection = $slots;
    }

    /**
     * Reset the pointer of the collection.
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Returns the current time slot by current position if it exists else 'null'.
     *
     * @return TimeSlot|null
     */
    public function current(): ?TimeSlot
    {
        return $this->collection[$this->position];
    }

    /**
     * Returns the current key of the collection.
     *
     * @return int
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Goes to the next position of the pointer.
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * Checks is exists the current time slot.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->collection[$this->position]);
    }

    /**
     * Counts and returns a number of time slots of the collection.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->collection);
    }

    /**
     * Adds a new time slot to the end of the collection.
     *
     * @param TimeSlot $slot
     */
    public function add(TimeSlot $slot): void
    {
        $this->collection[] = $slot;
    }

    /**
     * Inserts a given time slot into a given position moving other time slots
     * or to the end of the collection.
     *
     * @param  TimeSlot $slot
     * @param  int|null $position
     */
    public function insert(TimeSlot $slot, ?int $position): void
    {
        if ($position === null || $position < 0) {
            $this->collection[] = $slot;
            return;
        }

        array_splice($this->collection, $position, 0, [$slot]);
    }

    /**
     * Inserts a given slot time nearby with a similar time.
     *
     * @param TimeSlot $slot
     */
    public function insertNearby(TimeSlot $slot): void
    {
        /** @var TimeSlot|bool **/
        $current = reset($this->collection);

        // When the slot less than the first time slot or when they are equal
        if (
            $current !== false
            && ($slot->getFinishTime() <= $current->getStartTime()
            || $slot->getStartTime() == $current->getStartTime())
        ) {
            $this->insert($slot, 0);
            return;
        }

        while (
            $current !== false
            && $current->getFinishTime() <= $slot->getStartTime()
        ) {
            $current = next($this->collection);
        }

        $this->insert($slot, key($this->collection) ?: null);
    }

    /**
     * Creates and adds a new time slot to the collection.
     * Aslo returns the created time slot.
     *
     * @param  DateTime $startTime
     * @param  DateTime $finishTime
     * @param  string   $type
     * @param  int      $numberOfResources
     * @return TimeSlot
     */
    public function create(
        DateTime $startTime,
        DateTime $finishTime,
        string $type,
        int $numberOfResources = 0
    ): TimeSlot {
        $slot = new TimeSlot($startTime, $finishTime, $type, $numberOfResources);

        $this->collection[] = $slot;

        return $slot;
    }

    /**
     * Replaces an existed slot with a given slot.
     *
     * @param  TimeSlot $slot
     * @param  int      $position
     * @return bool
     */
    public function replace(TimeSlot $slot, int $position): bool
    {
        if (isset($this->collection[$position]) === false) {
            return false;
        }

        $this->collection[$position] = $slot;

        return true;
    }

    /**
     * Finds a similar time slots and returns their keys.
     * Compares by time and a type.
     *
     * @param  TimeSlot $slot
     * @param  int      $startPosition
     * @param  int      ...$exclude
     * @return array|int[]
     */
    public function findSimilar(TimeSlot $slot, int $startPosition = 0, int ...$exclude): array
    {
        $similar = [];

        for ($i = $startPosition; $i < count($this->collection); $i++) {
            if (in_array($i, $exclude) === false && $this->collection[$i]->isSimilar($slot)) {
                $similar[] = $i;
            }
        }

        return $similar;
    }

    /**
     * Filters the collection by given IDs and returns a new collection.
     *
     * @param  int  ...$ids
     * @return TimeSlotCollection
     */
    public function filterByIds(int ...$ids): self
    {
        $slots = array_filter($this->collection, fn ($id) => in_array($id, $ids), ARRAY_FILTER_USE_KEY);

        return new static(...$slots);
    }

    /**
     * Filters the collection by given times and returns a new collection.
     *
     * @param  array|null $types
     * @return TimeSlotCollection
     */
    public function filterByTypes(array $types = null): self
    {
        $types = Arr::firstExists($types, TimeSlot::getTypes());

        $slots = array_filter($this->collection, fn ($slot) => in_array($slot->getType(), $types));

        return new static(...$slots);
    }

    /**
     * Filters the collection by a given time range and returns a new collection.
     *
     * @param  DateTime $startTime
     * @param  DateTime $finishTime
     * @return TimeSlotCollection
     */
    public function filterByTimeRange(DateTime $startTime, DateTime $finishTime): self
    {
        $slots = array_filter($this->collection, function ($slot) use ($startTime, $finishTime) {
            return DT::compareTime($slot->getStartTime(), $startTime) === 0
                && DT::compareTime($slot->getFinishTime(), $finishTime) === 0
            ;
        });

        return new static(...$slots);
    }

    /**
     * Sorts a collection by the start time column and the types.
     * Returns the collection sorted in order of the start time.
     * The candidates with same start time stand after the busy type.
     *
     * @return TimeSlotCollection
     */
    public function sortByStartTime(): self
    {
        /** @var array|TimeSlot[] $collection **/
        $collection = $this->collection;

        usort($collection, function (TimeSlot $slot1, TimeSlot $slot2) {
            if (DT::compareTime($slot1->getStartTime(), $slot2->getStartTime()) === 0
                && $slot1->getType() === TimeSlot::CANDIDATE_TYPE
            ) {
                return 1;
            }

            return (-1) * DT::compareTime($slot1->getStartTime(), $slot2->getStartTime());
        });

        return new static(...$collection);
    }

    /**
     * Counts and returns a number of resources set in time slots. If a given
     * the $currentTime then it uses as a minimal possible time.
     *
     * @param  DateTime|null $currentTime
     * @return int
     */
    public function countNumberOfResources(DateTime $currentTime = null): int
    {
        $amount = 0;

        foreach ($this->collection as $slot) {
            if ($currentTime === null
                || ($currentTime
                && DT::compareTime($currentTime, $slot->getStartTime()) >= 0)
            ) {
                $amount += $slot->getNumberOfResources();
            }
        }

        return $amount;
    }

    /**
     * Returns a collection with BUSY_TYPE and CANDIDATE_TYPE types.
     *
     * @return TimeSlotCollection
     */
    public function getActives(): self
    {
        return $this->filterByTypes([
            TimeSlot::BUSY_TYPE, TimeSlot::CANDIDATE_TYPE
        ]);
    }

    /**
     * Returns an array of time slots.
     *
     * @return array|TimeSlot[]
     */
    public function getTimeSlots(): array
    {
        return $this->collection;
    }

    /**
     * Returns the current collection of time slots in the form of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = [];

        foreach ($this->collection as $slot) {
            $array[] = $slot->toArray();
        }

        return $array;
    }
}
