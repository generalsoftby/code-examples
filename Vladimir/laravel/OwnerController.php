<?php

namespace App\Http\Controllers\Profile\Property;

use App\Http\Controllers\Controller;
use App\Http\Requests\Property\PropertyOwnerStoreRequest;
use App\Manager\OwnerCopy;
use App\Models\Client;
use App\Support\Form\Property\OwnerForm;
use Illuminate\Http\Request;

class OwnerController extends Controller
{
    public function tableRow(Request $request)
    {
        $clientId = $request->get('client_id');
        $client = Client::where('public_id', '=', $clientId)->first();
        $manager = app()->make(OwnerCopy::class);
        $ownerData = $manager->fromClientToOwnerData($client);
        $blockName=$request->get('block_name');

        $response = [
            'view' => view('profile.properties.owners.partials.tableRow', [
                'owner' => $ownerData,
                'block_name' => $blockName
            ])->render(),
            'owner' => $ownerData
        ];

        return $response;
    }

    public function editForm(Request $request)
    {

        $ownerData = $request->get('ownerData');
        $ownerData = collect($ownerData);

        $use_purpose = $request->get('use_purpose', '');
        $role = $request->get('role', '');

        if ($ownerData->isEmpty()) {
            $client = new Client();
            $manager = app()->make(OwnerCopy::class);
            $ownerData = $manager->fromClientToOwnerData($client);
        }

        $form = new OwnerForm(
            [
                'owner' => $ownerData,
                'use_purpose' => $use_purpose,
                'role' => $role,
            ]
        );

        return $form->render();
    }

    public function edit(PropertyOwnerStoreRequest $request)
    {
        $editData = $request->get('editData', []);

        $form = new OwnerForm(['owner' => $editData]);
        $ownerData = $form->submit($request);

        $response = [
            'view' => view('profile.properties.owners.partials.tableRow', [
                'owner' => $ownerData
            ])->render(),
            'owner' => $ownerData
        ];

        return $response;
    }
}
