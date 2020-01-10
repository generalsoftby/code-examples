<?php

namespace App\Elastic\SearchRules;

use Illuminate\Support\Arr;
use ScoutElastic\SearchRule;

class AdsSearchRule extends SearchRule
{
    /**
     * @inheritdoc
     */
    public function buildHighlightPayload()
    {
        //
    }

    /**
     * @inheritdoc
     */
    public function buildQueryPayload()
    {
        $filters = (array) $this->builder->query;

        $queryPayload = [
            "must" => [],
            "must_mot" => [],
        ];

        /**
         * Id
         */
        if($adsId = Arr::get($filters, 'id')) {
            $queryPayload["must"][] =
                [
                    ["term" => ['id' => $adsId]],
                ];
        }

        /**
         * Status
         */
        if($adsStatus = Arr::get($filters, 'status')) {
            $queryPayload["must"][] =
                [
                    ["term" => ['status' => $adsStatus]],
                ];
        }

        /**
         * Use Purposes
         */
        if($usePurposes = Arr::get($filters, 'use_purposes')) {
            $queryPayload["must"][] =
                [
                    ["terms" => ['use_purpose' => $usePurposes]],
                ];
        }

        /**
         * Property Type
         */
        if($propertyType = Arr::get($filters, 'property_type')) {
            $queryPayload["must"][] =
            [
                ["term" => ['property_type' => $propertyType]],
            ];
        }

        /**
         * Property Subtype
         */
        if($propertySubtype = Arr::get($filters, 'property_subtype')) {
            $queryPayload["must"][] =
            [
                ["term" => ['property_subtype' => $propertySubtype]],
            ];
        }

        /**
         * Cost
         */
        if($costMin = Arr::get($filters, 'cost_min')) {
            $queryPayload["must"][] =
            [
                'range' => [
                    'cost' => [
                        ["gte" => $costMin],
                    ]
                ]
            ];
        }

        if($costMax = Arr::get($filters, 'cost_max')) {
            $queryPayload["must"][] =
            [
                'range' => [
                    'cost' => [
                        ["lte" => $costMax],
                    ]
                ]
            ];
        }

        /**
         * Total Area
         */
        if($totalAreaMin = Arr::get($filters, 'total_area_min')) {
            $queryPayload["must"][] =
            [
                'range' => [
                    'total_area' => [
                        ["gte" => $totalAreaMin],
                    ]
                ]
            ];
        }

        if($totalAreaMax = Arr::get($filters, 'total_area_max')) {
            $queryPayload["must"][] =
            [
                'range' => [
                    'total_area' => [
                        ["lte" => $totalAreaMax],
                    ]
                ]
            ];
        }

        /**
         * Rooms Count
         */

        if($roomsCount = Arr::get($filters, 'rooms_count')) {
            $queryPayload["must"][] =
                [
                    ["term" => ['rooms_count' => $roomsCount]],
                ];
        }

        if($roomsCountMin = Arr::get($filters, 'rooms_count_min')) {
            $queryPayload["must"][] =
            [
                'range' => [
                    'rooms_count' => [
                        ["gte" => $roomsCountMin],
                    ]
                ]
            ];
        }

        if($roomsCountMax = Arr::get($filters, 'rooms_count_max')) {
            $queryPayload["must"][] =
            [
                'range' => [
                    'rooms_count' => [
                        ["lte" => $roomsCountMax],
                    ]
                ]
            ];
        }

        /**
         * Current Tariff
         */
        if($adsTariff = Arr::get($filters, 'tariff')) {
            $queryPayload["must"][] =
                [
                    ["term" => ['adstariff' => $adsTariff]],
                ];
        }

        /**
         * New Building
         */
        if($newBuilding = Arr::get($filters, 'newbuilding')) {
            $queryPayload["must"][] =
                [
                    ["term" => ['newbuilding' => $newBuilding]],
                ];
        }

        /**
         * New Property
         */
        if($newProperty = Arr::get($filters, 'newproperty')) {
            $queryPayload["must"][] =
                [
                    ["term" => ['newproperty' => $newProperty]],
                ];
        }

        /**
         * Location
         */
        if($location = Arr::get($filters, 'location')) {
            $queryPayload["must"][] =
                [
                    'bool' => [
                        'should' => [
                            ["term" => ['region' => $location]],
                            ["term" => ['locality' => $location]],
                            ["term" => ['street' => $location]]
                        ]
                    ]
                ];
        }

        /**
         * Removal
         */
        if(($removalMin = Arr::get($filters, 'removal_min')) && ($removalGeoPoint = Arr::get($filters, 'removal_geo_point'))) {
            $queryPayload["must_not"][] =
                [
                    'geo_distance' => [
                        'distance' => $removalMin . 'km',
                        'location' => [
                            'lat' => $removalGeoPoint['latitude'],
                            'lon' => $removalGeoPoint['longitude'],
                        ]
                    ]
                ];
        }

        if(($removalMax = Arr::get($filters, 'removal_max')) && ($removalGeoPoint = Arr::get($filters, 'removal_geo_point'))) {
            $queryPayload["must"][] =
            [
                'geo_distance' => [
                    'distance' => $removalMax . 'km',
                    'location' => [
                        'lat' => $removalGeoPoint['latitude'],
                        'lon' => $removalGeoPoint['longitude'],
                    ]
                ]
            ];
        }


        return $queryPayload;
    }
}