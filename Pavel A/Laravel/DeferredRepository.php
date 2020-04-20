<?php

namespace App\Repositories;

use App\Models;

class DeferredRepository extends AbstractRepository {

    public function __construct() {
        parent::__construct(new Models\Deferred());
    }

    public function getDeferred($token)
    {
        return Models\Deferred::whereToken($token)->first();
    }

    public function addDeferred()
    {
        $deferred = new Models\Deferred();
        if (\Auth::user()){
            $deferred->client_id = \Auth::user()->id;
        } else {
            $deferred->client_id = 0;
        }
        $deferred->save();

        return $deferred;
    }

    public function getDeferredModels(Models\Deferred $deferred)
    {
        $items = [];
        foreach($deferred->models as $model){
            $items[$model->model_id] = [
                'item' => $model
            ];
        };

        return $items;
    }

    public function getUserDeferred(){
        if(\Auth::user()){
            return Models\Deferred::where('client_id', \Auth::user()->id)->first();
        }
        return null;
    }

    public function updateDeferredUser(Models\Deferred $deferred){
        if (\Auth::user()){
            $deferred->client_id = \Auth::user()->id;
        } else {
            $deferred->client_id = 0;
        }
        $deferred->save();
    }

    public function addDeferredModel(Models\Deferred $deferred, Models\Model $item)
    {
        $deferred_model = $deferred->models()->find($item->model_id);
        if (!$deferred_model){
            $deferred->models()->attach($item->model_id);
        };
    }

    public function deleteDeferredModel(Models\Deferred $deferred, Models\Model $item)
    {
        Models\DeferredModel::where('deferred_id', $deferred->id)->where('model_id', $item->model_id)->delete();
    }
}
