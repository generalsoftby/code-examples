<?php

namespace App\Repositories;

use App\Models;
use App\Classes\DeliveryMethodEnum;
use App\Classes\ShopsEnum;

class RegionRepository extends AbstractRepository
{

    const PER_PAGE = 50;

    public function __construct()
	{
        parent::__construct(new Models\Region());
    }

    public function getCities($cacheTime = 60)
	{
        $query = Models\Region::whereNull('district_id')->orderBy('name', 'asc');

        if (!$cacheTime || $cacheTime < 0) {
            return $query->get();
        }

        return \Cache::remember(__CLASS__ . ':' . __FUNCTION__, $cacheTime, function() use ($query) {
            return $query->get();
        });
    }

    public function getUnits($cacheTime = 60)
	{
        $query = Models\Region::whereNotNull('district_id')->orderBy('name', 'asc');

        if (!$cacheTime || $cacheTime < 0) {
            return $query->get();
        }

        return \Cache::remember(__CLASS__ . ':' . __FUNCTION__, $cacheTime, function() use ($query) {
            return $query->get();
        });
    }

    public function getDisticts($cacheTime = 60)
	{
        $query = \DB::table('region_districts');

        if (!$cacheTime || $cacheTime < 0) {
            return $query->get();
        }

        return \Cache::remember(__CLASS__ . ':' . __FUNCTION__, $cacheTime, function() use ($query) {
            return $query->get();
        });
    }

    public function getDeliveries($region, $site = ShopsEnum::FOXFISHING)
	{
        return $delivery_price = \DB::table('delivery_prices')
            ->select("*")
            ->leftJoin('delivery_weight', 'delivery_weight.id', '=', 'delivery_prices.ves_id')
            ->join('delivery_methods', 'delivery_methods.id', '=', 'delivery_weight.method_id')
            ->leftJoin('delivery_time', function($join){
                $join
                    ->on('delivery_time.region_id', '=', 'delivery_prices.region_id')
                    ->on('delivery_methods.id', '=', 'delivery_time.company_id');
            })
            ->leftJoin('delivery_region_tariff', function($join) use ($region){
                $join
                    ->on('delivery_region_tariff.delivery_method_id', '=', 'delivery_methods.id')
                    ->where('delivery_region_tariff.zone', '=', $region->zone);
            })
            ->where('delivery_prices.region_id', '=', $region->id)
            ->where('delivery_methods.active', '=', TRUE)
            ->orderBy('delivery_methods.name')
            ->orderBy('delivery_methods.id')
            ->get();
    }

    public function getRegionsList($request_data)
	{
        $per_page = \Request::input('per_page', 50);
        $data = [];

        $delivery = Models\DeliveryMethod::get();
        $data['deliveries'] = $delivery;

        $append = ['per_page' => $per_page];
        $data['per_page'] = $per_page;

        $regions = Models\Region::query();


        $data['regions'] = $regions->orderBy('name', 'asc')->paginate($per_page)->appends($append);
        
    }

}
