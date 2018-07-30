<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models;

class SearchController extends Controller
{

	private $filters = [];

	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{

		$data = array();
		$data['page_title'] = 'Подбор и сравнение автомобилей в аренду в Москве';

		$data['datacatalog'] = $this->_getCatalogPagination();

		$data['auto_brands'] = Models\AutoBrand::getAvtoBrandsActive();
		$data['auto_body_types'] = Models\AutoBodyType::getAvtoBodyTypesActive();
		$data['auto_transmissions'] = Models\AutoTransmission::getAvtoTransmissionsActive();
		$data['auto_classes'] = Models\AutoClass::getAvtoClassesActive();
		$data['metro'] = Models\Metro::getItemsActive();
		$data['popular'] = Models\Question::getPopular();

		if (\Request::ajax()) {
			return [
				'view' => view('site.pages.search.list')->with($data)->render(),
				'url' => $data['datacatalog']['url'],
				'total' => $data['datacatalog']['total'],
				'current_page' => $data['datacatalog']['current_page'],
				'count' => $data['datacatalog']['count'],
			];
		}

		if(isset($this->filters['brand'])) {
			$min_price_day = $data['datacatalog']['catalog_query']->orderBy('price_day', 'asc')->first();

			if($min_price_day) {
				$data['page_title'] = sprintf('Аренда автомобилей %s без водителя в Москве - от %d руб.', $this->filters['brand']->name, $min_price_day->price_day);
			} else {
				$data['page_title'] = sprintf('Аренда автомобилей %s без водителя в Москве.', $this->filters['brand']->name);
			}

			$data['page_header'] = sprintf('Аренда автомобилей %s', $this->filters['brand']->name);
			$data['need_reload'] = true;
		}

		if(isset($this->filters['metro'])) {
			$min_price_day = $data['datacatalog']['catalog_query']->orderBy('price_day', 'asc')->first();

			if($min_price_day) {
				$data['page_title'] = sprintf('Аренда автомобилей возле метро %s - эконом, бизнес, внедорожники от %d руб.', $this->filters['metro']->name, $min_price_day->price_day);
			} else {
				$data['page_title'] = sprintf('Аренда автомобилей возле метро %s - эконом, бизнес, внедорожники', $this->filters['metro']->name);
			}

			$data['page_header'] = sprintf('Аренда автомобилей, метро %s', $this->filters['metro']->name);
			$data['need_reload'] = true;
		}

		if(isset($this->filters['rent'])) {
			$this->setRentTitle($data);
		}

		return view('site.pages.search.index')->with($data);
	}


	private function _getCatalogPagination()
	{
		$data = array();
		$catalog = Models\Catalog::query();

		$per_page = \Request::input('per_page', 20);
		$data['url'] = \Request::server('REQUEST_URI');

		$filter_rent = isset($this->filters['rent']) ?
			[$this->filters['rent']] : \Request::input('filter_rent', []);

		foreach($filter_rent as $rent_filter) {
			switch($rent_filter) {
				case 'no_pledge':
					$catalog->where('security_deposit', 0);
					break;
				case 'pledge':
					$catalog->where('security_deposit', '>', 0);
					break;
				case 'unlimited_mileage':
					$catalog->where('mileage_limit', 0);
					break;
				case 'buyout':
					$catalog->where('buyout', true);
					break;
				case 'delivery':
					$catalog->where('delivery', true);
					break;
			}
		}

		$filter_auto_classes = \Request::input('filter_auto_classes', array());
		foreach ($filter_auto_classes as $key => $item) {
			$mod_del = Models\AutoClass::find($item);
			if ($mod_del) {
				if ($mod_del->is_deleted) {
					unset($filter_auto_classes[$key]);
				}
			} else {
				unset($filter_auto_classes[$key]);
			}
		}
		if (count($filter_auto_classes) > 0) {
			$catalog->whereIn('auto_class_id', $filter_auto_classes);
		}

		$filter_auto_transmissions = \Request::input('filter_auto_transmissions', array());
		foreach ($filter_auto_transmissions as $key => $item) {
			$mod_del = Models\AutoTransmission::find($item);
			if ($mod_del) {
				if ($mod_del->is_deleted) {
					unset($filter_auto_transmissions[$key]);
				}
			} else {
				unset($filter_auto_transmissions[$key]);
			}
		}
		if (count($filter_auto_transmissions) > 0) {
			$catalog->whereIn('auto_transmission_id', $filter_auto_transmissions);
		}

		$filter_auto_body_types = \Request::input('filter_auto_body_types', array());
		foreach ($filter_auto_body_types as $key => $item) {
			$mod_del = Models\AutoBodyType::find($item);
			if ($mod_del) {
				if ($mod_del->is_deleted) {
					unset($filter_auto_body_types[$key]);
				}
			} else {
				unset($filter_auto_body_types[$key]);
			}
		}
		if (count($filter_auto_body_types) > 0) {
			$catalog->whereIn('auto_body_type_id', $filter_auto_body_types);
		}

		$filter_auto_brands = isset($this->filters['brand']) ?
			[$this->filters['brand']->id] : \Request::input('filter_auto_brands', array());

		foreach ($filter_auto_brands as $key => $item) {
			$mod_del = Models\AutoBrand::find($item);
			if ($mod_del) {
				if ($mod_del->is_deleted) {
					unset($filter_auto_brands[$key]);
				}
			} else {
				unset($filter_auto_brands[$key]);
			}
		}
		if (count($filter_auto_brands) > 0) {
			$catalog->whereIn('auto_brand_id', $filter_auto_brands);
		}

		$filter_price = \Request::input('filter_price', 0);
		if ($filter_price) {
			$catalog->where('price_day', '<=', $filter_price);
		}

		$is_order_address = 0;
		$filter_address = \Request::input('filter_address', "");


		$filter_metro = isset($this->filters['metro']) ?
			$this->filters['metro']->id : \Request::input('filter_metro', 0);

		if (trim($filter_address) != "") {
			$params = array(
				'geocode' => $filter_address, // адрес
				'format' => 'json',                          // формат ответа
				'results' => 1,                               // количество выводимых результатов
				// 'key'     => '...',                           // ваш api key
			);
			$response = json_decode(file_get_contents('http://geocode-maps.yandex.ru/1.x/?' . http_build_query($params, '', '&')));

			if ($response->response->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found > 0) {
				$coords = $response->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos;
				$array = explode(' ', $coords);
				if ((isset($array[0])) and (isset($array[1]))) {
					$coord_x = $array[1];
					$coord_y = $array[0];
					$catalog->join('car_rentals', 'car_rentals.id', '=', 'catalog.car_rental_id');
					$catalog->select(\DB::raw('catalog.*, POW(car_rentals.coord_x-' . $coord_x . ', 2) + POW(car_rentals.coord_y-' . $coord_y . ', 2)  as s'.
					    	', 111.2*SQRT( POW(car_rentals.coord_x-'.$coord_x.',2) + POW( (car_rentals.coord_y-'.$coord_y.')*COS(PI()*car_rentals.coord_x/180)  ,2) ) as skm'
						));
					$is_order_address = 1;
				}
			}
		} else {
			if ($filter_metro > 0) {
				$metro = Models\Metro::find($filter_metro);
				if ($metro) {
					$data['metro'] = $metro;
					$coord_x = $metro->coord_x;
					$coord_y = $metro->coord_y;
					if (($coord_x > 0) and ($coord_y > 0)) {
						$catalog->join('car_rentals', 'car_rentals.id', '=', 'catalog.car_rental_id');
						$catalog->select(\DB::raw('catalog.*, POW(car_rentals.coord_x-' . $coord_x . ', 2) + POW(car_rentals.coord_y-' . $coord_y . ', 2)  as s'.
							', 111.2*SQRT( POW(car_rentals.coord_x-'.$coord_x.',2) + POW( (car_rentals.coord_y-'.$coord_y.')*COS(PI()*car_rentals.coord_x/180)  ,2) ) as skm'
							));
						$is_order_address = 1;
					}
				}
			}
		}

		//function echoDistance($s1,$d1,$s2,$d2){
		//    return 111.2 *sqrt( pow(($s1-$s2),2) + pow( ($d1-$d2)*cos(M_PI*$s1/180),2 )  );
		//};
		//echo round(echoDistance(55.771033,37.64309,55.78245,37.669182),2)."km";


		$appends = array();
		$appends['per_page'] = $per_page;
		$appends['filter_auto_classes'] = $filter_auto_classes;
		$appends['filter_auto_transmissions'] = $filter_auto_transmissions;
		$appends['filter_auto_body_types'] = $filter_auto_body_types;
		$appends['filter_auto_brands'] = $filter_auto_brands;
		$appends['filter_price'] = $filter_price;
		$appends['filter_address'] = $filter_address;
		$appends['filter_metro'] = $filter_metro;
		$appends['filter_rent'] = $filter_rent;

		if ($is_order_address == 1) {
			$catalog->orderBy('skm', 'asc');
		} else {
			$catalog->orderBy('catalog.created_at', 'asc');
		}
		$catalog->where('catalog.is_active', true);
		$catalog->where('catalog.is_deleted', false);
		$data['catalog_query'] = clone $catalog;
		$data['catalog'] = $catalog->paginate($per_page)->appends($appends)->setPath(route('company.catalog'));
		$car_rentals = array();
		foreach ($data['catalog'] as $c) {
			$car_rentals[$c->car_rental->id] = $c->car_rental;
			//echo $c->car_rental->address;
		}
		$data['car_rentals'] = $car_rentals;
		//dd($data['catalog']);
		$data['filter_auto_classes'] = $filter_auto_classes;
		$data['filter_auto_transmissions'] = $filter_auto_transmissions;
		$data['filter_auto_body_types'] = $filter_auto_body_types;
		$data['filter_auto_brands'] = $filter_auto_brands;
		$data['filter_price'] = $filter_price;
		$data['filter_address'] = $filter_address;
		$data['filter_metro'] = $filter_metro;
		$data['filter_rent'] = $filter_rent;
		//dd($data);

		$data['count'] = $data['catalog']->count();
		$data['total'] = $data['catalog']->total();
		$data['current_page'] = $data['catalog']->currentPage();

		return $data;

		/*
		if (!\Auth::check()){
			 return response('Unauthorized.', 401);
		 }
		$data = array();
		$users = Models\User::query();

		$per_page = \Request::input('per_page' , 5 );
		$data['url'] = \Request::server('REQUEST_URI');


		$filter_surname = \Request::input('filter_surname', '');
		if($filter_surname != ''){
			$users->SearchSurname($filter_surname);
		}
		$filter_name = \Request::input('filter_name', '');
		if($filter_name != ''){
			$users->SearchName($filter_name);
		}
		$filter_middlename = \Request::input('filter_middlename', '');
		if($filter_middlename != ''){
			$users->SearchMiddlename($filter_middlename);
		}
		$filter_email = \Request::input('filter_email', '');
		if($filter_email != ''){
			$users->SearchEmail($filter_email);
		}
		$filter_group = \Request::input('filter_group', array());
		if(count($filter_group) > 0){
			$users->SearchGroup($filter_group);
		}

		$filter_sort = \Request::input('filter_sort', '');
		$filter_sort_order = 'asc';
		if( $filter_sort != ''){
			$filter_sort_order = \Request::input('filter_sort_order', 'asc');
			if( $filter_sort != 'group'){
				$users->orderBy($filter_sort, $filter_sort_order);
			}
			else{
				$users->join('user_groups', 'users.group_id', '=', 'user_groups.id');
				$users->orderBy('user_groups.name', $filter_sort_order);
			}
		}
		else{
			$users->orderBy('id', 'decs');
		}

		$appends = array();
		$appends['per_page'] = $per_page;
		$appends['filter_surname'] = $filter_surname;
		$appends['filter_name'] = $filter_name;
		$appends['filter_middlename'] = $filter_middlename;
		$appends['filter_email'] = $filter_email;
		$appends['filter_group'] = $filter_group;
		$appends['filter_sort'] = $filter_sort;
		   $appends['filter_sort_order'] = $filter_sort_order;



		$data['users'] = $users->paginate($per_page)->appends($appends)->setPath(route('users'));
		$data['filter_surname'] = $filter_surname;
		$data['filter_name'] = $filter_name;
		$data['filter_middlename'] = $filter_middlename;
		$data['filter_email'] = $filter_email;
		$data['filter_group'] = $filter_group;
		$data['filter_sort'] = $filter_sort;
		$data['filter_sort_order'] = $filter_sort_order;

		return $data;
		*/
	}


	public function mixed($mixed_slug)
	{
		if($brand = Models\AutoBrand::active()->where('is_deleted', false)->where('url_slug', $mixed_slug)->first()) {
			$this->filters['brand'] = $brand;
			return $this->index();
		}

		if($metro = Models\Metro::active()->where('is_deleted', false)->where('url_slug', $mixed_slug)->first()) {
			$this->filters['metro'] = $metro;
			return $this->index();
		}

		$rent_filters = [
			'bez-zaloga'				=> 'no_pledge',
			'bez-ogranichenija-probega'	=> 'unlimited_mileage',
			's-pravom-vykupa'			=> 'buyout',
			's-dostavkoj'				=> 'delivery',
			's-oplatoj'					=> 'pledge',
		];

		if(isset($rent_filters[$mixed_slug])) {
			$this->filters['rent'] = $rent_filters[$mixed_slug];
			return $this->index();
		}

		return redirect()->route('main');
	}

	protected function setRentTitle(&$data)
	{
		$titles = [
			'no_pledge' => [
				'title'	=> 'Аренда автомобилей в Москве без водителя - без залога',
				'h1'	=> 'Аренда автомобилей без водителя'
			],
			'unlimited_mileage' => [
				'title'	=> 'Аренда автомобиля в Москве без ограничения пробега',
				'h1'	=> 'Аренда автомобиля без ограничения пробега',
			],
			'buyout' => [
				'title'	=> 'Аренда автомобилей с правом выкупа в Москве',
				'h1'	=> 'Аренда автомобилей с правом выкупа',
			],
			'delivery' => [
				'title'	=> 'Аренда автомобиля без водителя с доставкой к дому, работе, в аэропорт, вокзалы',
				'h1'	=> 'Аренда автомобиля с доставкой',
			],
			'pledge' => [
				'title'	=> 'Аренда автомобилей в Москве с оплатой кредитной картой',
				'h1'	=> 'Аренда автомобилей с оплатой по карте',
			],
		];

		if(isset($titles[$this->filters['rent']])) {
			$data['page_title'] = $titles[$this->filters['rent']]['title'];
			$data['page_header'] = $titles[$this->filters['rent']]['h1'];
		}

	}

}
