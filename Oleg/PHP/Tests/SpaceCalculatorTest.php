<?php

namespace Tests\Feature\ResourceCalculators\Space;

use DateTime;
use App\Models\User;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Space;
use App\Enums\OrderStatusTypeEnum;
use App\Enums\SpacePriceTypeEnum;
use App\Services\ResourceCalculators\Space\SpaceCalculator;
use App\Services\ResourceCalculators\Space\ScheduleCalculator;
use App\Services\ResourceCalculators\Space\Limits;
use App\Services\ResourceCalculators\Base\Reservation;
use App\Services\ResourceCalculators\Base\ReservationCollection;
use App\Services\ResourceCalculators\Base\LimitsCollection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpaceCalculatorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createUsers();

        $this->calculator = new SpaceCalculator();

        $inventories = $this->createUserSpaces();
        $orders = $this->createUserOrders();

        foreach ($orders as $order) {
            $this->setSpacesDataToOrder($order);
        }
    }

    /**
     * @param int        $id
     * @param Limits|null $expected
     *
     * @dataProvider limitsProvider
     */
    public function testGetLimits(int $id, ?Limits $expected): void
    {
        $this->assertEquals($expected, $this->calculator->getLimits($id));
    }

    public function limitsProvider(): array
    {
        return [
            'first_space' => [
                'id' => 1,
                'expected' => new Limits(
                    DateTime::createFromFormat('H:i:s', '10:00:00'),
                    DateTime::createFromFormat('H:i:s', '18:00:00'),
                    15,
                    60,
                    1,
                    10,
                    SpacePriceTypeEnum::HOURS_PRICE
                ),
            ],
            'second_space' => [
                'id' => 2,
                'expected' => new Limits(
                    DateTime::createFromFormat('H:i:s', '10:00:00'),
                    DateTime::createFromFormat('H:i:s', '18:00:00'),
                    15,
                    120,
                    5,
                    20,
                    SpacePriceTypeEnum::HOURS_PRICE
                ),
            ],
            'inactive_space' => [
                'id' => 5,
                'expected' => null,
            ],
        ];
    }

    /**
     * @param array       $expected
     * @param array|int[] $ids
     *
     * @dataProvider limitsCollectionProvider
     */
    public function testGetLimitsCollection(array $expected, array $ids): void
    {
        $this->assertEquals(new LimitsCollection($expected), $this->calculator->getLimitsCollection(...$ids));
    }

    public function limitsCollectionProvider(): array
    {
        return [
            'two_spaces' => [
                'expected' => [
                    1 => new Limits(
                        DateTime::createFromFormat('H:i:s', '10:00:00'),
                        DateTime::createFromFormat('H:i:s', '18:00:00'),
                        15,
                        60,
                        1,
                        10,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                    2 => new Limits(
                        DateTime::createFromFormat('H:i:s', '10:00:00'),
                        DateTime::createFromFormat('H:i:s', '18:00:00'),
                        15,
                        120,
                        5,
                        20,
                        SpacePriceTypeEnum::HOURS_PRICE
                    ),
                ],
                'ids' => [1, 2],
            ],
            'inactive_space' => [
                'expected' => [],
                'ids' => [5],
            ],
            'single_space' => [
                'expected' => [
                    2 => new Limits(
                        DateTime::createFromFormat('H:i:s', '10:00:00'),
                        DateTime::createFromFormat('H:i:s', '18:00:00'),
                        15,
                        120,
                        5,
                        20,
                        SpacePriceTypeEnum::HOURS_PRICE
                    )
                ],
                'ids' => [2],
            ]
        ];
    }

    /**
     * @param array $expected
     * @param array $ids
     * @param array $dates
     *
     * @dataProvider getReservationCollectionProvider
     */
    public function testGetReservationCollection(array $expected, array $ids, array $dates): void
    {
        /** @var ReservationCollection $collection **/
        $collection = $this->calculator->getReservationCollection($ids, $dates);

        $this->assertEquals($expected, $collection->getReservations());
    }

    public function getReservationCollectionProvider(): array
    {
        return [
            'empty' => [
                [],
                [6, 5, 7],
                [
                    new DateTime('2020-02-25'),
                    new DateTime('2020-02-26'),
                    new DateTime('2020-02-27'),
                ],
            ],
            'first_space' => [
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
                        1,
                        new DateTime('2020-02-10'),
                        DateTime::createFromFormat('H:i:s', '10:10:00'),
                        DateTime::createFromFormat('H:i:s', '11:30:00'),
                        7
                    ),
                    new Reservation(
                        1,
                        new DateTime('2020-02-10'),
                        DateTime::createFromFormat('H:i:s', '11:50:00'),
                        DateTime::createFromFormat('H:i:s', '12:50:00'),
                        9
                    ),
                    new Reservation(
                        1,
                        new DateTime('2020-02-10'),
                        DateTime::createFromFormat('H:i:s', '13:30:00'),
                        DateTime::createFromFormat('H:i:s', '15:00:00'),
                        8
                    ),
                    new Reservation(
                        1,
                        new DateTime('2020-02-10'),
                        DateTime::createFromFormat('H:i:s', '15:30:00'),
                        DateTime::createFromFormat('H:i:s', '16:30:00'),
                        8
                    ),
                    new Reservation(
                        1,
                        new DateTime('2020-02-10'),
                        DateTime::createFromFormat('H:i:s', '16:45:00'),
                        DateTime::createFromFormat('H:i:s', '18:00:00'),
                        8
                    ),
                ],
                [1],
                [
                    new DateTime('2020-02-07'),
                    new DateTime('2020-02-08'),
                    new DateTime('2020-02-09'),
                    new DateTime('2020-02-10'),
                ],
            ],
            'inactive_space' => [
                [
                ],
                [5],
                [new DateTime('2020-02-25')],
            ],
            'orders_is_not_found' => [
                [
                ],
                [4],
                [new DateTime('2020-02-11')],
            ],
            'all_spaces' => [
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
                        1,
                        new DateTime('2020-02-10'),
                        DateTime::createFromFormat('H:i:s', '10:10:00'),
                        DateTime::createFromFormat('H:i:s', '11:30:00'),
                        7
                    ),
                    new Reservation(
                        1,
                        new DateTime('2020-02-10'),
                        DateTime::createFromFormat('H:i:s', '11:50:00'),
                        DateTime::createFromFormat('H:i:s', '12:50:00'),
                        9
                    ),
                    new Reservation(
                        1,
                        new DateTime('2020-02-10'),
                        DateTime::createFromFormat('H:i:s', '13:30:00'),
                        DateTime::createFromFormat('H:i:s', '15:00:00'),
                        8
                    ),
                    new Reservation(
                        1,
                        new DateTime('2020-02-10'),
                        DateTime::createFromFormat('H:i:s', '15:30:00'),
                        DateTime::createFromFormat('H:i:s', '16:30:00'),
                        8
                    ),
                    new Reservation(
                        1,
                        new DateTime('2020-02-10'),
                        DateTime::createFromFormat('H:i:s', '16:45:00'),
                        DateTime::createFromFormat('H:i:s', '18:00:00'),
                        8
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
                    new Reservation(
                        3,
                        new DateTime('2020-02-09'),
                        DateTime::createFromFormat('H:i:s', '16:00:00'),
                        DateTime::createFromFormat('H:i:s', '18:00:00'),
                        8
                    ),
                ],
                [1, 2, 3],
                [
                    new DateTime('2020-02-07'),
                    new DateTime('2020-02-08'),
                    new DateTime('2020-02-09'),
                    new DateTime('2020-02-10'),
                ],
            ]
        ];
    }

    /**
     * @param ScheduleCalculator $expected
     * @param array              $ids
     * @param array              $dates
     *
     * @dataProvider getScheduleCalculatorProvider
     */
    public function testGetScheduleCalculator(ScheduleCalculator $expected, array $ids, array $dates): void
    {
        $this->assertEquals($expected, $this->calculator->getScheduleCalculator($ids, $dates));
    }

    public function getScheduleCalculatorProvider(): array
    {
        return [
            'empty' => [
                new ScheduleCalculator(
                    new LimitsCollection(),
                    new ReservationCollection()
                ),
                [10, 20, 30],
                [new DateTime('2020-02-25')]
            ],
            [
                new ScheduleCalculator(
                    new LimitsCollection([
                        1 => new Limits(
                            DateTime::createFromFormat('H:i:s', '10:00:00'),
                            DateTime::createFromFormat('H:i:s', '18:00:00'),
                            15,
                            60,
                            1,
                            10,
                            SpacePriceTypeEnum::HOURS_PRICE
                        ),
                        2 => new Limits(
                            DateTime::createFromFormat('H:i:s', '10:00:00'),
                            DateTime::createFromFormat('H:i:s', '18:00:00'),
                            15,
                            120,
                            5,
                            20,
                            SpacePriceTypeEnum::HOURS_PRICE
                        ),
                    ]),
                    new ReservationCollection(
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
                            1,
                            new DateTime('2020-02-10'),
                            DateTime::createFromFormat('H:i:s', '10:10:00'),
                            DateTime::createFromFormat('H:i:s', '11:30:00'),
                            7
                        ),
                        new Reservation(
                            1,
                            new DateTime('2020-02-10'),
                            DateTime::createFromFormat('H:i:s', '11:50:00'),
                            DateTime::createFromFormat('H:i:s', '12:50:00'),
                            9
                        ),
                        new Reservation(
                            1,
                            new DateTime('2020-02-10'),
                            DateTime::createFromFormat('H:i:s', '13:30:00'),
                            DateTime::createFromFormat('H:i:s', '15:00:00'),
                            8
                        ),
                        new Reservation(
                            1,
                            new DateTime('2020-02-10'),
                            DateTime::createFromFormat('H:i:s', '15:30:00'),
                            DateTime::createFromFormat('H:i:s', '16:30:00'),
                            8
                        ),
                        new Reservation(
                            1,
                            new DateTime('2020-02-10'),
                            DateTime::createFromFormat('H:i:s', '16:45:00'),
                            DateTime::createFromFormat('H:i:s', '18:00:00'),
                            8
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
                    )
                ),
                [
                    1,
                    2,
                    30 // undefined
                ],
                [
                    new DateTime('2020-02-07'),
                    new DateTime('2020-02-08'),
                    new DateTime('2020-02-09'),
                    new DateTime('2020-02-10'),
                ]
            ]
        ];
    }

    /**
     * @return Collection
     */
    protected function createUsers(): Collection
    {
        return factory(User::class)->createMany([
            [
                'id' => 1,
                'api_token' => 'THE_MOST_SECURE_TOKEN_EVER',
            ],
            [
                'id' => 2,
                'api_token' => 'THE_MOST_SECURE_TOKEN_EVER 2',
            ]
        ]);
    }

    /**
     * @param  int  $userId
     *
     * @return Collection
     */
    public function createUserCustomers(int $userId = 1): Collection
    {
        return factory(Customer::class)->createMany([
            [
                'id' => 1,
                'user_id' => $userId,
            ],
            [
                'id' => 2,
                'user_id' => $userId,
            ],
        ]);
    }

    /**
     * @param  int  $userId
     *
     * @return Collection
     */
    public function createUserSpaces(int $userId = 1): Collection
    {
        return factory(Space::class)->createMany([
            [
                'user_id' => $userId,
                'min_occupancy' => 1,
                'max_occupancy' => 10,
                'pause_between' => 15,
                'start_time' => '10:00:00',
                'finish_time' => '18:00:00',
                'min_lease_time' => 60,
                'is_active' => true,
                'price_type' => SpacePriceTypeEnum::HOURS_PRICE,
            ],
            [
                'user_id' => $userId,
                'min_occupancy' => 5,
                'max_occupancy' => 20,
                'pause_between' => 15,
                'start_time' => '10:00:00',
                'finish_time' => '18:00:00',
                'min_lease_time' => 120,
                'is_active' => true,
                'price_type' => SpacePriceTypeEnum::HOURS_PRICE,
            ],
            [
                'user_id' => $userId,
                'min_occupancy' => 1,
                'max_occupancy' => 30,
                'pause_between' => 30,
                'start_time' => '10:00:00',
                'finish_time' => '18:00:00',
                'min_lease_time' => 60,
                'is_active' => true,
                'price_type' => SpacePriceTypeEnum::DAY_RATE,
            ],
            [
                'user_id' => $userId,
                'min_occupancy' => 1,
                'max_occupancy' => 40,
                'pause_between' => 40,
                'start_time' => '10:00:00',
                'finish_time' => '18:00:00',
                'min_lease_time' => 60,
                'is_active' => true,
                'price_type' => SpacePriceTypeEnum::MINUTES_PRICE,
            ],
            [
                'user_id' => $userId,
                'min_occupancy' => 1,
                'max_occupancy' => 50,
                'pause_between' => 40,
                'start_time' => '10:00:00',
                'finish_time' => '18:00:00',
                'min_lease_time' => 60,
                'is_active' => false,
                'price_type' => SpacePriceTypeEnum::MINUTES_PRICE,
            ],
        ]);
    }

    /**
     * @param  int  $userId
     *
     * @return Collection
     */
    public function createUserOrders(int $userId = 1): Collection
    {
        return factory(Order::class)->createMany([
            // Only the first is actual
            [
                'user_id' => $userId,
                'status' => OrderStatusTypeEnum::NEW,
                'paid' => true,
            ],
            [
                'user_id' => $userId,
                'status' => OrderStatusTypeEnum::NEW,
                'paid' => false,
            ],
            [
                'user_id' => $userId,
                'status' => OrderStatusTypeEnum::CANCELED,
                'paid' => true,
            ],
            [
                'user_id' => $userId,
                'status' => OrderStatusTypeEnum::IN_PROGRESS,
                'paid' => true,
            ],
            [
                'user_id' => $userId,
                'status' => OrderStatusTypeEnum::DONE,
                'paid' => true,
            ],
        ]);
    }

    /**
     * @param Order $order
     */
    public function setSpacesDataToOrder(Order $order): void
    {
        $order->spaces()->attach([
            [
                'space_id' => 1,
                'date' => '2020-02-07',
                'start_time' => '12:00:00',
                'finish_time' => '14:00:00', // + 15 min
                'occupancy' => 5,
            ],
            [
                'space_id' => 1,
                'date' => '2020-02-07',
                'start_time' => '16:00:00',
                'finish_time' => '18:00:00', // + 15 min, min 1 h
                'occupancy' => 3,
            ],
            [
                'space_id' => 2,
                'date' => '2020-02-07',
                'start_time' => '16:00:00',
                'finish_time' => '18:00:00', // + 15 min, min 2 h
                'occupancy' => 10,
            ],
            [
                'space_id' => 2,
                'date' => '2020-02-09',
                'start_time' => '14:30:00', // -15min
                'finish_time' => '16:00:00', // +15min, min 2 h
                'occupancy' => 20,
            ],
            [
                'space_id' => 3, // by days
                'date' => '2020-02-09',
                'start_time' => '16:00:00',
                'finish_time' => '18:00:00',
                'occupancy' => 8,
            ],
            [
                'space_id' => 1,
                'date' => '2020-02-10',
                'start_time' => '10:10:00',
                'finish_time' => '11:30:00', // + 15 min, min 1 h
                'occupancy' => 7,
            ],
            [
                'space_id' => 1,
                'date' => '2020-02-10',
                'start_time' => '11:50:00', // -15min
                'finish_time' => '12:50:00', // + 15 min, min 1 h
                'occupancy' => 9,
            ],
            [
                'space_id' => 1,
                'date' => '2020-02-10',
                'start_time' => '13:30:00', // -15min
                'finish_time' => '15:00:00', // + 15 min, min 1 h
                'occupancy' => 8,
            ],
            [
                'space_id' => 1,
                'date' => '2020-02-10',
                'start_time' => '15:30:00', // -15min
                'finish_time' => '16:30:00', // + 15 min
                'occupancy' => 8,
            ],
            [
                'space_id' => 1,
                'date' => '2020-02-10',
                'start_time' => '16:45:00', // -15min
                'finish_time' => '18:00:00', // the end of day
                'occupancy' => 8,
            ],
        ]);
    }

    public function reservationsProvider(): array
    {
        return [
            'first_space' => [
                'id' => 1,
                'date' => new DateTime('2020-02-07'),
                'expected' => [
                    [
                        'start_time' => '12:00:00', // -15 min
                        'finish_time' => '14:00:00', // +15 min
                        'type' => 'busy',
                        'people' => 5,
                    ],
                    [
                        'start_time' => '16:00:00', // -15 min
                        'finish_time' => '18:00:00', // the end of time
                        'type' => 'busy',
                        'people' => 3,
                    ],
                ],
            ],
        ];
    }

    public function reservationsByDatesProvider(): array
    {
        return [
            'first_space' => [
                'expected' => [
                    '2020-02-07' => [
                        [
                            'start_time' => '12:00:00',
                            'finish_time' => '14:00:00',
                            'type' => 'busy',
                            'people' => 5,
                        ],
                        [
                            'start_time' => '16:00:00',
                            'finish_time' => '18:00:00',
                            'type' => 'busy',
                            'people' => 3,
                        ],
                    ]
                ],
                'id' => 1,
                'date' => [new DateTime('2020-02-07')],
            ],
            'first_space' => [
                'expected' => [
                    '2020-02-07' => [
                        [
                            'start_time' => '12:00:00',
                            'finish_time' => '14:00:00',
                            'type' => 'busy',
                            'people' => 5,
                        ],
                        [
                            'start_time' => '16:00:00',
                            'finish_time' => '18:00:00',
                            'type' => 'busy',
                            'people' => 3,
                        ],
                    ],
                    '2020-02-10' => [
                        [
                            'start_time' => '10:10:00',
                            'finish_time' => '11:30:00',
                            'type' => 'busy',
                            'people' => 7,
                        ],
                        [
                            'start_time' => '11:50:00',
                            'finish_time' => '12:50:00',
                            'type' => 'busy',
                            'people' => 9,
                        ],
                        [
                            'start_time' => '13:30:00',
                            'finish_time' => '15:00:00',
                            'type' => 'busy',
                            'people' => 8,
                        ],
                        [
                            'start_time' => '15:30:00',
                            'finish_time' => '16:30:00',
                            'type' => 'busy',
                            'people' => 8,
                        ],
                        [
                            'start_time' => '16:45:00',
                            'finish_time' => '18:00:00',
                            'type' => 'busy',
                            'people' => 8,
                        ],
                    ],
                ],
                'id' => 1,
                'date' => [
                    new DateTime('2020-02-07'), new DateTime('2020-02-08'),
                    new DateTime('2020-02-09'), new DateTime('2020-02-10')
                ],
            ],
        ];
    }

    public function scheduleProvider(): array
    {
        return [
            'first_space_2020-02-07' => [
                'id' => 1,
                'date' => new DateTime('2020-02-07'),
                'types' => ['free', 'pause'],
                'expected' => [
                    [
                        'start_time' => '10:00:00',
                        'finish_time' => '11:45:00',
                        'type' => 'free',
                    ],
                    [
                        'start_time' => '14:00:00',
                        'finish_time' => '14:15:00',
                        'type' => 'pause',
                    ],
                    [
                        'start_time' => '14:15:00',
                        'finish_time' => '15:45:00',
                        'type' => 'free',
                    ],
                ],
            ],
            'available_space_2020-02-08' => [
                'id' => 1,
                'date' => new DateTime('2020-02-08'),
                'types' => ['free', 'pause'],
                'expected' => [
                    [
                        'start_time' => '10:00:00',
                        'finish_time' => '18:00:00',
                        'type' => 'free',
                    ],
                ],
            ],
            'available_space_in_the_end_of_day_2020-02-09' => [
                'id' => 2,
                'date' => new DateTime('2020-02-09'),
                'types' => ['free', 'pause', 'lost'],
                'expected' => [
                    [
                        'start_time' => '10:00:00',
                        'finish_time' => '14:15:00',
                        'type' => 'free',
                    ],
                    [
                        'start_time' => '16:00:00',
                        'finish_time' => '16:15:00',
                        'type' => 'pause',
                    ],
                    [
                        'start_time' => '16:15:00',
                        'finish_time' => '18:00:00',
                        'type' => 'lost',
                    ],
                ],
            ],
            'partial_availability_2020-02-10' => [
                'id' => 1,
                'date' => new DateTime('2020-02-10'),
                'types' => null,
                'expected' => [
                    [
                        'start_time' => '10:00:00',
                        'finish_time' => '10:10:00',
                        'type' => 'lost',
                    ],
                    [
                        'start_time' => '10:10:00',
                        'finish_time' => '11:30:00',
                        'type' => 'busy',
                        'occupancy' => 7,
                    ],
                    [
                        'start_time' => '11:30:00',
                        'finish_time' => '11:45:00',
                        'type' => 'pause',
                    ],
                    [
                        'start_time' => '11:45:00',
                        'finish_time' => '11:50:00',
                        'type' => 'lost',
                    ],
                    [
                        'start_time' => '11:50:00',
                        'finish_time' => '12:50:00',
                        'type' => 'busy',
                        'occupancy' => 9,
                    ],
                    [
                        'start_time' => '12:50:00',
                        'finish_time' => '13:05:00',
                        'type' => 'pause',
                    ],
                    [
                        'start_time' => '13:05:00',
                        'finish_time' => '13:30:00',
                        'type' => 'lost',
                    ],
                    [
                        'start_time' => '13:30:00',
                        'finish_time' => '15:00:00',
                        'type' => 'busy',
                        'occupancy' => 8,
                    ],
                    [
                        'start_time' => '15:00:00',
                        'finish_time' => '15:15:00',
                        'type' => 'pause',
                    ],
                    [
                        'start_time' => '15:15:00',
                        'finish_time' => '15:30:00',
                        'type' => 'lost',
                    ],
                    [
                        'start_time' => '15:30:00',
                        'finish_time' => '16:30:00',
                        'type' => 'busy',
                        'occupancy' => 8,
                    ],
                    [
                        'start_time' => '16:30:00',
                        'finish_time' => '16:45:00',
                        'type' => 'pause',
                    ],
                    [
                        'start_time' => '16:45:00',
                        'finish_time' => '18:00:00',
                        'type' => 'busy',
                        'occupancy' => 8,
                    ],
                ],
            ]
            // TODO добавить больше промежутков!
            //
        ];
    }

    public function designProbableScheduleProvider(): array
    {
        return [
            'first_space_2020-02-07' => [
                'id' => 1,
                'date' => new DateTime('2020-02-07'),
                'types' => ['busy', 'lost', 'pause', 'candidate'],
                'expected' => [
                    [
                        'start_time' => '10:00:00',
                        'finish_time' => '11:00:00',
                        'occupancy' => 10,
                        'type' => 'candidate',
                    ],
                    [
                        'start_time' => '11:00:00',
                        'finish_time' => '11:15:00',
                        'type' => 'pause',
                    ],
                    [
                        'start_time' => '11:15:00',
                        'finish_time' => '12:00:00',
                        'type' => 'lost',
                    ],
                    [
                        'start_time' => '12:00:00',
                        'finish_time' => '14:00:00',
                        'occupancy' => 5,
                        'type' => 'busy',
                    ],
                    [
                        'start_time' => '14:00:00',
                        'finish_time' => '14:15:00',
                        'type' => 'pause',
                    ],
                    [
                        'start_time' => '14:15:00',
                        'finish_time' => '15:15:00',
                        'occupancy' => 10,
                        'type' => 'candidate',
                    ],
                    [
                        'start_time' => '15:15:00',
                        'finish_time' => '15:30:00',
                        'type' => 'pause',
                    ],
                    [
                        'start_time' => '15:30:00',
                        'finish_time' => '16:00:00',
                        'type' => 'lost',
                    ],
                    [
                        'start_time' => '16:00:00',
                        'finish_time' => '18:00:00',
                        'occupancy' => 3,
                        'type' => 'busy',
                    ],
                ],
            ],
            'available_space_2020-02-08' => [
                'id' => 1,
                'date' => new DateTime('2020-02-08'),
                'types' => ['pause'],
                'expected' => [
                    [
                        'start_time' => '11:00:00',
                        'finish_time' => '11:15:00',
                        'type' => 'pause',
                    ],
                    [
                        'start_time' => '12:15:00',
                        'finish_time' => '12:30:00',
                        'type' => 'pause',
                    ],
                    [
                        'start_time' => '13:30:00',
                        'finish_time' => '13:45:00',
                        'type' => 'pause',
                    ],
                    [
                        'start_time' => '14:45:00',
                        'finish_time' => '15:00:00',
                        'type' => 'pause',
                    ],
                    [
                        'start_time' => '16:00:00',
                        'finish_time' => '16:15:00',
                        'type' => 'pause',
                    ],
                    [
                        'start_time' => '17:15:00',
                        'finish_time' => '17:30:00',
                        'type' => 'pause',
                    ],
                ],
            ],
            'available_space_in_the_end_of_day_2020-02-09' => [
                'id' => 2,
                'date' => new DateTime('2020-02-09'),
                'types' => ['free', 'pause', 'lost', 'candidate'],
                'expected' => [
                    [
                        'start_time' => '10:00:00',
                        'finish_time' => '12:00:00',
                        'occupancy' => 20,
                        'type' => 'candidate',
                    ],
                    [
                        'start_time' => '12:00:00',
                        'finish_time' => '12:15:00',
                        'type' => 'pause',
                    ],
                    [
                        'start_time' => '12:15:00',
                        'finish_time' => '14:15:00',
                        'occupancy' => 20,
                        'type' => 'candidate',
                    ],
                    [
                        'start_time' => '14:15:00',
                        'finish_time' => '14:30:00',
                        'type' => 'pause',
                    ],
                    [
                        'start_time' => '16:00:00',
                        'finish_time' => '16:15:00',
                        'type' => 'pause',
                    ],
                    [
                        'start_time' => '16:15:00',
                        'finish_time' => '18:00:00',
                        'type' => 'lost',
                    ],
                ],
            ],
            'partial_availability_2020-02-10' => [
                'id' => 1,
                'date' => new DateTime('2020-02-10'),
                'types' => null,
                'expected' => [
                    [
                        'start_time' => '10:00:00',
                        'finish_time' => '10:10:00',
                        'type' => 'lost',
                    ],
                    [
                        'start_time' => '10:10:00',
                        'finish_time' => '11:30:00',
                        'type' => 'busy',
                        'occupancy' => 7,
                    ],
                    [
                        'start_time' => '11:30:00',
                        'finish_time' => '11:45:00',
                        'type' => 'pause',
                    ],
                    [
                        'start_time' => '11:45:00',
                        'finish_time' => '11:50:00',
                        'type' => 'lost',
                    ],
                    [
                        'start_time' => '11:50:00',
                        'finish_time' => '12:50:00',
                        'type' => 'busy',
                        'occupancy' => 9,
                    ],
                    [
                        'start_time' => '12:50:00',
                        'finish_time' => '13:05:00',
                        'type' => 'pause',
                    ],
                    [
                        'start_time' => '13:05:00',
                        'finish_time' => '13:30:00',
                        'type' => 'lost',
                    ],
                    [
                        'start_time' => '13:30:00',
                        'finish_time' => '15:00:00',
                        'type' => 'busy',
                        'occupancy' => 8,
                    ],
                    [
                        'start_time' => '15:00:00',
                        'finish_time' => '15:15:00',
                        'type' => 'pause',
                    ],
                    [
                        'start_time' => '15:15:00',
                        'finish_time' => '15:30:00',
                        'type' => 'lost',
                    ],
                    [
                        'start_time' => '15:30:00',
                        'finish_time' => '16:30:00',
                        'type' => 'busy',
                        'occupancy' => 8,
                    ],
                    [
                        'start_time' => '16:30:00',
                        'finish_time' => '16:45:00',
                        'type' => 'pause',
                    ],
                    [
                        'start_time' => '16:45:00',
                        'finish_time' => '18:00:00',
                        'type' => 'busy',
                        'occupancy' => 8,
                    ],
                ],
            ]
        ];
    }
}
