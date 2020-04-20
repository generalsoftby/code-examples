<?php

namespace Tests\Feature\ResourceCalculators\Space;

use DateTime;
use App\Enums\SpacePriceTypeEnum;
use App\Services\ResourceCalculators\Space\Limits;
use App\Services\ResourceCalculators\Space\Schedule;
use App\Services\ResourceCalculators\Space\ScheduleCalculator;
use App\Services\ResourceCalculators\Space\RequirementCollection;
use App\Services\ResourceCalculators\Base\LimitsCollection;
use App\Services\ResourceCalculators\Base\TimeSlot;
use App\Services\ResourceCalculators\Base\TimeSlotCollection;
use App\Services\ResourceCalculators\Base\Reservation;
use App\Services\ResourceCalculators\Base\ReservationCollection;
use Tests\TestCase;

class ScheduleCalculatorTest extends TestCase
{
    /**
     * @param LimitsCollection $expected
     * @param array            $limitsList
     * @param array            $reservations
     *
     * @dataProvider getLimitsCollectionProvider
     */
    public function testGetLimitsCollection(LimitsCollection $expected, array $limitsList, array $reservations): void
    {
        $limitsCollection = new LimitsCollection($limitsList);
        $reservationCollection = new ReservationCollection(...$reservations);

        $scheduleCalculator = new ScheduleCalculator($limitsCollection, $reservationCollection);

        /** @var LimitsCollection $collection **/
        $collection = $scheduleCalculator->getLimitsCollection();
        $collection->rewind();

        $this->assertEquals($expected, $collection);
    }

    public function getLimitsCollectionProvider(): array
    {
        return [
            'empty' => [
                new LimitsCollection(),
                [],
                [],
            ],
            [
                new LimitsCollection([
                    1 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        15,
                        60,
                        1,
                        10,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    5 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        30,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    7 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        70,
                        SpacePriceTypeEnum::DAY_RATE
                    ),
                ]),
                [
                    1 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        15,
                        60,
                        1,
                        10,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    5 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        30,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    7 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        70,
                        SpacePriceTypeEnum::DAY_RATE
                    ),
                ],
                [
                    new Reservation(
                        1,
                        new DateTime('2020-02-26'),
                        DateTime::createFromFormat('H:i:s', '11:00:00'),
                        DateTime::createFromFormat('H:i:s', '13:00:00'),
                        3
                    ),
                    new Reservation(
                        1,
                        new DateTime('2020-02-27'),
                        DateTime::createFromFormat('H:i:s', '10:00:00'),
                        DateTime::createFromFormat('H:i:s', '12:00:00'),
                        5
                    ),
                    new Reservation(
                        3,
                        new DateTime('2020-02-26'),
                        DateTime::createFromFormat('H:i:s', '11:00:00'),
                        DateTime::createFromFormat('H:i:s', '11:30:00'),
                        2
                    ),
                ],
            ]
        ];
    }

    /**
     * @param ReservationCollection $expected
     * @param array                 $limitsList
     * @param array                 $reservations
     *
     * @dataProvider getReservationCollectionProvider
     */
    public function testGetReservationCollection(
        ReservationCollection $expected,
        array $limitsList,
        array $reservations
    ): void {
        $limitsCollection = new LimitsCollection($limitsList);
        $reservationCollection = new ReservationCollection(...$reservations);

        $scheduleCalculator = new ScheduleCalculator($limitsCollection, $reservationCollection);

        /** @var ReservationCollection $collection **/
        $collection = $scheduleCalculator->getReservationCollection();
        $collection->rewind();

        $this->assertEquals($expected, $collection);
    }

    public function getReservationCollectionProvider(): array
    {
        return [
            'empty' => [
                new ReservationCollection(),
                [],
                [],
            ],
            [
                new ReservationCollection(
                    new Reservation(
                        1,
                        new DateTime('2020-02-26'),
                        DateTime::createFromFormat('H:i:s', '11:00:00'),
                        DateTime::createFromFormat('H:i:s', '13:00:00'),
                        3
                    ),
                    new Reservation(
                        1,
                        new DateTime('2020-02-27'),
                        DateTime::createFromFormat('H:i:s', '10:00:00'),
                        DateTime::createFromFormat('H:i:s', '12:00:00'),
                        5
                    ),
                    new Reservation(
                        3,
                        new DateTime('2020-02-26'),
                        DateTime::createFromFormat('H:i:s', '11:00:00'),
                        DateTime::createFromFormat('H:i:s', '11:30:00'),
                        2
                    ),
                ),
                [
                    1 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        15,
                        60,
                        1,
                        10,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    5 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        30,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    7 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        70,
                        SpacePriceTypeEnum::DAY_RATE
                    ),
                ],
                [
                    new Reservation(
                        1,
                        new DateTime('2020-02-26'),
                        DateTime::createFromFormat('H:i:s', '11:00:00'),
                        DateTime::createFromFormat('H:i:s', '13:00:00'),
                        3
                    ),
                    new Reservation(
                        1,
                        new DateTime('2020-02-27'),
                        DateTime::createFromFormat('H:i:s', '10:00:00'),
                        DateTime::createFromFormat('H:i:s', '12:00:00'),
                        5
                    ),
                    new Reservation(
                        3,
                        new DateTime('2020-02-26'),
                        DateTime::createFromFormat('H:i:s', '11:00:00'),
                        DateTime::createFromFormat('H:i:s', '11:30:00'),
                        2
                    ),
                ],
            ]
        ];
    }

    /**
     * @param bool          $expected
     * @param array         $limits
     * @param array         $reservations
     * @param array         $requirements
     * @param DateTime|null $currentTime
     *
     * @dataProvider canFulfilRequirementsProvider
     */
    public function testCanFulfilRequirements(
        bool $expected,
        array $limits,
        array $reservations,
        array $requirements,
        DateTime $currentTime = null
    ): void {
        $limitsCollection = new LimitsCollection($limits);
        $reservationCollection = new ReservationCollection(...$reservations);
        $requirementCollection = new RequirementCollection();

        foreach ($requirements as $requirement) {
            $requirementCollection->createFromStrings(...$requirement);
        }

        $scheduleCalculator = new ScheduleCalculator($limitsCollection, $reservationCollection);

        $this->assertEquals($expected, $scheduleCalculator->canFulfilRequirements($requirementCollection, $currentTime));
    }

    public function canFulfilRequirementsProvider(): array
    {
        return [
            'free_slots' => [
                true,
                [
                    1 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        15,
                        60,
                        1,
                        10,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    5 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        30,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    7 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        720,
                        1,
                        70,
                        SpacePriceTypeEnum::DAY_RATE
                    ),
                ],
                [],
                [
                    // First
                    [1, '2020-02-21', '10:15:00', '11:15:00', 10],
                    [1, '2020-02-21', '13:00:00', '14:00:00', 5],
                    [1, '2020-02-21', '16:00:00', '17:30:00', 7],

                    // Seventh
                    [7, '2020-02-21', '08:00:00', '20:00:00', 50],
                ]
            ],
            [
                true,
                [
                    1 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        15,
                        60,
                        1,
                        10,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    5 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        30,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    7 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        70,
                        SpacePriceTypeEnum::DAY_RATE
                    ),
                ],
                [
                    new Reservation(
                        1,
                        new DateTime('2020-02-26'),
                        DateTime::createFromFormat('H:i:s', '11:00:00'),
                        DateTime::createFromFormat('H:i:s', '13:00:00'),
                        3
                    ),
                    new Reservation(
                        1,
                        new DateTime('2020-02-27'),
                        DateTime::createFromFormat('H:i:s', '10:00:00'),
                        DateTime::createFromFormat('H:i:s', '12:00:00'),
                        5
                    ),
                    new Reservation(
                        3,
                        new DateTime('2020-02-26'),
                        DateTime::createFromFormat('H:i:s', '11:00:00'),
                        DateTime::createFromFormat('H:i:s', '11:30:00'),
                        2
                    ),
                ],
                [
                    // First
                    [1, '2020-02-21', '08:00:00', '10:00:00', 7],
                    [1, '2020-02-21', '10:15:00', '11:15:00', 10],
                    [1, '2020-02-21', '13:00:00', '14:00:00', 5],
                    [1, '2020-02-21', '16:00:00', '17:30:00', 7],
                    [1, '2020-02-21', '18:00:00', '20:00:00', 7],

                    [1, '2020-02-26', '08:00:00', '10:40:00', 7],
                    [1, '2020-02-26', '13:15:00', '16:00:00', 1],

                    // Seventh
                    [7, '2020-02-21', '08:00:00', '20:00:00', 70],
                    [7, '2020-02-22', '08:00:00', '20:00:00', 70],
                ]
            ],
            [
                false,
                [
                    1 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        15,
                        60,
                        1,
                        10,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    5 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        30,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    7 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        70,
                        SpacePriceTypeEnum::DAY_RATE
                    ),
                ],
                [
                    new Reservation(
                        1,
                        new DateTime('2020-02-26'),
                        DateTime::createFromFormat('H:i:s', '11:00:00'),
                        DateTime::createFromFormat('H:i:s', '13:00:00'),
                        3
                    ),
                    new Reservation(
                        1,
                        new DateTime('2020-02-27'),
                        DateTime::createFromFormat('H:i:s', '10:00:00'),
                        DateTime::createFromFormat('H:i:s', '12:00:00'),
                        5
                    ),
                    new Reservation(
                        3,
                        new DateTime('2020-02-26'),
                        DateTime::createFromFormat('H:i:s', '11:00:00'),
                        DateTime::createFromFormat('H:i:s', '11:30:00'),
                        2
                    ),
                ],
                [
                  // First
                  [1, '2020-02-21', '08:00:00', '10:00:00', 7],
                  [1, '2020-02-21', '10:15:00', '11:15:00', 10],
                  [1, '2020-02-21', '13:00:00', '14:00:00', 5],
                  [1, '2020-02-21', '16:00:00', '17:30:00', 7],
                  [1, '2020-02-21', '18:00:00', '20:00:00', 7],

                  [1, '2020-02-26', '08:00:00', '10:40:00', 7],
                  [1, '2020-02-26', '12:00:00', '13:00:00', 1], // a mistake
                  [1, '2020-02-26', '13:15:00', '16:00:00', 1],

                  // Seventh
                  [7, '2020-02-21', '08:00:00', '20:00:00', 70],
                  [7, '2020-02-22', '08:00:00', '20:00:00', 70],
                ]
            ],
            [
                false,
                [
                    1 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        15,
                        60,
                        1,
                        10,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    5 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        30,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    7 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        720,
                        1,
                        70,
                        SpacePriceTypeEnum::DAY_RATE
                    ),
                ],
                [
                    new Reservation(
                        1,
                        new DateTime('2020-02-26'),
                        DateTime::createFromFormat('H:i:s', '11:00:00'),
                        DateTime::createFromFormat('H:i:s', '13:00:00'),
                        3
                    ),
                    new Reservation(
                        1,
                        new DateTime('2020-02-27'),
                        DateTime::createFromFormat('H:i:s', '10:00:00'),
                        DateTime::createFromFormat('H:i:s', '12:00:00'),
                        5
                    ),
                    new Reservation(
                        3,
                        new DateTime('2020-02-26'),
                        DateTime::createFromFormat('H:i:s', '11:00:00'),
                        DateTime::createFromFormat('H:i:s', '11:30:00'),
                        2
                    ),
                ],
                [
                  // First
                  [1, '2020-02-21', '08:00:00', '10:00:00', 7],
                  [1, '2020-02-21', '10:15:00', '11:15:00', 10],
                  [1, '2020-02-21', '13:00:00', '14:00:00', 5],
                  [1, '2020-02-21', '16:00:00', '17:30:00', 7],
                  [1, '2020-02-21', '18:00:00', '20:00:00', 7],

                  [1, '2020-02-26', '08:00:00', '10:40:00', 7],
                  [1, '2020-02-26', '13:15:00', '16:00:00', 1],

                  // Seventh
                  [7, '2020-02-21', '08:00:00', '20:00:00', 70],
                  [7, '2020-02-22', '08:00:00', '20:00:00', 70],
                  [7, '2020-02-23', '09:00:00', '20:00:00', 70], // a mistake
                ]
            ],
            [
                true,
                [
                    1 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        15,
                        60,
                        1,
                        10,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    5 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        30,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    7 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        70,
                        SpacePriceTypeEnum::DAY_RATE
                    ),
                ],
                [
                    new Reservation(
                        1,
                        new DateTime('2020-02-26'),
                        DateTime::createFromFormat('H:i:s', '11:00:00'),
                        DateTime::createFromFormat('H:i:s', '13:00:00'),
                        1
                    ),
                    new Reservation(
                        1,
                        new DateTime('2020-02-27'),
                        DateTime::createFromFormat('H:i:s', '10:00:00'),
                        DateTime::createFromFormat('H:i:s', '12:00:00'),
                        1
                    ),
                    new Reservation(
                        3,
                        new DateTime('2020-02-26'),
                        DateTime::createFromFormat('H:i:s', '11:00:00'),
                        DateTime::createFromFormat('H:i:s', '11:30:00'),
                        2
                    ),
                ],
                [
                    // First
                    [1, '2020-02-21', '08:00:00', '09:00:00', 7],
                    [1, '2020-02-21', '09:15:00', '10:15:00', 7],
                    [1, '2020-02-21', '10:30:00', '11:30:00', 10],
                    [1, '2020-02-21', '11:45:00', '12:45:00', 10],
                    [1, '2020-02-21', '13:00:00', '14:00:00', 1],
                    [1, '2020-02-21', '14:15:00', '15:15:00', 9],
                    [1, '2020-02-21', '15:30:00', '16:30:00', 9],
                    [1, '2020-02-21', '16:45:00', '17:45:00', 7],
                    [1, '2020-02-21', '18:00:00', '19:00:00', 7],
                ]
            ],
            [
                false,
                [
                    1 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        15,
                        60,
                        1,
                        10,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    5 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        30,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    7 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        70,
                        SpacePriceTypeEnum::DAY_RATE
                    ),
                ],
                [
                    new Reservation(
                        1,
                        new DateTime('2020-02-26'),
                        DateTime::createFromFormat('H:i:s', '11:00:00'),
                        DateTime::createFromFormat('H:i:s', '13:00:00'),
                        1
                    ),
                    new Reservation(
                        1,
                        new DateTime('2020-02-27'),
                        DateTime::createFromFormat('H:i:s', '10:00:00'),
                        DateTime::createFromFormat('H:i:s', '12:00:00'),
                        1
                    ),
                    new Reservation(
                        3,
                        new DateTime('2020-02-26'),
                        DateTime::createFromFormat('H:i:s', '11:00:00'),
                        DateTime::createFromFormat('H:i:s', '11:30:00'),
                        2
                    ),
                ],
                [
                    // First
                    [1, '2020-02-21', '08:00:00', '09:00:00', 7],
                    [1, '2020-02-21', '09:15:00', '10:15:00', 7],
                    [1, '2020-02-21', '10:30:00', '11:30:00', 10],
                    [1, '2020-02-21', '11:45:00', '12:45:00', 11], // overload of people
                    [1, '2020-02-21', '13:00:00', '14:00:00', 1],
                    [1, '2020-02-21', '14:15:00', '15:15:00', 9],
                    [1, '2020-02-21', '15:30:00', '16:30:00', 9],
                    [1, '2020-02-21', '16:45:00', '17:45:00', 7],
                    [1, '2020-02-21', '18:00:00', '19:00:00', 7],
                ]
            ],
            [
                false,
                [
                    1 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        15,
                        60,
                        1,
                        10,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    5 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        30,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    7 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        70,
                        SpacePriceTypeEnum::DAY_RATE
                    ),
                ],
                [
                    new Reservation(
                        1,
                        new DateTime('2020-02-26'),
                        DateTime::createFromFormat('H:i:s', '11:00:00'),
                        DateTime::createFromFormat('H:i:s', '13:00:00'),
                        1
                    ),
                    new Reservation(
                        1,
                        new DateTime('2020-02-27'),
                        DateTime::createFromFormat('H:i:s', '10:00:00'),
                        DateTime::createFromFormat('H:i:s', '12:00:00'),
                        1
                    ),
                    new Reservation(
                        3,
                        new DateTime('2020-02-26'),
                        DateTime::createFromFormat('H:i:s', '11:00:00'),
                        DateTime::createFromFormat('H:i:s', '11:30:00'),
                        2
                    ),
                ],
                [
                    // First
                    [1, '2020-02-21', '08:00:00', '10:00:00', 7],
                    [1, '2020-02-21', '10:15:00', '11:15:00', 10],
                    [1, '2020-02-21', '13:00:00', '14:00:00', 5],
                    [1, '2020-02-21', '16:00:00', '17:30:00', 7],
                    [1, '2020-02-21', '18:00:00', '20:00:00', 7],

                    [1, '2020-02-26', '08:00:00', '10:40:00', 7],
                    [1, '2020-02-26', '13:15:00', '16:00:00', 1],
                    [1, '2020-02-26', '14:00:00', '15:00:00', 1], // intersection

                    // Seventh
                    [7, '2020-02-21', '08:00:00', '20:00:00', 70],
                    [7, '2020-02-22', '08:00:00', '20:00:00', 70],
                ]
            ],
        ];
    }

    /**
     * @param Schedule|null $expected
     * @param array         $limitsList
     * @param array         $reservations
     * @param int           $id
     * @param DateTime      $date
     *
     * @dataProvider getScheduleProvider
     */
    public function testGetSchedule(
        ?Schedule $expected,
        array $limitsList,
        array $reservations,
        int $id,
        DateTime $date
    ): void {
        $limitsCollection = new LimitsCollection($limitsList);
        $reservationCollection = new ReservationCollection(...$reservations);

        $scheduleCalculator = new ScheduleCalculator($limitsCollection, $reservationCollection);

        $this->assertEquals($expected, $scheduleCalculator->getSchedule($id, $date));
    }

    public function getScheduleProvider(): array
    {
        return [
            'undefined' => [
                null,
                [],
                [],
                1,
                new DateTime('2020-02-27')
            ],
            'first_service_2020-02-26' => [
                new Schedule(
                    new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        10,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    new TimeSlotCollection(...[
                        new TimeSlot(
                            DateTime::createFromFormat('H:i:s', '11:00:00'),
                            DateTime::createFromFormat('H:i:s', '13:00:00'),
                            TimeSlot::BUSY_TYPE,
                            3
                        )
                    ])
                ),
                [
                    1 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        10,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    5 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        10,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    7 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        10,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                ],
                [
                    new Reservation(
                        1,
                        new DateTime('2020-02-26'),
                        DateTime::createFromFormat('H:i:s', '11:00:00'),
                        DateTime::createFromFormat('H:i:s', '13:00:00'),
                        3
                    ),
                    new Reservation(
                        1,
                        new DateTime('2020-02-27'),
                        DateTime::createFromFormat('H:i:s', '10:00:00'),
                        DateTime::createFromFormat('H:i:s', '12:00:00'),
                        1
                    ),
                ],
                1,
                new DateTime('2020-02-26')
            ],
            'first_service_2020-02-28' => [
                null,
                [
                    1 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        10,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    5 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        10,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    7 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        10,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                ],
                [
                    new Reservation(
                        1,
                        new DateTime('2020-02-26'),
                        DateTime::createFromFormat('H:i:s', '11:00:00'),
                        DateTime::createFromFormat('H:i:s', '13:00:00'),
                        3
                    ),
                    new Reservation(
                        1,
                        new DateTime('2020-02-27'),
                        DateTime::createFromFormat('H:i:s', '10:00:00'),
                        DateTime::createFromFormat('H:i:s', '12:00:00'),
                        5
                    ),
                    new Reservation(
                        3,
                        new DateTime('2020-02-26'),
                        DateTime::createFromFormat('H:i:s', '11:00:00'),
                        DateTime::createFromFormat('H:i:s', '11:30:00'),
                        2
                    ),
                ],
                1,
                new DateTime('2020-02-28')
            ],
            'third_service_2020-02-26' => [
                new Schedule(
                    new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        10,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    new TimeSlotCollection(...[
                        new TimeSlot(
                            DateTime::createFromFormat('H:i:s', '11:00:00'),
                            DateTime::createFromFormat('H:i:s', '13:00:00'),
                            TimeSlot::BUSY_TYPE,
                            2
                        )
                    ])
                ),
                [
                    1 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        10,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    3 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        10,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    5 => new Limits(
                        DateTime::createFromFormat('H:i:s', '11:00:00'),
                        DateTime::createFromFormat('H:i:s', '11:00:00'),
                        10,
                        120,
                        1,
                        10,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    7 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        10,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                ],
                [
                    new Reservation(
                        1,
                        new DateTime('2020-02-26'),
                        DateTime::createFromFormat('H:i:s', '11:00:00'),
                        DateTime::createFromFormat('H:i:s', '13:00:00'),
                        3
                    ),
                    new Reservation(
                        1,
                        new DateTime('2020-02-27'),
                        DateTime::createFromFormat('H:i:s', '10:00:00'),
                        DateTime::createFromFormat('H:i:s', '12:00:00'),
                        5
                    ),
                    new Reservation(
                        3,
                        new DateTime('2020-02-26'),
                        DateTime::createFromFormat('H:i:s', '11:00:00'),
                        DateTime::createFromFormat('H:i:s', '13:00:00'),
                        2
                    ),
                ],
                3,
                new DateTime('2020-02-26')
            ],
        ];
    }

    /**
     * @param array $expected
     * @param array $limitsList
     * @param array $reservations
     *
     * @dataProvider getSchedulesProvider
     */
    public function testGetSchedules(array $expected, array $limitsList, array $reservations): void
    {
        $limitsCollection = new LimitsCollection($limitsList);
        $reservationCollection = new ReservationCollection(...$reservations);

        $scheduleCalculator = new ScheduleCalculator($limitsCollection, $reservationCollection);

        $this->assertEquals($expected, $scheduleCalculator->getSchedules());
    }

    public function getSchedulesProvider(): array
    {
        return [
            'undefined' => [
                [],
                [],
                [],
            ],
            [
                [
                    1 => [
                          '2020-02-26' => new Schedule(
                              new Limits(
                                  DateTime::createFromFormat('H:i:s', '08:00:00'),
                                  DateTime::createFromFormat('H:i:s', '20:00:00'),
                                  10,
                                  120,
                                  1,
                                  10,
                                  SpacePriceTypeEnum::HOURS_PRICE
                              ),
                              new TimeSlotCollection(...[
                                  new TimeSlot(
                                      DateTime::createFromFormat('H:i:s', '11:00:00'),
                                      DateTime::createFromFormat('H:i:s', '13:00:00'),
                                      TimeSlot::BUSY_TYPE,
                                      3
                                  )
                              ])
                          ),
                          '2020-02-27' => new Schedule(
                              new Limits(
                                  DateTime::createFromFormat('H:i:s', '08:00:00'),
                                  DateTime::createFromFormat('H:i:s', '20:00:00'),
                                  10,
                                  120,
                                  1,
                                  10,
                                  SpacePriceTypeEnum::HOURS_PRICE
                              ),
                              new TimeSlotCollection(...[
                                  new TimeSlot(
                                      DateTime::createFromFormat('H:i:s', '10:00:00'),
                                      DateTime::createFromFormat('H:i:s', '12:00:00'),
                                      TimeSlot::BUSY_TYPE,
                                      5
                                  )
                              ])
                          ),
                    ],
                    3 => [
                        '2020-02-26' => new Schedule(
                            new Limits(
                                DateTime::createFromFormat('H:i:s', '08:00:00'),
                                DateTime::createFromFormat('H:i:s', '20:00:00'),
                                10,
                                120,
                                1,
                                10,
                                SpacePriceTypeEnum::HOURS_PRICE
                            ),
                            new TimeSlotCollection(...[
                                new TimeSlot(
                                    DateTime::createFromFormat('H:i:s', '11:00:00'),
                                    DateTime::createFromFormat('H:i:s', '11:30:00'),
                                    TimeSlot::BUSY_TYPE,
                                    2
                                )
                            ])
                        ),
                    ]
                ],
                [
                    1 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        10,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    3 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        10,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    5 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        10,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    7 => new Limits(
                        DateTime::createFromFormat('H:i:s', '08:00:00'),
                        DateTime::createFromFormat('H:i:s', '20:00:00'),
                        10,
                        120,
                        1,
                        10,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                ],
                [
                    new Reservation(
                        1,
                        new DateTime('2020-02-26'),
                        DateTime::createFromFormat('H:i:s', '11:00:00'),
                        DateTime::createFromFormat('H:i:s', '13:00:00'),
                        3
                    ),
                    new Reservation(
                        1,
                        new DateTime('2020-02-27'),
                        DateTime::createFromFormat('H:i:s', '10:00:00'),
                        DateTime::createFromFormat('H:i:s', '12:00:00'),
                        5
                    ),
                    new Reservation(
                        3,
                        new DateTime('2020-02-26'),
                        DateTime::createFromFormat('H:i:s', '11:00:00'),
                        DateTime::createFromFormat('H:i:s', '11:30:00'),
                        2
                    ),
                ],
            ],
        ];
    }
}
