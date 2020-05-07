<?php

namespace App\Http\Controllers\FreeSlots;

use DateTime;
use App\Services\DT;
use App\Services\ResourceCalculators\Base\Requirement;
use App\Services\ResourceCalculators\Base\RequirementCollection;
use App\Services\ResourceCalculators\Base\ResourceCalculator;
use App\Services\ResourceCalculators\Base\TimeSlotCollection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

abstract class ResourceCalculatorController extends Controller
{
    /**
     * A data source of possible resources in the request.
     * Storages a key of the multiple array of resources with chosen data.
     *
     * @var string
     */
    const DATA_SOURCE_OF_RESOURCES = 'list';

    protected ResourceCalculator $calculator;

    /**
     * @param ResourceCalculator $calculator
     */
    public function __construct(ResourceCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Returns free slots of a given space by a given date range.
     *
     * @param  Request      $request
     * @param  int          $id
     * @param  DateTime     $startDate
     * @param  DateTime     $finishDate
     * @return JsonResponse
     */
    public function getByDateRange(
        Request $request,
        int $id,
        DateTime $startDate,
        DateTime $finishDate
    ): JsonResponse {
        /** @var RequirementCollection $requirementCollection **/
        $requirementCollection = $this->createRequirementCollectionFromData($id, $request->input(static::DATA_SOURCE_OF_RESOURCES));

        /** @var ScheduleCalculator $scheduleCalculator **/
        $scheduleCalculator = $this->calculator->getScheduleCalculatorByDateRange($id, $startDate, $finishDate, $requirementCollection);

        /** @var array|TimeSlotCollection[] $collectionOfFreeSlots **/
        $collectionOfFreeSlots = $scheduleCalculator->getFreeSlotsByDateRange($id, $startDate, $finishDate);

        /** @var array|TimeSlotCollection[] $collectionOfCandidates **/
        $collectionOfCandidates = $scheduleCalculator->getTimeSlotsOfCandidatesByDateRange($id, $startDate, $finishDate);

        return response()
            ->json([
                'slots' => $this->convertArrayOfTimeSlotCollectionToArray($collectionOfFreeSlots),
                'balances' => $this->countBalances($collectionOfCandidates)
            ])
        ;
    }

    /**
     * Returns free slots of a given space by a given date and an number of days.
     *
     * @param  Request      $request
     * @param  int          $id
     * @param  DateTime     $startDate
     * @param  int          $days
     * @return JsonResponse
     */
    public function getByNumberOfDays(Request $request, int $id, DateTime $startDate, int $days): JsonResponse
    {
        /** @var DateTime $finishDate **/
        $finishDate = DT::add($startDate, $days - 1, 'days');

        return $this->getByDateRange(
            $request,
            $id,
            $startDate,
            $finishDate
        );
    }

    /**
     * Returns possible reservations by given data of the request.
     *
     * @param  array|null  $data
     * @return RequirementCollection
     */
    protected function createRequirementCollectionFromData(int $id, ?array $data): RequirementCollection
    {
        if (! $data) {
            return $this->createRequirementCollection();
        }
        /** @var array $data **/
        $data = $this->filterRequirements($id, $data);

        /** @var RequirementCollection $collection **/
        $collection = $this->createRequirementCollection();

        foreach ($data as $item) {
            /** @var Requirement $requirement **/
            $requirement = $this->createRequirement($item);

            $collection->add($requirement);
        }

        return $collection;
    }

    /**
     * Returns only correct data. The correct data is data with all columns filled in.
     *
     * @param  int   $id
     * @param  array $data
     * @return array
     */
    abstract protected function filterRequirements(int $id, array $data): array;

    /**
     * Convers an array of TimeSlotCollections to the array.
     *
     * @param  array $timeSlotCollections
     * @return array
     */
    protected function convertArrayOfTimeSlotCollectionToArray(array $timeSlotCollections): array
    {
        return array_map(
            fn(TimeSlotCollection $collection) => $collection->toArray(),
            $timeSlotCollections
        );
    }

    /**
     * Counts amounts of people of an array of TimeSlotCollections.
     *
     * @param  array $timeSlotCollections
     * @return array
     */
    protected function countBalances(array $timeSlotCollections): array
    {
        $balances = [];

        foreach ($timeSlotCollections as $date => $timeSlotCollection) {
            $balances[$date] = $timeSlotCollection->countNumberOfResources();
        }

        return $balances;
    }

    /**
     * Creates and returns an instance of RequirementCollection.
     *
     * @return RequirementCollection
     */
    protected function createRequirementCollection(): RequirementCollection
    {
        return new RequirementCollection();
    }

    /**
     * Creates and returns an instance of Requirement.
     *
     * @param  array       $data
     * @return Requirement
     */
    abstract protected function createRequirement(array $data): Requirement;
}
