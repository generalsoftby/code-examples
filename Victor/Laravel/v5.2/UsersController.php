<?php namespace App\Http\Controllers;

use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Requests\Request;
use App\Models;
use NotificationSubscriptionTypesEnum;

class UsersController extends Controller {


	public function __construct()
	{
		$this->middleware('access.rest:' . \ComponentsEnum::USERS);
	}	


	public function index()
	{
		$this->_collection_options['filters'] = [
			'name'		=> 'LIKE:DS',
			'group_id'	=> 'EQUAL',
			'status'	=> 'EQUAL',
		];

		$data = $this->_format_collection(Models\User::query());
		$data['groups_list'] = Models\UserGroup::orderBy('name')->lists('name', 'id');
		$data['statuses_list'] = \UserStatusesEnum::getAll();
		
		return view('users.index', $data);
	}


	public function create()
	{
		$data['groups_list'] = Models\UserGroup::orderBy('name')->lists('name', 'id');
		$data['statuses_list'] = \UserStatusesEnum::getAll();
		
		return view('users.create', $data);
	}


	public function store(UserCreateRequest $request)
	{
		return $this->_save($request);
	}


	public function update(UserUpdateRequest $request, $id)
	{
		return $this->_save($request, $id);
	}


	public function edit($id)
	{
		$obj = Models\User::find($id);

		if (!$obj) {
			return redirect()->to(route('users.index'))
				->with('message', trans('messages.obj_not_found'));
		}

		$data['obj'] = $obj;
		$data['groups_list'] = Models\UserGroup::orderBy('name')->lists('name', 'id');
		$data['statuses_list'] = \UserStatusesEnum::getAll();

		return view('users.edit', $data);
	}


	private function _save(Request $request, $id = NULL)
	{
		$obj = $id ? Models\User::find($id) : new Models\User();
		$data = array_except($request->only($obj->getFillable()), ['password']);

		if ($id && !$obj) {
			return redirect()->to(route('users.index'))
				->withErrors(['message' => trans('messages.obj_not_found')]);
		}

		$obj->fill($data);

		$user_password = $request->get('password');
		if(!$id) {
			$random_password = $request->get('random_password');
			$obj->password = bcrypt($user_password ?: $random_password);
		} else if($user_password){
			$obj->password = bcrypt($user_password);
		}

        $subscriptions = array_keys(
            array_filter
            ([
                NotificationSubscriptionTypesEnum::CRASH_NOTIFICATIONS => $request->has('is_subscribe_to_crash_notification'),
            ])
        );

        \DB::transaction(function () use ($subscriptions, $obj)
        {
            $obj->save();

            $obj->syncSubscriptions($subscriptions);
        });

		return redirect()->to(route('users.index'))
		 	->with('message', trans($id ? 'messages.obj_updated_success' : 'messages.obj_created_success'));
	}
	
	
	public function destroy($id)
	{
		$obj = Models\User::find($id);

		if(!$obj) {
			return response(trans('messages.obj_not_found'), 404);
		}

		\DB::transaction(function() use($obj)
		{
			$obj->delete();
		});

		return ['status' => 'OK'];
	}


}
