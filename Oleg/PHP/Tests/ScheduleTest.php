<?php

namespace Tests\Feature\ResourceCalculators\Space;

use DateTime;
use App\Models\User;
use App\Models\Space;
use App\Enums\OrderStatusTypeEnum;
use App\Enums\SpacePriceTypeEnum;
use App\Services\ResourceCalculators\Space\Limits;
use App\Services\ResourceCalculators\Base\TimeSlot;
use App\Services\ResourceCalculators\Base\Reservation;
use App\Services\ResourceCalculators\Base\ReservationCollection;
use App\Services\ResourceCalculators\Base\TimeSlotCollection;
use App\Services\ResourceCalculators\Space\Requirement;
use App\Services\ResourceCalculators\Space\RequirementCollection;
use App\Services\ResourceCalculators\Space\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected Limits $limits;

    protected Schedule $schedule;

    public function setUp(): void
    {
        parent::setUp();

        /** @var Limits $limits **/
        $this->limits = $this->createLimits();

        $this->schedule = new Schedule($this->limits);
    }

    public function testConstructor(): void
    {
        $schedule = new Schedule($this->limits);

        $this->assertIsObject($schedule);
    }

    /**
     * @param array $expected
     * @param array $reservations
     *
     * @dataProvider reservationsProvider
     */
    public function testGetAndSetReservations(array $expected, array $reservations): void
    {
        $collection = new TimeSlotCollection(...$reservations);

        $this->schedule->setReservations($collection);

        $this->assertEquals($expected, $this->schedule->getReservations()->getTimeSlots());
    }

    public function reservationsProvider(): array
    {
        return [
            'all' => [
                'expected' => [
                    TimeSlot::createFromStrings('08:00:00', '09:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('09:15:00', '10:15:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('10:30:00', '11:30:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('11:45:00', '12:45:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('13:00:00', '14:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('14:15:00', '15:15:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('15:30:00', '16:30:00', TimeSlot::BUSY_TYPE, 10),
                ],
                'reservations' => [
                    TimeSlot::createFromStrings('08:00:00', '09:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('09:15:00', '10:15:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('10:30:00', '11:30:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('11:45:00', '12:45:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('13:00:00', '14:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('14:15:00', '15:15:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('15:30:00', '16:30:00', TimeSlot::BUSY_TYPE, 10),
                ],
            ],
            'with_other_types' => [
                'expected' => [
                    TimeSlot::createFromStrings('08:00:00', '09:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('09:15:00', '10:15:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('10:30:00', '11:30:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('11:45:00', '12:45:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('13:00:00', '14:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('14:15:00', '15:15:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('15:30:00', '16:30:00', TimeSlot::BUSY_TYPE, 10),
                ],
                'reservations' => [
                    TimeSlot::createFromStrings('08:00:00', '09:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('09:00:00', '09:15:00', TimeSlot::PAUSE_TYPE),
                    TimeSlot::createFromStrings('09:15:00', '10:15:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('10:15:00', '10:30:00', TimeSlot::PAUSE_TYPE),
                    TimeSlot::createFromStrings('10:30:00', '11:30:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('11:30:00', '11:45:00', TimeSlot::PAUSE_TYPE),
                    TimeSlot::createFromStrings('11:45:00', '12:45:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('12:45:00', '13:00:00', TimeSlot::PAUSE_TYPE),
                    TimeSlot::createFromStrings('13:00:00', '14:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('14:00:00', '14:15:00', TimeSlot::PAUSE_TYPE),
                    TimeSlot::createFromStrings('14:15:00', '15:15:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('15:15:00', '15:30:00', TimeSlot::PAUSE_TYPE),
                    TimeSlot::createFromStrings('15:30:00', '16:30:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('16:30:00', '16:45:00', TimeSlot::PAUSE_TYPE),
                    TimeSlot::createFromStrings('16:45:00', '17:00:00', TimeSlot::LOST_TYPE),
                ],
            ],
        ];
    }

    /**
     * @param array $expected
     *
     * @dataProvider calculateCapacityProvider
     */
    public function testCalculateCapacity(int $expected): void
    {
        $this->assertEquals($expected, $this->schedule->calculateCapacity());
    }

    public function calculateCapacityProvider(): array
    {
        return [
            [
                70
            ]
        ];
    }

    /**
     * @param int   $expected
     * @param array $reservations
     *
     * @dataProvider calculateResidualCapacityProvider
     */
    public function testCalculateResidualCapacity(int $expected, array $reservations): void
    {
        $collection = new TimeSlotCollection(...$reservations);

        $this->schedule->setReservations($collection);

        $this->assertEquals($expected, $this->schedule->calculateResidualCapacity());
    }

    public function calculateResidualCapacityProvider()
    {
        return [
            [
                'expected' => 20,
                'reservations' => [
                    TimeSlot::createFromStrings('08:00:00', '11:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('13:00:00', '15:45:00', TimeSlot::BUSY_TYPE, 10),
                ],
            ],
            [
                'expected' => 10,
                'reservations' => [
                    TimeSlot::createFromStrings('08:00:00', '11:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('11:30:00', '12:30:00', TimeSlot::BUSY_TYPE, 5),
                    TimeSlot::createFromStrings('13:00:00', '15:45:00', TimeSlot::BUSY_TYPE, 10),
                ],
            ],
        ];
    }

    /**
     * @param bool     $expected
     * @param array    $reservations
     * @param TimeSlot $reservation
     *
     * @dataProvider insertTimeSlotProvider
     */
    public function testInsertTimeSlot(bool $expected, array $reservations, TimeSlot $reservation): void
    {
        $collection = new TimeSlotCollection(...$reservations);

        $this->schedule->setReservations($collection);

        $this->assertEquals($expected, $this->schedule->insertTimeSlot($reservation));
    }

    public function insertTimeSlotProvider(): array
    {
        return [
            [
                'expected' => true,
                'reservations' => [],
                'reservation' => TimeSlot::createFromStrings('08:00:00', '09:00:00', TimeSlot::BUSY_TYPE, 10),
            ],
            [
                'expected' => true,
                'reservations' => [],
                'reservation' => TimeSlot::createFromStrings('08:00:00', '17:00:00', TimeSlot::BUSY_TYPE, 10),
            ],
            [
                'expected' => false,
                'reservations' => [],
                'reservation' => TimeSlot::createFromStrings('07:00:00', '09:00:00', TimeSlot::BUSY_TYPE, 10),
            ],
            [
                'expected' => false,
                'reservations' => [
                    TimeSlot::createFromStrings('08:00:00', '09:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('09:15:00', '10:15:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('10:30:00', '11:30:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('11:45:00', '12:45:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('13:00:00', '14:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('14:15:00', '15:15:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('15:30:00', '16:30:00', TimeSlot::BUSY_TYPE, 10),
                ],
                'reservation' => TimeSlot::createFromStrings('08:00:00', '09:00:00', TimeSlot::BUSY_TYPE, 10),
            ],
            [
                'expected' => true,
                'reservations' => [
                    TimeSlot::createFromStrings('08:00:00', '09:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('09:15:00', '10:15:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('10:30:00', '11:30:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('13:00:00', '14:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('14:15:00', '15:15:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('15:30:00', '16:30:00', TimeSlot::BUSY_TYPE, 10),
                ],
                'reservation' => TimeSlot::createFromStrings('11:45:00', '12:45:00', TimeSlot::BUSY_TYPE, 10),
            ],
        ];
    }

    /**
     * @param bool  $expected
     * @param array $slots
     * @param array $candidates
     *
     * @dataProvider canInsertTimeSlotsProvider
     */
    public function testCanInsertTimeSlots(bool $expected, array $slots, array $candidates): void
    {
        $collection = new TimeSlotCollection(...$slots);

        $this->schedule->setReservations($collection);

        $insertingCollection = new TimeSlotCollection(...$candidates);

        $this->assertEquals($expected, $this->schedule->canInsertTimeSlots($insertingCollection));
    }

    public function canInsertTimeSlotsProvider(): array
    {
        return [
            [
                'expected' => true,
                'reservations' => [],
                'candidates' => [
                    TimeSlot::createFromStrings('08:00:00', '09:00:00', TimeSlot::BUSY_TYPE, 10)
                ],
            ],
            [
                'expected' => false,
                'reservations' => [],
                'candidates' => [
                    TimeSlot::createFromStrings('08:00:00', '17:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('08:00:00', '17:00:00', TimeSlot::BUSY_TYPE, 10)
                ],
            ],
            [
                'expected' => false,
                'reservations' => [],
                'candidates' => [
                    TimeSlot::createFromStrings('07:00:00', '09:00:00', TimeSlot::BUSY_TYPE, 10)
                ],
            ],
            [
                'expected' => true,
                'reservations' => [
                    TimeSlot::createFromStrings('08:00:00', '09:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('09:15:00', '10:15:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('10:30:00', '11:30:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('11:45:00', '12:45:00', TimeSlot::BUSY_TYPE, 10),
                ],
                'candidates' => [
                    TimeSlot::createFromStrings('13:00:00', '14:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('14:15:00', '15:15:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('15:30:00', '16:30:00', TimeSlot::BUSY_TYPE, 10),
                ],
            ],
            [
                'expected' => true,
                'reservations' => [
                    TimeSlot::createFromStrings('08:00:00', '09:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('14:15:00', '15:15:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('15:30:00', '16:30:00', TimeSlot::BUSY_TYPE, 10),
                ],
                'candidates' => [
                    TimeSlot::createFromStrings('09:15:00', '10:15:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('10:30:00', '11:30:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('13:00:00', '14:00:00', TimeSlot::BUSY_TYPE, 10),
                ],
            ],
            [
                'expected' => false,
                'reservations' => [
                    TimeSlot::createFromStrings('08:00:00', '09:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('14:15:00', '15:15:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('15:30:00', '16:30:00', TimeSlot::BUSY_TYPE, 10),
                ],
                'candidates' => [
                    TimeSlot::createFromStrings('09:15:00', '10:15:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('10:00:00', '11:30:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('13:00:00', '14:00:00', TimeSlot::BUSY_TYPE, 10),
                ],
            ],
        ];
    }

    /**
     * @param array $expected
     * @param array $types
     *
     * @dataProvider designProfitableScheduleProvider
     */
    public function testDesignProfitableSchedule(array $expected, ?array $types = null): void
    {
        $schedule = $this->schedule->designProfitableSchedule($types);

        $this->assertEquals($expected, $schedule->getTimeSlots());
    }

    public function designProfitableScheduleProvider(): array
    {
        return [
            [
                'expected' => [
                    TimeSlot::createFromStrings('08:00:00', '09:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('09:00:00', '09:15:00', TimeSlot::PAUSE_TYPE),

                    TimeSlot::createFromStrings('09:15:00', '10:15:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('10:15:00', '10:30:00', TimeSlot::PAUSE_TYPE),

                    TimeSlot::createFromStrings('10:30:00', '11:30:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('11:30:00', '11:45:00', TimeSlot::PAUSE_TYPE),

                    TimeSlot::createFromStrings('11:45:00', '12:45:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('12:45:00', '13:00:00', TimeSlot::PAUSE_TYPE),

                    TimeSlot::createFromStrings('13:00:00', '14:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('14:00:00', '14:15:00', TimeSlot::PAUSE_TYPE),

                    TimeSlot::createFromStrings('14:15:00', '15:15:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('15:15:00', '15:30:00', TimeSlot::PAUSE_TYPE),

                    TimeSlot::createFromStrings('15:30:00', '16:30:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('16:30:00', '16:45:00', TimeSlot::PAUSE_TYPE),

                    TimeSlot::createFromStrings('16:45:00', '17:00:00', TimeSlot::LOST_TYPE),
                ],
            ],
            [
                'expected' => [
                    TimeSlot::createFromStrings('08:00:00', '09:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('09:15:00', '10:15:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('10:30:00', '11:30:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('11:45:00', '12:45:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('13:00:00', '14:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('14:15:00', '15:15:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('15:30:00', '16:30:00', TimeSlot::BUSY_TYPE, 10),
                ],
                'types' => [TimeSlot::BUSY_TYPE]
            ],
        ];
    }

    /**
     * @param array $expected
     * @param array $reservations
     *
     * @dataProvider designScheduleProvider
     */
    public function testDesignSchedule(array $expected, array $reservations): void
    {
        $collection = new TimeSlotCollection(...$reservations);

        $schedule = Schedule::designSchedule($this->limits, $collection);

        $this->assertEquals($expected, $schedule->getTimeSlots());
    }

    public function designScheduleProvider(): array
    {
        return [
            [
                'expected' => [
                    TimeSlot::createFromStrings('08:00:00', '11:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('11:00:00', '11:15:00', TimeSlot::PAUSE_TYPE),
                    TimeSlot::createFromStrings('11:15:00', '12:00:00', TimeSlot::LOST_TYPE),

                    TimeSlot::createFromStrings('12:00:00', '13:00:00', TimeSlot::BUSY_TYPE, 5),
                    TimeSlot::createFromStrings('13:00:00', '13:15:00', TimeSlot::PAUSE_TYPE),

                    TimeSlot::createFromStrings('13:15:00', '15:45:00', TimeSlot::FREE_TYPE),
                    TimeSlot::createFromStrings('15:45:00', '16:00:00', TimeSlot::POSSIBLE_PAUSE_TYPE),

                    TimeSlot::createFromStrings('16:00:00', '17:00:00', TimeSlot::BUSY_TYPE, 1),
                ],
                'reservations' => [
                    TimeSlot::createFromStrings('08:00:00', '11:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('12:00:00', '13:00:00', TimeSlot::BUSY_TYPE, 5),
                    TimeSlot::createFromStrings('16:00:00', '17:00:00', TimeSlot::BUSY_TYPE, 1),
                ],
            ],
            [
                'expected' => [
                    TimeSlot::createFromStrings('08:00:00', '17:00:00', TimeSlot::BUSY_TYPE, 10),
                ],
                'reservations' => [
                    TimeSlot::createFromStrings('08:00:00', '17:00:00', TimeSlot::BUSY_TYPE, 10),
                ],
            ],
            'lost_time_x3' => [
                'expected' => [
                    TimeSlot::createFromStrings('08:00:00', '08:30:00', TimeSlot::LOST_TYPE),
                    TimeSlot::createFromStrings('08:30:00', '10:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('10:00:00', '10:15:00', TimeSlot::PAUSE_TYPE),

                    TimeSlot::createFromStrings('10:15:00', '10:30:00', TimeSlot::LOST_TYPE),
                    TimeSlot::createFromStrings('10:30:00', '12:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('12:00:00', '12:15:00', TimeSlot::PAUSE_TYPE),
                    TimeSlot::createFromStrings('12:15:00', '14:15:00', TimeSlot::FREE_TYPE),
                    TimeSlot::createFromStrings('14:15:00', '14:30:00', TimeSlot::POSSIBLE_PAUSE_TYPE),

                    TimeSlot::createFromStrings('14:30:00', '15:30:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('15:30:00', '15:45:00', TimeSlot::PAUSE_TYPE),

                    TimeSlot::createFromStrings('15:45:00', '16:45:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('16:45:00', '17:00:00', TimeSlot::PAUSE_TYPE),
                ],
                'reservations' => [
                    TimeSlot::createFromStrings('08:30:00', '10:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('10:30:00', '12:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('14:30:00', '15:30:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('15:45:00', '16:45:00', TimeSlot::BUSY_TYPE, 10),
                ],
            ],
        ];
    }

    /**
     * @param array      $expected
     * @param array      $reservations
     * @param array|null $types
     *
     * @dataProvider designProbableScheduleProvider
     */
    public function testDesignProbableSchedule(array $expected, array $reservations, ?array $types = null): void
    {
        $collection = new TimeSlotCollection(...$reservations);

        $this->schedule->setReservations($collection);

        $schedule = $this
            ->schedule
            ->designProbableSchedule()
            ->filterByTypes($types)
            ->getTimeSlots()
        ;

        if (count($expected) != count($schedule)) {
            \var_dump($schedule);
        }

        $this->assertEquals($expected, $schedule);
    }

    public function designProbableScheduleProvider(): array
    {
        return [
            [
                'expected' => [
                    TimeSlot::createFromStrings('08:00:00', '11:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('13:00:00', '15:45:00', TimeSlot::BUSY_TYPE, 10),
                ],
                'reservations' => [
                    TimeSlot::createFromStrings('08:00:00', '11:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('13:00:00', '15:45:00', TimeSlot::BUSY_TYPE, 10),
                ],
                'types' => ['busy'],
            ],
            [
                'expected' => [
                    TimeSlot::createFromStrings('08:00:00', '11:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('11:00:00', '11:15:00', TimeSlot::PAUSE_TYPE),

                    TimeSlot::createFromStrings('11:15:00', '12:15:00', TimeSlot::CANDIDATE_TYPE, 10),
                    TimeSlot::createFromStrings('12:15:00', '12:30:00', TimeSlot::POSSIBLE_PAUSE_TYPE),

                    TimeSlot::createFromStrings('12:30:00', '13:00:00', TimeSlot::LOST_TYPE),
                    TimeSlot::createFromStrings('13:00:00', '15:45:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('15:45:00', '16:00:00', TimeSlot::PAUSE_TYPE),

                    TimeSlot::createFromStrings('16:00:00', '17:00:00', TimeSlot::CANDIDATE_TYPE, 10),
                ],
                'reservations' => [
                    TimeSlot::createFromStrings('08:00:00', '11:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('13:00:00', '15:45:00', TimeSlot::BUSY_TYPE, 10),
                ],
                'types' => null,
            ],
            [
                'expected' => [
                    TimeSlot::createFromStrings('08:00:00', '11:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('11:15:00', '12:15:00', TimeSlot::CANDIDATE_TYPE, 10),
                    TimeSlot::createFromStrings('13:00:00', '15:45:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('16:00:00', '17:00:00', TimeSlot::CANDIDATE_TYPE, 10),
                ],
                'reservations' => [
                    TimeSlot::createFromStrings('08:00:00', '11:00:00', TimeSlot::BUSY_TYPE, 10),
                    TimeSlot::createFromStrings('13:00:00', '15:45:00', TimeSlot::BUSY_TYPE, 10),
                ],
                'types' => ['busy', 'candidate'],
            ],
        ];
    }

    /**
     * @param array  $expected
     * @param array  $requirements
     * @param Limits $limits
     *
     * @dataProvider getReservationCollectionFromRequirementCollectionProvider
     */
    public function testGetReservationCollectionFromRequirementCollection(
        array $expected,
        array $requirements,
        Limits $limits
    ): void {
        $requirementCollection = new RequirementCollection(...$requirements);

        /** @var ReservationCollection $reservationCollection **/
        $reservationCollection = Schedule::getReservationCollectionFromRequirementCollection(
            $requirementCollection,
            $limits
        );

        $this->assertEquals($expected, $reservationCollection->getReservations());
    }

    public function getReservationCollectionFromRequirementCollectionProvider(): array
    {
        return [
            [
                [
                    new Reservation(
                        1,
                        new DateTime('2020-02-07'),
                        DateTime::createFromFormat('H:i:s', '12:00:00'),
                        DateTime::createFromFormat('H:i:s', '14:00:00'),
                        5
                    ),
                    new Reservation(
                        1,
                        new DateTime('2020-02-07'),
                        DateTime::createFromFormat('H:i:s', '16:00:00'),
                        DateTime::createFromFormat('H:i:s', '18:00:00'),
                        3
                    ),
                    new Reservation(
                        2,
                        new DateTime('2020-02-07'),
                        DateTime::createFromFormat('H:i:s', '16:00:00'),
                        DateTime::createFromFormat('H:i:s', '18:00:00'),
                        10
                    ),
                    new Reservation(
                        2,
                        new DateTime('2020-02-09'),
                        DateTime::createFromFormat('H:i:s', '14:30:00'),
                        DateTime::createFromFormat('H:i:s', '16:00:00'),
                        20
                    ),
                ],
                [
                    new Requirement(
                        1,
                        new DateTime('2020-02-07'),
                        DateTime::createFromFormat('H:i:s', '12:00:00'),
                        DateTime::createFromFormat('H:i:s', '14:00:00'),
                        5
                    ),
                    new Requirement(
                        1,
                        new DateTime('2020-02-07'),
                        DateTime::createFromFormat('H:i:s', '16:00:00'),
                        DateTime::createFromFormat('H:i:s', '18:00:00'),
                        3
                    ),
                    new Requirement(
                        2,
                        new DateTime('2020-02-07'),
                        DateTime::createFromFormat('H:i:s', '16:00:00'),
                        DateTime::createFromFormat('H:i:s', '18:00:00'),
                        10
                    ),
                    new Requirement(
                        2,
                        new DateTime('2020-02-09'),
                        DateTime::createFromFormat('H:i:s', '14:30:00'),
                        DateTime::createFromFormat('H:i:s', '16:00:00'),
                        20
                    ),
                ],
                new Limits(
                    DateTime::createFromFormat('H:i:s', '08:00:00'),
                    DateTime::createFromFormat('H:i:s', '17:00:00'),
                    15,
                    60,
                    1,
                    10,
                    SpacePriceTypeEnum::HOURS_PRICE
                )
            ]
        ];
    }

    public function createUser(int $userId = 1): User
    {
        return factory(User::class)->create([
                'id' => $userId,
        ]);
    }

    public function createSpace(int $userId = 1): Space
    {
        return factory(Space::class)->create([
              'user_id' => $userId,
              'min_occupancy' => 1,
              'max_occupancy' => 10,
              'pause_between' => 15,
              'start_time' => '08:00:00',
              'finish_time' => '17:00:00',
              'min_lease_time' => 60,
              'is_active' => true,
              'price_type' => SpacePriceTypeEnum::HOURS_PRICE,
        ]);
    }

    public function createLimits()
    {
        return new Limits(
            DateTime::createFromFormat('H:i:s', '08:00:00'),
            DateTime::createFromFormat('H:i:s', '17:00:00'),
            15,
            60,
            1,
            10,
            SpacePriceTypeEnum::HOURS_PRICE
        );
    }
}
