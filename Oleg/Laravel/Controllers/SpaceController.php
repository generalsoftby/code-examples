<?php

namespace App\Http\Controllers\FreeSlots;

use App\Services\ResourceCalculators\Space\Requirement;
use App\Services\ResourceCalculators\Space\RequirementCollection;
use App\Services\ResourceCalculators\Space\SpaceCalculator;

class SpaceController extends ResourceCalculatorController
{
    /**
     * A data source of possible resources in the request.
     * Storages a key of the multiple array of resources with chosen data.
     *
     * @var string
     */
    const DATA_SOURCE_OF_RESOURCES = 'spaces';

    /**
     * Initializes the SpaceController.
     *
     * @param SpaceCalculator $calculator
     */
    public function __construct(SpaceCalculator $calculator)
    {
        parent::__construct($calculator);
    }

    /**
     * Returns only correct data. The correct data is data with all columns filled in.
     *
     * @param  int   $id
     * @param  array $data
     * @return array
     */
    protected function filterRequirements(int $id, array $data): array
    {
        return array_filter($data, function ($row) use ($id) {
              return isset($row['space_id'], $row['date'], $row['start_time'], $row['finish_time'], $row['occupancy'])
                  && (int) $row['space_id'] === $id
              ;
        });
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
    protected function createRequirement(array $data): Requirement
    {
        return Requirement::createFromStrings(
            $data['space_id'],
            $data['date'],
            $data['start_time'],
            $data['finish_time'],
            $data['occupancy']
        );
    }
}
