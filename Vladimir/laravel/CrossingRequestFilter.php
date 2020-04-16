<?php

namespace App\Support\Filter\Crossing\CrossingRequest;

use App\Support\Filter\AbstractFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use App\Enums\Crossing\MovingRequestStatuses;
use Carbon\Carbon;

class CrossingRequestFilter extends AbstractFilter
{

    protected $filterData;

    public function filter(Builder $query, Collection $request)
    {
        $this->filterData = $request;

        if ($parameter = $request->get('number')) {
            $query->where('number', 'like', '%'.$parameter.'%');
        }

        if ($parameter = $request->get('fio')) {
            $query->whereHas('client', function (Builder $query) use ($parameter) {
                $searchItems = explode(' ', $parameter);
                foreach ($searchItems as $item) {
                    $query->where(function (Builder $query) use ($item) {
                        $query->orWhere('name', 'like', sprintf('%%%s%%', $item));
                        $query->orWhere('last_name', 'like', sprintf('%%%s%%', $item));
                        $query->orWhere('middle_name', 'like', sprintf('%%%s%%', $item));
                    });
                }
            });
        }

        if ($parameter = $request->get('status')) {
            switch ($parameter) {
                case 1:
                    $query->whereIn('status', [MovingRequestStatuses::NEW, MovingRequestStatuses::PROCESSING, MovingRequestStatuses::IN_WORK]);
                    break;
                case 2:
                    $query->whereIn('status', [MovingRequestStatuses::DONE, MovingRequestStatuses::CANCELED]);
                    break;
            }
        }

        if ($parameter = $request->get('phone')) {
            $parameter = preg_replace("/[^0-9]/", '', $parameter);
            $query->whereHas('client', function (Builder $query) use ($parameter) {
                $query->WhereHas('phones', function (Builder $query) use ($parameter) {
                    $query->where('value', 'like', sprintf('%%%s%%', $parameter));
                });
            });
        }

        if ($parameter = $request->get('email')) {
            $query->whereHas('client', function (Builder $query) use ($parameter) {
                $query->where('email', 'like', '%'.$parameter.'%');
            });
        }

        if ($parameter = $request->get('created_from')) {
            $query->where('created_at', '>=', new Carbon($parameter));
        }

        if ($parameter = $request->get('created_to')) {
            $query->where('created_at', '<=', (new Carbon($parameter))->setTime(23, 59, 59));
        }

        if ($parameter = $request->get('moving_from')) {
            $query->where('moving_start_at', '>=', new Carbon($parameter));
        }

        if ($parameter = $request->get('moving_to')) {
            $query->where('moving_end_at', '<=', new Carbon($parameter));
        }

        if ($parameter = $request->get('addr_from')) {
            $searchItems = explode(' ', $parameter);
            foreach ($searchItems as $item) {
                $query->where(function (Builder $query) use ($item) {
                    $query->orWhere('address_from_str', 'like', sprintf('%%%s%%', $item));
                });
            }
        }

        if ($parameter = $request->get('addr_to')) {
            $searchItems = explode(' ', $parameter);
            foreach ($searchItems as $item) {
                $query->where(function (Builder $query) use ($item) {
                    $query->orWhere('address_from_str', 'like', sprintf('%%%s%%', $item));
                });
            }
        }


        $order = $request->get('order','desc');
        $query->orderBy('id', $order);

        return $query;
    }

    /**
     * Get the evaluated contents of the object.
     *
     * @return string
     */
    public function render()
    {

        return view('profile.crossing.request.list.filter.crossingRequestDefault', [
            'filterData' => $this->filterData,
            'action' => $this->action,
            'method' => $this->method,

        ]);
    }
}
