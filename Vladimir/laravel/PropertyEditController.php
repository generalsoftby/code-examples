<?php

namespace App\Http\Controllers\Profile\Property;

use App\Enums\CarPlaceType;
use App\Enums\PropertyTypes;
use App\Enums\Roles;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\PropertyCheckOwnershipRequest;
use App\Http\Requests\Property\PropertyStoreRequest;
use App\Manager\OwnerCopy;
use App\Models\Property;
use App\Models\Client;
use App\Models\TypeLibraries\GarageType;
use App\Models\TypeLibraries\ParkingType;
use App\Models\TypeLibraries\UsePurpose;
use App\Support\Form\PropertyForm;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class PropertyEditController extends Controller
{
    public function addPropertyForm(Request $request)
    {
        $viewData = $this->addForm($request);

        return view('profile.properties.add', $viewData);
    }

    public function addPropertyChangeType(Request $request)
    {
        $viewData = $this->addForm($request);

        $form = $viewData['form'];
        $formData = $form->render()->getData();
        $propertyOwnersData = Arr::get($formData, 'propertyOwnersData');

        return [
            'view' => view('profile.properties.add', $viewData)->render(),
            'owners' => $propertyOwnersData
        ];
    }


    private function addForm(Request $request)
    {
        $form = new PropertyForm($request->all());
        $roles = Roles::getAllTranslated();
        $roles->pull(Roles::BUYER);
        $roles->pull(Roles::TENANT);

        $roleDefault = '';
        $rolesAuthUser = Auth::user()->roles()->pluck('role')->toArray();
        if(is_array($rolesAuthUser) && count($rolesAuthUser) == 1){
            $roleAuthUser = current($rolesAuthUser);
            if($roles->flip()->contains($roleAuthUser)) $roleDefault = $roleAuthUser;
        }
        if($client = Client::where('public_id', $request->get('client_owner', ''))->first()){
            $rolesClient = $client->roles()->pluck('role')->toArray();
            if(is_array($rolesClient) && count($rolesClient) == 1){
                $roleClient = current($rolesClient);
                if($roles->flip()->contains($roleClient)) $roleDefault = $roleClient;
            }
        }
        //$roleDefault = ($request->get('client_owner') ? Roles::INTERMEDIARY : $roleDefault);
        $selectedRole = old('role', $roleDefault);

        $usePurposes = UsePurpose::where('role', '=', $selectedRole)->get()->pluck('name', 'type');
        $usePurposeDefault = ($usePurposes->count() == 1) ? $usePurposes->flip()->first() : '';
        $selectedUserPurpose = old('use_purpose', $usePurposeDefault);

        $propertyTypes = PropertyTypes::mainTypesTranslated();
        $selectedPropertyType = old('main_type', '');
        $propertySubTypes = PropertyTypes::subTypesTranslated($selectedPropertyType);
        $selectedSubType = old('sub_type', $request->get('subtype'));

        return [
            'form' => $form,
            'roles' => $roles,
            'usePurposes' => $usePurposes,
            'propertyTypes' => $propertyTypes,
            'propertySubTypes' => $propertySubTypes,
            'selectedRole' => $selectedRole,
            'selectedUserPurpose' => $selectedUserPurpose,
            'selectedSubType' => $selectedSubType
        ];
    }

    public function editPropertyForm(PropertyCheckOwnershipRequest $request, Property $property)
    {
        $form = new PropertyForm(['property' => $property]);

        return view('profile.properties.edit', [
            'property' => $property,
            'form' => $form
        ]);
    }


    public function updateProperty(PropertyStoreRequest $request, Property $property)
    {
        $isCreate = false;
        if (!$property->id) {
            $isCreate = true;
        }

        $form = new PropertyForm(['property' => $property]);
        $property = $form->submit($request);

        if ($isCreate) {
            return redirect()->route('profile.properties.propertyCreated', ['property' => $property->public_id]);
        } else {
            session()->flash('status', trans('status.saved'));
            return redirect()->route('profile.properties.edit_form', ['property' => $property->public_id]);
        }
    }


    public function propertyCreated(Property $property)
    {
        return view('profile.properties.created', ['property' => $property]);
    }


    public function deleteProperty(Request $request, Property $property)
    {
        $property->delete();

        session()->flash('status', trans('status.deleted'));
        return ['redirect' => route('profile.properties.index')];
    }


    public function parkingGeneralType(Request $request)
    {
        $val = $request->get('parking_type');
        $options = [];
        if ($val == CarPlaceType::GARAGE) {
            $options = GarageType::all()->pluck('name', 'id');
        }
        if ($val == CarPlaceType::PARKING) {
            $options = ParkingType::all()->pluck('name', 'id');
        }

        return \Form::optionalSelect('subtype', $options);
    }

    public function usePurpose(Request $request)
    {
        $val = $request->get('role');
        $options = UsePurpose::where('role', '=', $val)->get()->pluck('name', 'type');
        $user = Auth::user();

        $response = [
            'usePurposes' => \Form::optionalSelect('use_purpose', $options)->toHtml()
        ];

        if (in_array($val, [Roles::SELLER, Roles::LANDLORD])) {
            $copyManager = app()->make(OwnerCopy::class);
            $userData = $copyManager->fromUserToOwnerData($user);

            // при автоматическом подставлении текущего юзера как собственника ему автоматом надо задавать роль продавец
            $userData['role'] = $val;

            $response['userOwnerData'] = $userData;
            $response['userOwnerView'] = view('profile.properties.owners.partials.tableRow', [
                'owner' => $userData
            ])->render();

        } else {
            $response['deleteUserFromOwnersId'] = $user->id;
        }

        return $response;
    }

    public function propertySubtype(Request $request)
    {
        $val = $request->get('type');
        $options = PropertyTypes::subTypesTranslated($val);
        return \Form::optionalSelect('use_purpose', $options);
    }
}
