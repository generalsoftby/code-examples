<?php

namespace App\Http\Controllers\Settings;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models;
use Carbon\Carbon;
use App\Repositories\RegionRepository;
use App\Classes\MenuItemsEnum;
use App\Classes\SubMenuItemsEnum;
use App\Http\Requests\Settings\RegionRequest

class RegionController extends Controller
{

    private $_region_repository;

    public function __construct()
	{
        parent::__construct();

        $this->_region_repository = app(RegionRepository::className());

        view()->share([
            'menu'     => MenuItemsEnum::SETTINGS,
            'sub_menu' => SubMenuItemsEnum::SETTINGS_DELIVERY_REGION,
        ]);

    }

    public function anyIndex()
	{
        $data = $this->_region_repository->getRegionsList(\Request::all()); 

        if (\Request::ajax()) {
            return [
                'status'        => 'OK',
                'view'          => view('pages.settings.delivery.regions_ajax')->with($data)->render(),
            ];
        }

        return view('pages.settings.delivery.regions')->with($data);
    }

    public function postAdd(RegionRequest $request)
	{
        $region = $this->_region_repository->addRegion(\Request::all()); 
            
        
        $regions[] = $region;

        return [
            'status'   => "OK",
            'view'     => view('pages.settings.delivery.regions_rows_ajax')->with(['regions' => $regions])->render(),
        ];
    }

}
