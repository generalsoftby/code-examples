<?php

namespace Tests\Feature\ResourceCalculators\Space;

use DateTime;
use App\Services\ResourceCalculators\Space\RequirementCollection;
use App\Services\ResourceCalculators\Space\Requirement;
use Tests\TestCase;

class RequirementCollectionTest extends TestCase
{
    protected RequirementCollection $collection;

    /**
     * @param Requirement $expected
     * @param int         $id
     * @param DateTime    $date
     * @param DateTime    $startTime
     *
     * @dataProvider requirementProvider
     */
    public function testCreate(
        Requirement $expected,
        int $id,
        DateTime $date,
        DateTime $startTime,
        DateTime $finishTime,
        int $people
    ): void {
        $collection = new RequirementCollection();

        $this->assertEquals($expected, $collection->create($id, $date, $startTime, $finishTime, $people));
    }

    public function requirementProvider(): array
    {
        return [
            [
                'expected' => Requirement::createFromStrings(1, '2020-02-20', '12:00:00', '13:00:00', 5),
                'id' => 1,
                'date' => new DateTime('2020-02-20'),
                'start_time' => DateTime::createFromFormat('H:i:s', '12:00:00'),
                'finish_time' => DateTime::createFromFormat('H:i:s', '13:00:00'),
                'people' => 5,
            ],
            [
                'expected' => Requirement::createFromStrings(2,'2020-02-21', '12:00:00', '13:00:00', 10),
                'id' => 2,
                'date' => new DateTime('2020-02-21'),
                'start_time' => DateTime::createFromFormat('H:i:s', '12:00:00'),
                'finish_time' => DateTime::createFromFormat('H:i:s', '13:00:00'),
                'people' => 10,
            ],
        ];
    }

    /**
     * @param array $requirements
     *
     * @dataProvider requirementsProvider
     */
    public function testCount(array $requirements): void
    {
        $collection = new RequirementCollection(...$requirements);

        $this->assertCount(count($requirements), $collection);
    }

    /**
     * @param array $requirements
     *
     * @dataProvider requirementsProvider
     */
    public function testForeach(array $requirements): void
    {
        $collection = new RequirementCollection(...$requirements);

        foreach ($collection as $key => $requirement) {
            $this->assertEquals($requirements[$key], $requirement);
        }
    }

    public function requirementsProvider(): array
    {
        return [
            [
                [
                    Requirement::createFromStrings(1, '2020-02-20', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(2, '2020-02-19', '12:00:00', '14:00:00'),
                    Requirement::createFromStrings(3, '2020-02-21', '12:00:00', '15:00:00'),
                    Requirement::createFromStrings(4, '2020-02-16', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(5, '2020-02-17', '12:00:00', '18:00:00'),
                    Requirement::createFromStrings(1, '2020-02-25', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(1, '2020-02-21', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(2, '2020-02-19', '12:00:00', '14:00:00'),
                    Requirement::createFromStrings(3, '2020-02-20', '12:00:00', '15:00:00'),
                ]
            ]
        ];
    }

    /**
     * @param array       $expected
     * @param array       $requirements
     * @param Requirement $requirement
     *
     * @dataProvider addProvider
     */
    public function testAdd(array $expected, array $requirements, Requirement $requirement): void
    {
        $collection = new RequirementCollection(...$requirements);

        $collection->add($requirement);

        $this->assertEquals($expected, $collection->getRequirements());
    }

    public function addProvider(): array
    {
        return [
            [
                [
                    Requirement::createFromStrings(1, '2020-02-20', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(2, '2020-02-19', '12:00:00', '14:00:00'),
                    Requirement::createFromStrings(3, '2020-02-21', '12:00:00', '15:00:00'),
                    Requirement::createFromStrings(4, '2020-02-16', '12:00:00', '13:00:00'),
                ],
                [
                    Requirement::createFromStrings(1, '2020-02-20', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(2, '2020-02-19', '12:00:00', '14:00:00'),
                    Requirement::createFromStrings(3, '2020-02-21', '12:00:00', '15:00:00'),
                ],
                Requirement::createFromStrings(4, '2020-02-16', '12:00:00', '13:00:00'),
            ],
            [
                [
                    Requirement::createFromStrings(4, '2020-02-16', '12:00:00', '13:00:00'),
                ],
                [],
                Requirement::createFromStrings(4, '2020-02-16', '12:00:00', '13:00:00'),
            ],
        ];
    }

    /**
     * @param array    $expected
     * @param int      $id
     * @param array    $requirements
     *
     * @dataProvider filterByIdProvider
     */
    public function testFilterById(array $expected, int $id, array $requirements): void
    {
        $collection = new RequirementCollection(...$requirements);

        $this->assertEquals($expected, $collection->filterById($id)->getRequirements());
    }

    public function filterByIdProvider(): array
    {
        return [
            [
                'expected' => [
                    Requirement::createFromStrings(1, '2020-02-20', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(1, '2020-02-25', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(1, '2020-02-21', '12:00:00', '13:00:00'),
                ],
                'id' => 1,
                'requirements' => [
                    Requirement::createFromStrings(1, '2020-02-20', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(2, '2020-02-19', '12:00:00', '14:00:00'),
                    Requirement::createFromStrings(3, '2020-02-21', '12:00:00', '15:00:00'),
                    Requirement::createFromStrings(4, '2020-02-16', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(5, '2020-02-17', '12:00:00', '18:00:00'),
                    Requirement::createFromStrings(1, '2020-02-25', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(1, '2020-02-21', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(2, '2020-02-19', '12:00:00', '14:00:00'),
                    Requirement::createFromStrings(3, '2020-02-20', '12:00:00', '15:00:00'),
                ],
            ],
            [
                'expected' => [
                    Requirement::createFromStrings(2, '2020-02-19', '12:00:00', '14:00:00'),
                    Requirement::createFromStrings(2, '2020-02-19', '12:00:00', '14:00:00'),
                ],
                'id' => 2,
                'requirements' => [
                    Requirement::createFromStrings(1, '2020-02-20', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(2, '2020-02-19', '12:00:00', '14:00:00'),
                    Requirement::createFromStrings(3, '2020-02-21', '12:00:00', '15:00:00'),
                    Requirement::createFromStrings(4, '2020-02-16', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(5, '2020-02-17', '12:00:00', '18:00:00'),
                    Requirement::createFromStrings(1, '2020-02-25', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(1, '2020-02-21', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(2, '2020-02-19', '12:00:00', '14:00:00'),
                    Requirement::createFromStrings(3, '2020-02-20', '12:00:00', '15:00:00'),
                ],
            ],
            [
                'expected' => [
                ],
                'id' => 6,
                'requirements' => [
                    Requirement::createFromStrings(1, '2020-02-20', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(2, '2020-02-19', '12:00:00', '14:00:00'),
                    Requirement::createFromStrings(3, '2020-02-21', '12:00:00', '15:00:00'),
                    Requirement::createFromStrings(4, '2020-02-16', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(5, '2020-02-17', '12:00:00', '18:00:00'),
                    Requirement::createFromStrings(1, '2020-02-25', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(1, '2020-02-21', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(2, '2020-02-19', '12:00:00', '14:00:00'),
                    Requirement::createFromStrings(3, '2020-02-20', '12:00:00', '15:00:00'),
                ],
            ],
        ];
    }

    /**
     * @param array    $expected
     * @param DateTime $date
     * @param array    $requirements
     *
     * @dataProvider filterByDateProvider
     */
    public function testFilterByDate(array $expected, DateTime $date, array $requirements): void
    {
        $collection = new RequirementCollection(...$requirements);

        $this->assertEquals($expected, $collection->filterByDate($date)->getRequirements());
    }

    public function filterByDateProvider(): array
    {
        return [
            [
                'expected' => [
                    Requirement::createFromStrings(3, '2020-02-21', '12:00:00', '15:00:00'),
                    Requirement::createFromStrings(1, '2020-02-21', '12:00:00', '13:00:00'),
                ],
                'date' => new DateTime('2020-02-21'),
                'requirements' => [
                    Requirement::createFromStrings(1, '2020-02-20', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(2, '2020-02-19', '12:00:00', '14:00:00'),
                    Requirement::createFromStrings(3, '2020-02-21', '12:00:00', '15:00:00'),
                    Requirement::createFromStrings(4, '2020-02-16', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(5, '2020-02-17', '12:00:00', '18:00:00'),
                    Requirement::createFromStrings(1, '2020-02-25', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(1, '2020-02-21', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(2, '2020-02-19', '12:00:00', '14:00:00'),
                    Requirement::createFromStrings(3, '2020-02-20', '12:00:00', '15:00:00'),
                ],
            ],
            [
                'expected' => [
                  Requirement::createFromStrings(4, '2020-02-16', '12:00:00', '13:00:00'),
                ],
                'date' => new DateTime('2020-02-16'),
                'requirements' => [
                    Requirement::createFromStrings(1, '2020-02-20', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(2, '2020-02-19', '12:00:00', '14:00:00'),
                    Requirement::createFromStrings(3, '2020-02-21', '12:00:00', '15:00:00'),
                    Requirement::createFromStrings(4, '2020-02-16', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(5, '2020-02-17', '12:00:00', '18:00:00'),
                    Requirement::createFromStrings(1, '2020-02-25', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(1, '2020-02-21', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(2, '2020-02-19', '12:00:00', '14:00:00'),
                    Requirement::createFromStrings(3, '2020-02-20', '12:00:00', '15:00:00'),
                ],
            ],
            [
                'expected' => [
                ],
                'date' => new DateTime('2020-02-11'),
                'requirements' => [
                    Requirement::createFromStrings(1, '2020-02-20', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(2, '2020-02-19', '12:00:00', '14:00:00'),
                    Requirement::createFromStrings(3, '2020-02-21', '12:00:00', '15:00:00'),
                    Requirement::createFromStrings(4, '2020-02-16', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(5, '2020-02-17', '12:00:00', '18:00:00'),
                    Requirement::createFromStrings(1, '2020-02-25', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(1, '2020-02-21', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(2, '2020-02-19', '12:00:00', '14:00:00'),
                    Requirement::createFromStrings(3, '2020-02-20', '12:00:00', '15:00:00'),
                ],
            ],
        ];
    }

    /**
     * @param array $expected
     * @param array $requirements
     *
     * @dataProvider getIdsProvider
     */
    public function testGetIds(array $expected, array $requirements): void
    {
        $collection = new RequirementCollection(...$requirements);

        $this->assertEquals($expected, $collection->getIds());
    }

    public function getIdsProvider(): array
    {
        return [
            [
                'expected' => [
                    1, 2, 3, 4, 5
                ],
                'requirements' => [
                    Requirement::createFromStrings(1, '2020-02-20', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(2, '2020-02-19', '12:00:00', '14:00:00'),
                    Requirement::createFromStrings(3, '2020-02-21', '12:00:00', '15:00:00'),
                    Requirement::createFromStrings(4, '2020-02-16', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(5, '2020-02-17', '12:00:00', '18:00:00'),
                    Requirement::createFromStrings(1, '2020-02-25', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(1, '2020-02-21', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(2, '2020-02-19', '12:00:00', '14:00:00'),
                    Requirement::createFromStrings(3, '2020-02-20', '12:00:00', '15:00:00'),
                ],
            ],
            [
                'expected' => [
                ],
                'requirements' => [
                ],
            ],
        ];
    }

    /**
     * @param array $expected
     * @param array $requirements
     *
     * @dataProvider getDatesProvider
     */
    public function testGetDates(array $expected, array $requirements): void
    {
        $collection = new RequirementCollection(...$requirements);

        $this->assertEquals($expected, $collection->getDates());
    }

    public function getDatesProvider(): array
    {
        return [
            [
                'expected' => [
                    new DateTime('2020-02-16'),
                    new DateTime('2020-02-17'),
                    new DateTime('2020-02-19'),
                    new DateTime('2020-02-20'),
                    new DateTime('2020-02-21'),
                    new DateTime('2020-02-25'),

                ],
                'requirements' => [
                    Requirement::createFromStrings(1, '2020-02-20', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(2, '2020-02-19', '12:00:00', '14:00:00'),
                    Requirement::createFromStrings(3, '2020-02-21', '12:00:00', '15:00:00'),
                    Requirement::createFromStrings(4, '2020-02-16', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(5, '2020-02-17', '12:00:00', '18:00:00'),
                    Requirement::createFromStrings(1, '2020-02-25', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(1, '2020-02-21', '12:00:00', '13:00:00'),
                    Requirement::createFromStrings(2, '2020-02-19', '12:00:00', '14:00:00'),
                    Requirement::createFromStrings(3, '2020-02-20', '12:00:00', '15:00:00'),
                ],
            ],
            [
                'expected' => [
                ],
                'requirements' => [
                ],
            ],
        ];
    }
}
