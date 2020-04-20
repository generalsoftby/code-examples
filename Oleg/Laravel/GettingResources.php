<?php

namespace App\Services\ResourceCalculators\Base;

use DateTime;
use App\Services\DT;
use App\Enums\OrderStatusTypeEnum;
use Illuminate\Database\Eloquent\Builder;

/**
 * The trait makes queries to get resources.
 */
trait GettingResources
{
    /**
     * Makes a query to get resources by ids and dates.
     *
     * @param Builder            $query
     * @param string             $idColumn
     * @param array|int[]        $ids
     * @param array|DateTime[]   $dates
     */
    protected function makeQueryOfResourcesByIdsAndDates(Builder $query, string $idColumn, array $ids, array $dates): void
    {
        $query
            ->whereIn($idColumn, $ids)
            ->whereIn('date', DT::toString('Y-m-d', ...$dates))
        ;
    }

    /**
     * Makes a query to get resources by a date range.
     *
     * @param  Builder  $query
     * @param  string   $idColumn
     * @param  int      $id
     * @param  DateTime $startDate
     * @param  DateTime $finishDate
     */
    protected function makeQueryOfResourcesByDateRange(Builder $query, string $idColumn, int $id, DateTime $startDate, DateTime $finishDate): void
    {
        $query
            ->where($idColumn, $id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $finishDate->format('Y-m-d')])
        ;
    }

    /**
     * Makes a query to get active orders.
     *
     * @param  Builder $query
     */
    protected function makeQueryOfActiveOrders(Builder $query): void
    {
        $query
            ->whereHas('order', function (Builder $query) {
                $query->where([
                    'status' => OrderStatusTypeEnum::NEW,
                    'paid' => true,
                ]);
            })
        ;
    }

    /**
     * Makes a query to sort resources.
     * @param Builder $query
     * @param string  $idColumn
     */
    protected function makeQueryOfSortingResources(Builder $query, string $idColumn): void
    {
        $query
            ->orderBy($idColumn, 'asc')
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
        ;
    }
}
