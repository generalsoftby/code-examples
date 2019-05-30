<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;

class Controller extends BaseController
{
	use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;
	

	protected $_collection_options = [
		'per_page'	=> 20,
		'order'		=> '+id',
		'filters'	=> [],
	];


	protected function _format_collection($query, $do_search = true)
	{
		$order_field = \Request::get('order')?: substr($this->_collection_options['order'], 1);
		$order_direct = \Request::get('direct')?: ($this->_collection_options['order']{0} == '+' ? 'asc' : 'desc');

		$query->orderBy($order_field, $order_direct);

		$filters = [];
		$filters_config = array_get($this->_collection_options, 'filters', []);

        foreach($filters_config as $field_name => $selection_type)
        {
            $filter_value_index = 'search_' . $field_name;
            $filter_value = \Request::get($filter_value_index);
            $filters[$filter_value_index] = $filter_value;
        }

		foreach($filters_config as $field_name => $selection_type)
		{
			$filter_value = $filters['search_' . $field_name];

			if($filter_value == '')
			{
				continue;
			}

			if(is_object($selection_type) && ($selection_type instanceof \Closure))
			{
				$selection_type($query, $filter_value, $filters);
				continue;
			}

			switch($selection_type)
            {
				case 'LIKE:DS':
					$query->where($field_name, 'LIKE', '%'.$filter_value.'%');
					break;
				case 'LIKE:RS':
					$query->where($field_name, 'LIKE', $filter_value.'%');
					break;
				case 'LIKE:LS':
					$query->where($field_name, 'LIKE', '%'.$filter_value);
					break;
				case 'EQUAL':
					$query->where($field_name, $filter_value);
					break;
			}
		}

		$filters['order'] = $order_field;
		$filters['direct'] = $order_direct;

		return [
			'query'			=> clone $query,
			'collection'	=> $do_search ? $query->paginate($this->_collection_options['per_page'])->appends($filters) : [],
			'filters'		=> $filters,
		];
	}

}
